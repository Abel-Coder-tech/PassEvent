<?php

namespace App\Http\Controllers\AgentVente;

use App\Http\Controllers\Controller;
use App\Models\AgentVente;
use App\Models\Ticket;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('agent-vente.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('agent_vente')->attempt($credentials)) {
            $agent = Auth::guard('agent_vente')->user();

            if (!$agent->actif) {
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

    public function dashboard(): View
    {
        $agent = Auth::guard('agent_vente')->user();

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

    public function historiqueJson(): \Illuminate\Http\JsonResponse
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
                'montant' => $t->montant > 0 ? number_format($t->montant, 0, ',', ' ') . ' F' : 'Gratuit',
                'montant_val' => $t->montant,
                'methode' => $t->methode_paiement,
                'methode_label' => \App\Models\Ticket::methodePaiementLabel($t->methode_paiement),
                'date' => $t->date_achat->format('H:i'),
            ]);

        return response()->json([
            'tickets' => $tickets,
            'total_tickets' => $agent->tickets_count,
            'montant_total' => number_format($agent->montant_total, 0, ',', ' ') . ' F',
            'aujourd_hui' => $agent->tickets()->where('statut_paiement', 'payé')->whereDate('date_achat', today())->count(),
            'montant_ajd' => number_format(
                $agent->tickets()->where('statut_paiement', 'payé')->whereDate('date_achat', today())->sum('montant'), 0, ',', ' '
            ) . ' F',
        ]);
    }

    public function vendre(Request $request): RedirectResponse
    {
        $agent = Auth::guard('agent_vente')->user();

        $validated = $request->validate([
            'nom_acheteur' => 'required|string|max:255',
            'email_acheteur' => 'required|email|max:255',
            'telephone_acheteur' => 'required|string|max:20',
            'tarif_id' => 'required|exists:tarifs,id',
            'methode_paiement' => 'required|in:cash,mobile_money',
        ]);

        if ($agent->evenement->date_event < now()) {
            return back()->withErrors(['email_acheteur' => 'L\'événement est terminé.']);
        }

        $tarif = $agent->evenement->tarifs()->findOrFail($validated['tarif_id']);

        $ticket = Ticket::create([
            'evenement_id' => $agent->evenement->id,
            'tarif_id' => $tarif->id,
            'agent_vente_id' => $agent->id,
            'code_unique' => strtoupper(Str::random(12)),
            'qr_signature' => Str::uuid()->toString(),
            'email_acheteur' => $validated['email_acheteur'],
            'telephone_acheteur' => $validated['telephone_acheteur'],
            'nom_acheteur' => $validated['nom_acheteur'],
            'categorie' => $tarif->categorie ?? 'externe',
            'type' => $tarif->type ?? 'normal',
            'montant' => $tarif->prix,
            'quantite' => 1,
            'statut_paiement' => 'en_attente',
            'methode_paiement' => $validated['methode_paiement'],
            'utilise' => false,
            'date_achat' => now(),
        ]);

        if ($validated['methode_paiement'] === 'cash') {
            $ticket->update(['statut_paiement' => 'payé']);
            $agent->evenement->increment('quota_vendu', 1);
            $tarif->increment('quantite_vendue', 1);
            $agent->increment('tickets_count');
            $agent->increment('montant_total', $tarif->prix);
            session()->flash('ticket_created', $ticket->id);
            return redirect()->route('agent-vente.dashboard')
                ->with('success', 'Ticket vendu avec succès !');
        }

        return redirect()->route('agent-vente.paiement', $ticket->id);
    }

    public function payer(Ticket $ticket): View|RedirectResponse
    {
        $agent = Auth::guard('agent_vente')->user();

        if ($ticket->agent_vente_id !== $agent->id) {
            abort(403);
        }

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('agent-vente.dashboard');
        }

        $fedapay = app(\App\Services\FedapayService::class);
        $publicKey = $fedapay->getPublicKey();
        $sandbox = $fedapay->isSandbox();

        return view('agent-vente.paiement', compact('agent', 'ticket', 'publicKey', 'sandbox'));
    }

    public function downloadPdf(Ticket $ticket): \Illuminate\Http\Response
    {
        $agent = Auth::guard('agent_vente')->user();

        if ($ticket->agent_vente_id !== $agent->id) {
            abort(403);
        }

        if ($ticket->statut_paiement !== 'payé') {
            abort(403, 'Le paiement de ce ticket n\'a pas été confirmé.');
        }

        if ($ticket->download_count >= 3) {
            abort(403, 'Limite de téléchargements atteinte (3 maximum).');
        }

        $ticket->increment('download_count');

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 200);
        $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo-ticket.png')));
        $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri', 'logoDataUri'));
        $filename = 'ticket-' . $ticket->code_unique . '.pdf';

        return $pdf->download($filename);
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('agent_vente')->logout();
        return redirect()->route('agent-vente.login');
    }
}
