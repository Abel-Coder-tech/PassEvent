<?php

namespace App\Http\Controllers\AgentVente;

use App\Http\Controllers\Controller;
use App\Models\CodePromo;
use App\Models\Evenement;
use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\PaiementMapper;
use App\Services\QrCodeService;
use App\Services\TicketPdfService;
use App\Support\PerPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    // Affiche le formulaire de connexion des agents de vente
    public function showLoginForm(): View
    {
        return view('agent-vente.auth.login');
    }

    // Authentifié un agent de vente avec vérifications (actif, événement non terminé)
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ],
        [
            'email.required' => 'Veuillez saisir votre adresse e-mail.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'password.required' => 'Veuillez saisir votre mot de passe.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
        ]);

        if (Auth::guard('agent_vente')->attempt($credentials)) {
            $agent = Auth::guard('agent_vente')->user();

            if (! $agent->actif) {
                Auth::guard('agent_vente')->logout();

                return back()->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'organisateur.']);
            }

            if ($agent->evenement->date_event < now()) {
                Auth::guard('agent_vente')->logout();

                return back()->withErrors(['email' => 'L\'événement est déjà terminé.']);
            }

            $agent->update(['dernier_acces' => now()]);

            return redirect()->intended(route('agent-vente.dashboard'));
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.']);
    }

    // Tableau de bord de l'agent de vente avec ventes du jour et statistiques
    public function dashboard(): View
    {
        $agent = Auth::guard('agent_vente')->user();

        // Supprime les tickets en attente depuis plus de 30 minutes (expiration)
        $agent->tickets()
            ->where('statut_paiement', 'en_attente')
            ->where('date_achat', '<', now()->subMinutes(30))
            ->delete();

        $ticketsAujourdHui = $agent->tickets()
            ->where('statut_paiement', 'payé')
            ->whereDate('date_achat', today())
            ->with('tarif')
            ->latest('date_achat')
            ->get();

        $stats = [
            'total_tickets' => $agent->tickets_count,
            'montant_total' => $agent->montant_total,
            'aujourd_hui' => $ticketsAujourdHui->count(),
            'montant_ajd' => $ticketsAujourdHui->sum('montant'),
        ];

        return view('agent-vente.dashboard', compact('agent', 'ticketsAujourdHui', 'stats'));
    }

    // Retourne l'historique des ventes en JSON pour rafraîchissement dynamique
    public function historiqueJson(): JsonResponse
    {
        $agent = Auth::guard('agent_vente')->user();

        $tickets = $agent->tickets()
            ->where('statut_paiement', 'payé')
            ->with('tarif')
            ->latest('date_achat')
            ->take(50)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'nom' => $t->nom_acheteur,
                'tarif' => $t->tarif?->getLabel() ?? 'N/A',
                'montant' => $t->montant > 0 ? number_format($t->montant, 0, ',', ' ').' F' : 'Gratuit',
                'montant_val' => $t->montant,
                'methode' => $t->methode_paiement,
                'methode_label' => Ticket::methodePaiementLabel($t->methode_paiement),
                'date' => $t->date_achat->format('H:i'),
            ]);

        return response()->json([
            'tickets' => $tickets,
            'total_tickets' => $agent->tickets_count,
            'montant_total' => number_format($agent->montant_total, 0, ',', ' ').' F',
            'aujourd_hui' => $agent->tickets()->where('statut_paiement', 'payé')->whereDate('date_achat', today())->count(),
            'montant_ajd' => number_format(
                $agent->tickets()->where('statut_paiement', 'payé')->whereDate('date_achat', today())->sum('montant'), 0, ',', ' '
            ).' F',
        ]);
    }

    // Historique complet des ventes de l'agent : paginé (10/page) et filtrable par période
    public function historique(Request $request): View
    {
        $agent = Auth::guard('agent_vente')->user();

        $validated = $request->validate([
            'periode' => 'nullable|string|in:aujourdhui,hier,semaine,mois,tout',
            'per_page' => 'nullable|integer',
        ],
        [
            'periode.in' => 'Période invalide. Choisissez parmi : aujourd\'hui, hier, semaine, mois, tout.',
            'per_page.integer' => 'Le nombre de résultats par page doit être un entier.',
        ]);

        $periode = $validated['periode'] ?? 'aujourdhui';
        $start = match ($periode) {
            'hier' => now()->subDay()->startOfDay(),
            'semaine' => now()->startOfWeek(),
            'mois' => now()->startOfMonth(),
            'tout' => null,
            default => now()->startOfDay(),
        };

        $query = $agent->tickets()
            ->where('statut_paiement', 'payé')
            ->with('tarif')
            ->latest('date_achat');

        if ($start) {
            $query->where('date_achat', '>=', $start);
        }

        $tickets = $query->paginate(PerPage::resolve());

        $montantFiltre = $tickets->sum('montant');

        return view('agent-vente.historique', compact('agent', 'tickets', 'periode', 'montantFiltre'));
    }

    // Enregistre une vente de ticket avec gestion espèces ou paiement mobile
    public function vendre(Request $request): RedirectResponse
    {
        $agent = Auth::guard('agent_vente')->user();

        $validated = $request->validate([
            'nom_acheteur' => 'required|string|max:255',
            'email_acheteur' => 'required|email|max:255',
            'telephone_acheteur' => 'required|string|max:20',
            'tarif_id' => 'required|exists:tarifs,id',
            'methode_paiement' => 'required|in:cash,mobile_money',
            'code_promo' => 'nullable|string|max:50',
        ],
        [
            'nom_acheteur.required' => 'Veuillez saisir le nom de l\'acheteur.',
            'email_acheteur.required' => 'Veuillez saisir l\'adresse e-mail de l\'acheteur.',
            'email_acheteur.email' => 'Veuillez saisir une adresse e-mail valide.',
            'telephone_acheteur.required' => 'Veuillez saisir le numéro de téléphone de l\'acheteur.',
            'tarif_id.required' => 'Veuillez sélectionner un tarif.',
            'tarif_id.exists' => 'Le tarif sélectionné est invalide.',
            'methode_paiement.required' => 'Veuillez sélectionner une méthode de paiement.',
            'methode_paiement.in' => 'Méthode de paiement invalide. Choisissez entre espèces ou mobile money.',
        ]);

        if ($agent->evenement->date_event < now()) {
            return back()->withErrors(['email_acheteur' => 'L\'événement est terminé.']);
        }

        $tarif = $agent->evenement->tarifs()->findOrFail($validated['tarif_id']);

        // Application du code promo (si fourni)
        $codePromo = null;
        $montantReduction = 0;
        $prixUnitaire = $tarif->prix;

        if (! empty($validated['code_promo'])) {
            $codePromo = CodePromo::validerPour($validated['code_promo'], $agent->evenement, $tarif);
            $montantReduction = $codePromo->calculerReduction($tarif->prix);
            $prixUnitaire = max(0, $tarif->prix - $montantReduction);
            // nb_utilisations incrémenté uniquement à la confirmation (cash ici, mobile via callback/webhook)
        }

        // Blocage espèces si seuil mobile money non atteint ou si bloqué par le superadmin
        if ($validated['methode_paiement'] === 'cash' && ! $agent->evenement->ventesEspecesActivees()) {
            if ($agent->evenement->ventesEspecesBloqueesSuperadmin()) {
                return back()->withErrors([
                    'methode_paiement' => 'Les ventes espèces sont actuellement désactivées pour cet événement. Utilisez le mobile money pour vendre vos tickets.',
                ]);
            }
            $seuil = (int) ceil($agent->evenement->capacite * Evenement::SEUIL_ESPECES_PCT / 100);
            $vendus = $agent->evenement->ticketsEnLigneCount();

            return back()->withErrors([
                'methode_paiement' => "Ventes espèces bloquées. Vendre d'abord {$seuil} ticket(s) en ligne. Progression : {$vendus}/{$seuil}.",
            ]);
        }

        $ticket = Ticket::create([
            'evenement_id' => $agent->evenement->id,
            'tarif_id' => $tarif->id,
            'agent_vente_id' => $agent->id,
            'code_unique' => Ticket::genererCodeSecurise(),
            'qr_signature' => Str::uuid()->toString(),
            'email_acheteur' => $validated['email_acheteur'],
            'telephone_acheteur' => $validated['telephone_acheteur'],
            'nom_acheteur' => $validated['nom_acheteur'],
            'nom_tarif' => $tarif->nom ?? 'Standard',
            'montant' => $prixUnitaire,
            'montant_reduction' => $montantReduction,
            'code_promo_utilise' => $codePromo ? $codePromo->code : null,
            'quantite' => 1,
            'statut_paiement' => 'en_attente',
            'methode_paiement' => $validated['methode_paiement'],
            'type_paiement' => PaiementMapper::moyenPaiement($validated['methode_paiement']),
            'utilise' => false,
            'date_achat' => now(),
        ]);

        if ($validated['methode_paiement'] === 'cash') {
            $ticket->update(['statut_paiement' => 'payé']); // Paiement espèces = confirmé immédiatement
            $agent->evenement->increment('quota_vendu', 1);
            $tarif->increment('quantite_vendue', 1);
            $agent->increment('tickets_count', 1, []);
            $agent->increment('montant_total', $prixUnitaire, []);
            // Comptabilise le code promo une fois la vente espèces confirmée
            if ($codePromo) {
                $codePromo->increment('nb_utilisations', 1, []);
            }
            session()->flash('ticket_created', $ticket->id); // Pour affichage du dernier ticket

            return redirect()->route('agent-vente.dashboard')
                ->with('success', 'Ticket vendu avec succès !');
        }

        return redirect()->route('agent-vente.paiement', $ticket->id);
    }

    // Affiche la page de paiement FedaPay pour un ticket
    public function payer(Ticket $ticket): View|RedirectResponse
    {
        $agent = Auth::guard('agent_vente')->user();

        if ($ticket->agent_vente_id !== $agent->id) {
            abort(403);
        }

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('agent-vente.dashboard');
        }

        $fedapay = app(FedapayService::class);
        $publicKey = $fedapay->getPublicKey();
        $sandbox = $fedapay->isSandbox();

        return view('agent-vente.paiement', compact('agent', 'ticket', 'publicKey', 'sandbox'));
    }

    // Télécharge le PDF du ticket avec QR code (max 3 téléchargements)
    public function downloadPdf(Ticket $ticket): Response
    {
        $agent = Auth::guard('agent_vente')->user();

        if ($ticket->agent_vente_id !== $agent->id) {
            abort(403); // Vérification de propriété
        }

        if ($ticket->statut_paiement !== 'payé') {
            abort(403, 'Le paiement de ce ticket n\'a pas été confirmé.'); // Ticket non payé
        }

        $max = config('app.max_downloads');

        if ($ticket->download_count >= $max) {
            abort(403, 'Limite de téléchargements atteinte ('.$max.' maximum).'); // Limite anti-abus
        }

        $ticket->increment('download_count', 1, []); // Incrémente le compteur de téléchargements

        $reste = $max - $ticket->download_count;
        if ($reste === 1) {
            session()->flash('warning', "Attention : il ne vous reste plus qu'1 téléchargement sur les {$max} autorisés.");
        }

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 170);
        $logoDataUri = Ticket::logoVioletDataUri();
        $pdf = TicketPdfService::generer($ticket, $qrCodeDataUri, $logoDataUri);
        $filename = 'ticket-'.$ticket->code_unique.'.pdf';

        return $pdf->download($filename);
    }

    // Déconnecte l'agent de vente
    public function logout(): RedirectResponse
    {
        Auth::guard('agent_vente')->logout();

        return redirect()->route('agent-vente.login');
    }
}
