<?php

namespace App\Http\Controllers;

use App\Mail\TicketEmail;
use App\Models\Evenement;
use App\Models\Ticket;
use App\Models\Log;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    // Liste les tickets de l'organisateur avec filtres et statistiques
    public function index(Request $request)
    {
        $user = auth()->user();
        $evenementsIds = Evenement::where('user_id', $user->id)->pluck('id'); // Événements de l'utilisateur

        $search = $request->input('q');

        $query = Ticket::with('evenement', 'tarif')->whereIn('evenement_id', $evenementsIds);

        if ($search) {
            // Recherche par nom, téléphone ou code ticket
            $query->where(function ($sub) use ($search) {
                $sub->where('nom_acheteur', 'like', '%' . $search . '%')
                    ->orWhere('telephone_acheteur', 'like', '%' . $search . '%')
                    ->orWhere('code_unique', 'like', '%' . $search . '%');
            });
        }

        $tickets = $query->orderBy('date_achat', 'desc')->paginate(15);

        // Statistiques par catégorie de ticket
        $statsQuery = Ticket::whereIn('evenement_id', $evenementsIds);

        if ($search) {
            // Applique le même filtre de recherche sur les stats
            $statsQuery->where(function ($sub) use ($search) {
                $sub->where('nom_acheteur', 'like', '%' . $search . '%')
                    ->orWhere('telephone_acheteur', 'like', '%' . $search . '%')
                    ->orWhere('code_unique', 'like', '%' . $search . '%');
            });
        }

        $totalTickets = $statsQuery->count();
        $valides = (clone $statsQuery)->where('statut_paiement', 'payé')->where('utilise', false)->count();
        $scannes = (clone $statsQuery)->where('utilise', true)->count();
        $etudiants = (clone $statsQuery)->where('categorie', 'etudiant')->count();
        $annules = (clone $statsQuery)->whereIn('statut_paiement', ['annulé', 'remboursé'])->count();

        return view('tickets.index', compact('tickets', 'totalTickets', 'valides', 'scannes', 'etudiants', 'annules', 'search'));
    }

    // Détails d'un ticket avec historique de logs
    public function show(int $id)
    {
        $ticket = Ticket::with('evenement', 'tarif', 'notifications')->findOrFail($id);
        // use two-argument where to avoid argument mismatch
        $logs = Log::where('ticket_id', $id)->orderBy('created_at', 'desc')->get(); // Historique complet

        return view('tickets.show', compact('ticket', 'logs'));
    }

    // Télécharge le PDF du ticket avec QR code
    public function downloadPdf(int $id)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($id);

        if ($ticket->download_count >= 3) {
            return back()->with('error', 'Limite de téléchargements atteinte (3 maximum).'); // Anti-abus
        }

        $ticket->increment('download_count', 1, []); // Incrémente le compteur

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 200);
        $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo-ticket.png')));

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri', 'logoDataUri'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'PaxEvent-' . $ticket->code_unique . '.pdf';

        return $pdf->download($filename);
    }

    // Télécharge le ticket (avec vérification de paiement)
    public function downloadTicket(int $id)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($id);

        if ($ticket->statut_paiement !== 'payé') {
            return back()->with('error', 'Le ticket n\'est pas disponible tant que le paiement n\'est pas confirme.'); // Paiement requis
        }

        if ($ticket->download_count >= 3) {
            return back()->with('error', 'Limite de téléchargements atteinte (3 maximum).');
        }

        $ticket->increment('download_count', 1, []);

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 200);
        $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo-ticket.png')));

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri', 'logoDataUri'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'PaxEvent-' . $ticket->code_unique . '.pdf';

        return $pdf->download($filename);
    }

    // Page de récupération de ticket par le public
    public function recuperer()
    {
        return view('site.recuperer');
    }

    // Recherche de tickets par ID de transaction et email
    public function rechercher(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'email' => 'required|email',
        ],[
            'transaction_id.required' => 'L\'ID de transaction est requis.',
            'transaction_id.string' => 'L\'ID de transaction doit être une chaîne de caractères.',
            'transaction_id.max' => 'L\'ID de transaction ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
        ]);

        $tickets = Ticket::with('evenement', 'tarif')
            ->where('transaction_id', $request->transaction_id)
            ->where('email_acheteur', strtolower($request->email))
            ->orderBy('date_achat', 'desc')
            ->get();

        if ($tickets->isEmpty()) {
            return back()->with('error', 'Aucun billet trouvé avec ces informations. Vérifiez l\'ID de transaction et l\'email saisis lors de l\'achat.');
        }

        return view('site.resultats', compact('tickets'));
    }

    // Renvoie le ticket par email depuis l'interface admin
    public function renvoyer(int $id)
    {
        $ticket = Ticket::with('evenement')->findOrFail($id);

        try {
            Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi du ticket: ' . $e->getMessage());
        }

        Log::create([
            'ticket_id' => $ticket->id,
            'type_operation' => 'envoi',
            'details' => json_encode(['methode' => 'renvoi_admin', 'email' => $ticket->email_acheteur]), // Contexte : renvoi admin
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Ticket renvoyé a ' . $ticket->email_acheteur);
    }

    // Annule un ticket et restaure les quotas
    public function annuler(int $id)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($id);

        if ($ticket->statut_paiement !== 'payé' && $ticket->statut_paiement !== 'en_attente') {
            return back()->with('error', 'Ce ticket ne peut pas etre annulé.'); // Statut incompatible
        }

        $ancienStatut = $ticket->statut_paiement;

        $ticket->update([
            'statut_paiement' => 'rembourse', // Marque comme remboursé/annulé
        ]);

        if ($ticket->evenement) {
            $ticket->evenement->decrement('quota_vendu', 1, []); // Restaure le quota
        }
        if ($ticket->tarif) {
            $ticket->tarif->decrement('quantite_vendue', 1, []); // Restaure la quantité vendue
        }

        Log::create([
            'ticket_id' => $ticket->id,
            'type_operation' => 'remboursement',
            'details' => json_encode(['ancien_statut' => $ancienStatut, 'motif' => 'annulation_admin']),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Ticket annulé et quota restauré.');
    }

    public function create()
    {
        return view('tickets.index');
    }
    public function store(Request $request) { return back(); }
    public function edit(int $id) { return back(); }
    public function update(Request $request, int $id) { return back(); }
    public function destroy(int $id) { return back(); }
}
