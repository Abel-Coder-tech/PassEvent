<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DemandeSuperAdminController extends Controller
{
    public const OBJETS = [
        'ticket_physique' => 'Ticket physique (QR Code)',
        'reduction_commission' => 'Réduction Commission',
        'augmentation_agents' => 'Augmentation des agents',
        'evenement_a_la_une' => 'Événement à la une',
        'probleme_technique' => 'Problème technique',
    ];

    // Enregistre une demande de l'organisateur vers le super admin (notification système)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'objet' => 'required|string|in:'.implode(',', array_keys(self::OBJETS)),
            'evenement_id' => 'nullable|integer|exists:evenement,id',
            'message' => 'required|string|min:10|max:2000',
            'commission_pourcentage' => 'nullable|numeric|min:0|max:100',
            'quantites' => 'nullable|array',
            'quantites.*' => 'nullable|integer|min:0|max:5000',
        ]);

        $user = $request->user();
        $objet = self::OBJETS[$validated['objet']];
        $evenement = null;

        if (! empty($validated['evenement_id'])) {
            $evenement = Evenement::where('user_id', $user->id)->findOrFail($validated['evenement_id']);
        }

        $message = trim($validated['message']);

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

        return $message;
    }
}
