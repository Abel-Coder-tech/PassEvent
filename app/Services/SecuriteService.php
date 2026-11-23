<?php

namespace App\Services;

use App\Models\JournalSecurite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Journal de sécurité : détecte les requêtes suspectes, enregistre les
 * événements (champs sensibles masqués) et alerte en cas d'attaque répétée.
 *
 * L'enregistrement ne doit jamais faire échouer une requête : toute erreur
 * est avalée et seulement écrite dans le journal Laravel.
 */
class SecuriteService
{
    // Au-delà de ce nombre d'événements par IP en 10 minutes, on n'enregistre plus
    // chaque ligne (le compteur continue) : évite qu'un robot remplisse la base.
    public const MAX_LIGNES_PAR_IP = 60;

    // Alerte email quand une IP atteint ce nombre d'événements en 5 minutes
    public const SEUIL_ALERTE = 20;

    // Champs jamais enregistrés en clair
    private const CHAMPS_SENSIBLES = '/pass|mot_de_passe|token|secret|carte|card|cvv|cvc|pin|otp|code_acces|authorization|cookie/i';

    // Liens que PaxEvent n'utilise pas et que les robots testent en masse
    private const LIENS_SUSPECTS = [
        'fichier_env' => '#(^|/)\.env#i',
        'depot_git' => '#(^|/)\.(git|svn|hg)(/|$)#i',
        'wordpress' => '#(^|/)(wp-admin|wp-login|wp-content|wp-includes|wp-json|xmlrpc\.php|wp-config)#i',
        'phpmyadmin' => '#(^|/)(phpmyadmin|pma|myadmin|adminer|mysqladmin)#i',
        'fichier_php' => '#\.php(\d)?(/|$)#i', // PaxEvent n'expose aucune page .php
        'secrets_serveur' => '#(^|/)(\.aws|\.ssh|\.htpasswd|\.htaccess|\.DS_Store|id_rsa|config\.(json|yml|yaml)|web\.config)#i',
        'sauvegarde' => '#\.(sql|bak|old|backup|tar|tgz|zip|7z|rar)(\.gz)?$#i',
        'outils_serveur' => '#(^|/)(cgi-bin|server-status|actuator|boaform|HNAP1|vendor/phpunit|telescope|_ignition|solr|jenkins)#i',
    ];

    // Contenus d'attaque cherchés dans le lien et les champs envoyés
    private const ATTAQUES = [
        'injection_sql' => "/(\bunion\b[\s\/\*]+(all[\s\/\*]+)?select\b|\bselect\b.+\bfrom\b.+\binformation_schema\b|'\s*or\s*'?\d+'?\s*=\s*'?\d+|'\s*or\s*'[^']*'\s*=\s*'|\b(sleep|benchmark|pg_sleep)\s*\(\s*\d|;\s*(drop|truncate|alter)\s+table\b|\bwaitfor\s+delay\b)/i",
        'script_xss' => '/(<\s*script\b|javascript\s*:|<\s*(img|svg|iframe|body)[^>]*\bon[a-z]+\s*=|\bon(error|load|mouseover)\s*=\s*["\']?[^"\'>\s]*\()/i',
        'lecture_fichier' => '#(\.\./\.\./|\.\.\\\\\.\.\\\\|/etc/(passwd|shadow|hosts)|php://(input|filter)|file://|expect://|data://text)#i',
        'injection_commande' => '/([;&|`]\s*(cat|ls|id|whoami|uname|wget|curl|bash|sh|nc|python|perl)\b|\$\((cat|id|whoami|curl|wget)\b)/i',
        'injection_template' => '/(\{\{\s*\d+\s*\*\s*\d+\s*\}\}|\$\{jndi:)/i',
    ];

    // Outils d'attaque automatiques reconnus à leur signature : refusés directement
    private const ROBOTS = '/(sqlmap|nikto|nmap|masscan|zgrab|acunetix|nessus|openvas|wpscan|dirbuster|gobuster|ffuf|feroxbuster|nuclei|hydra|havij|w3af|arachni|joomscan|commix|xsstrike)/i';

    /**
     * Analyse une requête. Renvoie [type, règle] si elle est suspecte, sinon null.
     */
    public function analyser(Request $request): ?array
    {
        $ua = (string) $request->userAgent();
        if ($ua !== '' && preg_match(self::ROBOTS, $ua, $m)) {
            return ['robot', strtolower($m[1])];
        }

        $chemin = '/'.ltrim(rawurldecode($request->path()), '/');
        foreach (self::LIENS_SUSPECTS as $regle => $motif) {
            if (preg_match($motif, $chemin)) {
                return ['lien_suspect', $regle];
            }
        }

        foreach ($this->valeursAInspecter($request) as $valeur) {
            foreach (self::ATTAQUES as $regle => $motif) {
                if (preg_match($motif, $valeur)) {
                    return ['attaque', $regle];
                }
            }
        }

        return null;
    }

    /**
     * Enregistre un événement de sécurité (sans jamais faire échouer la requête).
     */
    public function enregistrer(string $type, Request $request, array $extra = []): void
    {
        try {
            $ip = (string) $request->ip();

            $total = $this->compter("securite:ip10:{$ip}", 600);
            $recent = $this->compter("securite:ip5:{$ip}", 300);

            if ($total <= self::MAX_LIGNES_PAR_IP) {
                JournalSecurite::create([
                    'type' => $type,
                    'regle' => $extra['regle'] ?? null,
                    'ip' => $ip,
                    'methode' => $request->method(),
                    'url' => mb_substr($request->getRequestUri(), 0, 2000),
                    'espace' => $extra['espace'] ?? $this->espace($request),
                    'identifiant' => isset($extra['identifiant']) ? mb_substr((string) $extra['identifiant'], 0, 255) : null,
                    'donnees' => $this->donneesMasquees($request),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500) ?: null,
                ]);
            }

            if ($recent === self::SEUIL_ALERTE) {
                $this->alerter($ip, $recent, $type, $request);
            }
        } catch (Throwable $e) {
            Log::warning('Journal de sécurité : enregistrement impossible', ['erreur' => $e->getMessage()]);
        }
    }

    /**
     * Champs envoyés, sensibles masqués, valeurs tronquées. Les fichiers sont ignorés.
     */
    public function donneesMasquees(Request $request): ?array
    {
        $donnees = $this->masquer($request->except(['_token', '_method']));

        return $donnees === [] ? null : $donnees;
    }

    private function masquer(array $donnees, int $profondeur = 0): array
    {
        $resultat = [];
        foreach (array_slice($donnees, 0, 40, true) as $cle => $valeur) {
            if (preg_match(self::CHAMPS_SENSIBLES, (string) $cle)) {
                $resultat[$cle] = '••••••';
            } elseif (is_array($valeur)) {
                $resultat[$cle] = $profondeur < 3 ? $this->masquer($valeur, $profondeur + 1) : '[…]';
            } elseif (is_scalar($valeur) || $valeur === null) {
                $resultat[$cle] = is_string($valeur) ? mb_substr($valeur, 0, 300) : $valeur;
            }
        }

        return $resultat;
    }

    // Lien + champs envoyés (hors champs sensibles), aplatis et limités
    private function valeursAInspecter(Request $request): array
    {
        $valeurs = [rawurldecode((string) $request->getQueryString())];

        $aplatir = function (array $donnees, int $profondeur = 0) use (&$aplatir, &$valeurs) {
            foreach ($donnees as $cle => $valeur) {
                if (count($valeurs) > 100 || preg_match(self::CHAMPS_SENSIBLES, (string) $cle)) {
                    continue;
                }
                if (is_array($valeur) && $profondeur < 3) {
                    $aplatir($valeur, $profondeur + 1);
                } elseif (is_string($valeur) && $valeur !== '') {
                    $valeurs[] = mb_substr($valeur, 0, 5000);
                }
            }
        };
        $aplatir($request->except(['_token', '_method']));

        return $valeurs;
    }

    private function espace(Request $request): ?string
    {
        return match (true) {
            $request->is('superadmin', 'superadmin/*') => 'superadmin',
            $request->is('equipe', 'equipe/*') => 'equipe',
            $request->is('api/agent-vente/*', 'agent-vente', 'agent-vente/*') => 'agent_vente',
            $request->is('api/agent/*', 'agent', 'agent/*') => 'agent_scan',
            $request->is('affiliation', 'affiliation/*', 'affilie', 'affilie/*') => 'affilie',
            $request->is('login') => 'organisateur',
            default => null,
        };
    }

    private function compter(string $cle, int $secondes): int
    {
        Cache::add($cle, 0, $secondes);

        return (int) Cache::increment($cle);
    }

    // Une alerte par IP et par heure au plus, envoyée après la réponse
    private function alerter(string $ip, int $nombre, string $type, Request $request): void
    {
        if (! Cache::add("securite:alerte:{$ip}", true, 3600)) {
            return;
        }

        $destinataire = config('services.payment_monitoring.alert_email') ?: config('mail.support_address');
        if (! is_string($destinataire) || trim($destinataire) === '') {
            return;
        }

        $label = JournalSecurite::TYPES[$type]['label'] ?? $type;
        $lien = mb_substr($request->getRequestUri(), 0, 300);
        $ua = mb_substr((string) $request->userAgent(), 0, 200);

        dispatch(function () use ($destinataire, $ip, $nombre, $label, $lien, $ua) {
            try {
                Mail::mailer('support')->raw(
                "Activité suspecte répétée sur PaxEvent.\n\n".
                "IP : {$ip}\n".
                "Événements en 5 minutes : {$nombre} (dernier : {$label})\n".
                "Dernier lien : {$lien}\n".
                "Navigateur : {$ua}\n\n".
                "Détail et blocage de l'IP : Superadmin → Journal de sécurité.\n".
                "(Une seule alerte par heure et par IP.)",
                    fn ($mail) => $mail->to($destinataire)->subject("[PaxEvent] Alerte sécurité : {$ip}")
                );
            } catch (Throwable $e) {
                // Une panne d'email ne doit jamais casser la réponse
                Log::error('Alerte de sécurité non envoyée', ['ip' => $ip, 'erreur' => $e->getMessage()]);
            }
        })->afterResponse();
    }
}
