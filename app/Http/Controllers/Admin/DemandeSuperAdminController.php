<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Message;
use App\Services\LotAutoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DemandeSuperAdminController extends Controller
{
    public const OBJETS = [
        'ticket_physique' => 'Ticket physique (QR Code)',
        'reduction_commission' => 'Réduction Commission',
        'augmentation_agents' => 'Augmentation des agents',
        'evenement_a_la_une' => 'Événement à la une',
        'probleme_technique' => 'Problème technique',
        'booster_promouvoir' => 'Booster ou promouvoir un événement',
    ];

    // Objet complet stocké en base pour les demandes de campagnes marketing ciblées
    public const OBJET_CAMPAGNE = '[Demande] Booster ou promouvoir un événement';

    // Publics ciblés disponibles pour une campagne marketing
    public const PERSONAS = [
        'tous' => 'Tous publics',
        'jeunes' => 'Étudiants / jeunes',
        'professionnels' => 'Professionnels',
        'local' => 'Public local de la ville',
        'fideles' => 'Fidèles de l\'organisateur',
    ];

    // Canaux de diffusion souhaités pour une campagne marketing
    public const CANAUX = [
        'site' => 'Mise en avant sur le site (une / bannière)',
        'newsletter' => 'Newsletter PaxEvent',
        'reseaux' => 'Réseaux sociaux',
        'whatsapp' => 'WhatsApp / SMS',
        'mixte' => 'Mixte',
    ];

    // Enregistre une demande de l'organisateur vers le super admin (notification système)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'objet' => 'required|string|in:'.implode(',', array_keys(self::OBJETS)),
            'evenement_id' => 'nullable|integer|exists:evenement,id',
            'message' => 'nullable|string|max:2000',
            'commission_pourcentage' => 'nullable|numeric|min:0|max:100',
            'quantites' => 'nullable|array',
            'quantites.*' => 'nullable|integer|min:0|max:5000',
            'cible_persona' => ['nullable', 'in:'.implode(',', array_keys(self::PERSONAS))],
            'canal_souhaite' => ['nullable', 'in:'.implode(',', array_keys(self::CANAUX))],
        ]);

        $user = $request->user();
        $objet = self::OBJETS[$validated['objet']];
        $evenement = null;

        // Une campagne marketing ciblée concerne forcément un événement
        if ($objet === self::OBJETS['booster_promouvoir'] && empty($validated['evenement_id'])) {
            return back()->withInput($request->input())
                ->withErrors(['evenement_id' => 'Sélectionnez l\'événement que vous souhaitez booster ou promouvoir.']);
        }

        if (! empty($validated['evenement_id'])) {
            $evenement = Evenement::where('user_id', $user->id)->findOrFail($validated['evenement_id']);

            // Règle métier : les demandes liées à un événement se font avant sa date
            if ($evenement->date_event && $evenement->date_event->isPast()) {
                return back()->withInput($request->input())
                    ->withErrors(['evenement_id' => 'Cet événement est déjà passé : les demandes le concernant ne sont plus possibles.']);
            }
        }

        $message = trim((string) ($validated['message'] ?? ''));

        // Demande de QR codes : template fourni dans le formulaire, puis paiement de la commission
        if ($objet === self::OBJETS['ticket_physique']) {
            return $this->demanderQrCodes($user, $evenement, $message, $validated, $request);
        }

        $tarifsNoms = $evenement ? $evenement->tarifs()->pluck('nom', 'id')->all() : null;

        $message = self::formaterMessage($objet, $message, $tarifsNoms, $validated);

        Message::create([
            'user_id' => null, // Notification système visible côté super admin
            'evenement_id' => $evenement?->id,
            'nom_complet' => $user->nom,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'objet' => '[Demande] '.$objet,
            'message' => $message,
            'lu' => false,
        ]);

        // Notifie la boîte support technique par email
        $this->notifierSupport($user, $objet, $message, $evenement);

        return back()->with('success', 'Votre demande a été envoyée à l\'équipe PaxEvent. Vous serez notifié(e) de la suite.');
    }

    // Demande de QR codes : le template est fourni dans le formulaire, puis l'organisateur
    // règle la commission. Le super admin n'est notifié qu'une fois le paiement confirmé.
    private function demanderQrCodes($user, ?Evenement $evenement, string $message, array $validated, Request $request)
    {
        $objet = self::OBJETS['ticket_physique'];

        if (! $evenement) {
            return back()->withInput($request->input())
                ->withErrors(['evenement_id' => 'Sélectionnez l\'événement concerné par votre demande de QR codes.']);
        }

        $lignes = [];
        foreach ((array) ($validated['quantites'] ?? []) as $tarifId => $qte) {
            $qte = (int) $qte;
            if ($qte <= 0) {
                continue;
            }

            $tarif = $evenement->tarifs()->where('statut', 'actif')->where('id', $tarifId)->first();
            if (! $tarif) {
                return back()->withInput($request->input())
                    ->withErrors(['quantites' => 'Un tarif sélectionné n\'est plus disponible.']);
            }

            $lignes[] = ['tarif' => $tarif, 'quantite' => $qte];
        }

        if (empty($lignes)) {
            return back()->withInput($request->input())
                ->withErrors(['quantites' => 'Indiquez au moins une quantité de QR codes.']);
        }

        $format = $this->validerTemplateDemande($request);

        if ($format === null) {
            return back()->withInput($request->input())
                ->withErrors(['format' => 'Les dimensions personnalisées doivent être comprises entre 30 et 200 mm.']);
        }

        $tarifsNoms = $evenement->tarifs()->pluck('nom', 'id')->all();
        $message = self::formaterMessage($objet, $message, $tarifsNoms, $validated);

        $reference = LotPhysique::PREFIXE_DEMANDE.strtoupper(Str::random(10));

        // Le visuel du ticket est enregistré une seule fois et appliqué à tous les lots de la demande
        $templatePath = 'lot-templates/demande_'.$reference.'.'.$request->file('template_image')->getClientOriginalExtension();
        $request->file('template_image')->storeAs('', $templatePath, 'public');

        $total = DB::transaction(function () use ($lignes, $evenement, $user, $reference, $format, $templatePath) {
            $total = 0;

            foreach ($lignes as $ligne) {
                $commission = round((float) $ligne['tarif']->prix * (LotPhysique::TAUX_AUTO / 100) * $ligne['quantite'], 2);
                $total += $commission;

                LotPhysique::create([
                    'user_id' => $user->id,
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $ligne['tarif']->id,
                    'commission_pourcentage' => null,
                    'nom' => mb_substr('QR Code - '.$ligne['tarif']->nom, 0, 100),
                    'quantite' => $ligne['quantite'],
                    'statut' => LotPhysique::STATUT_ATTENTE_PAIEMENT,
                    'auto_genere' => false, // Génération manuelle : QR positionnés par le super admin
                    'montant_commission' => $commission,
                    'email_reception' => $user->email,
                    'reference_paiement' => $reference,
                    'template_path' => $templatePath,
                    'format' => $format['format'],
                    'largeur_personnalisee' => $format['largeur_personnalisee'],
                    'hauteur_personnalisee' => $format['hauteur_personnalisee'],
                ]);
            }

            return $total;
        });

        // Commande gratuite (tarifs à 0 F) : la demande est considered payée immédiatement
        if ($total <= 0) {
            $lots = LotPhysique::where('reference_paiement', $reference)->get();
            LotAutoService::confirmerLots($lots, 'GRATUIT-'.$reference);
            LotAutoService::notifierPaiementAccepte($lots);

            return redirect()->route('admin.lots-physiques.index')
                ->with('success', 'Votre demande a été envoyée. Le super admin génère vos QR codes et vous les transmettra.');
        }

        return redirect()->route('admin.lots-physiques.checkout', $reference)
            ->with('success', 'Votre demande est enregistrée. Réglez la commission pour lancer la génération de vos QR codes.');
    }

    // Template de la demande : image PNG du ticket + format (obligatoire, fourni dès la demande)
    private function validerTemplateDemande(Request $request): ?array
    {
        $validated = $request->validate([
            'template_image' => ['required', 'image', 'mimes:png', 'max:10240'],
            'format' => ['required', 'in:s1,s2,v1,v2,custom'],
            'largeur_personnalisee' => 'nullable|integer|min:30|max:200',
            'hauteur_personnalisee' => 'nullable|integer|min:30|max:200',
        ], [
            'template_image.required' => 'Veuillez joindre votre image de ticket.',
            'template_image.image' => 'Le fichier doit être une image.',
            'template_image.mimes' => 'Format accepté : PNG uniquement.',
            'template_image.max' => 'L\'image ne doit pas dépasser 10 Mo.',
            'format.required' => 'Veuillez choisir un format.',
            'format.in' => 'Format invalide.',
        ]);

        $largeur = null;
        $hauteur = null;

        if ($validated['format'] === LotPhysique::FORMAT_CUSTOM) {
            $validated = array_merge($validated, $request->validate([
                'largeur_personnalisee' => 'required|integer|min:30|max:200',
                'hauteur_personnalisee' => 'required|integer|min:30|max:200',
            ], [
                'largeur_personnalisee.required' => 'Renseignez la largeur de votre ticket (mm).',
                'hauteur_personnalisee.required' => 'Renseignez la hauteur de votre ticket (mm).',
            ]));

            if (LotPhysique::formatPersonnalise((float) $validated['largeur_personnalisee'], (float) $validated['hauteur_personnalisee']) === null) {
                return null;
            }

            $largeur = (int) $validated['largeur_personnalisee'];
            $hauteur = (int) $validated['hauteur_personnalisee'];
        }

        return [
            'format' => $validated['format'],
            'largeur_personnalisee' => $largeur,
            'hauteur_personnalisee' => $hauteur,
        ];
    }

    // Envoie un email de notification à la boîte support technique
    protected function notifierSupport($user, string $objet, string $message, ?Evenement $evenement): void
    {
        $supportEmail = config('mail.support_address');

        if (! $supportEmail) {
            return;
        }

        Mail::mailer('support')->raw(
            "Nouvelle demande depuis le tableau de bord organisateur :\n\n".
            "De : {$user->nom} ({$user->email})\n".
            ($user->telephone ? "Téléphone : {$user->telephone}\n" : '').
            ($evenement ? "Événement : {$evenement->titre}\n" : '').
            "Objet : {$objet}\n\n".
            "Message :\n{$message}\n\n".
            'Connectez-vous au super dashboard pour y répondre.',
            function ($mail) use ($supportEmail, $objet) {
                $mail->to($supportEmail)
                    ->subject($objet);
            }
        );
    }

    // Préfixe le message avec le détail structuré de la demande (quantités par tarif, commission)
    public static function formaterMessage(string $objet, string $message, ?array $tarifsNoms, array $donnees): string
    {
        // Détail des quantités par tarif (demande « Ticket physique »)
        if ($objet === self::OBJETS['ticket_physique'] && ! empty($donnees['quantites']) && $tarifsNoms) {
            $lignes = [];
            foreach ($donnees['quantites'] as $tarifId => $qte) {
                if ($qte === null || (int) $qte <= 0) {
                    continue;
                }
                $nomTarif = $tarifsNoms[(string) $tarifId] ?? null;
                if ($nomTarif !== null) {
                    $lignes[] = "• {$nomTarif} : {$qte} ticket(s)";
                }
            }
            if (! empty($lignes)) {
                $message = "Quantités demandées :\n".implode("\n", $lignes)."\n\n".$message;
            }
        }

        // Détail du pourcentage de commission demandé (demande « Réduction Commission »)
        if ($objet === self::OBJETS['reduction_commission']
            && isset($donnees['commission_pourcentage'])
            && $donnees['commission_pourcentage'] !== null
            && $donnees['commission_pourcentage'] !== '') {
            $message = "Commission demandée : {$donnees['commission_pourcentage']} %\n\n".$message;
        }

        // Détail de la campagne marketing ciblée (demande « Booster ou promouvoir un événement »)
        if ($objet === self::OBJETS['booster_promouvoir']) {
            $lignes = [];
            if (! empty($donnees['cible_persona']) && isset(self::PERSONAS[$donnees['cible_persona']])) {
                $lignes[] = 'Public ciblé : '.self::PERSONAS[$donnees['cible_persona']];
            }
            if (! empty($donnees['canal_souhaite']) && isset(self::CANAUX[$donnees['canal_souhaite']])) {
                $lignes[] = 'Canal souhaité : '.self::CANAUX[$donnees['canal_souhaite']];
            }
            if (! empty($lignes)) {
                $message = implode("\n", $lignes)."\n\n".$message;
            }
        }

        return $message;
    }
}
