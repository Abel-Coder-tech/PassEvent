<?php

namespace App\Http\Controllers;

use App\Mail\TicketEmail;
use App\Models\Ticket;
use App\Models\Log;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = Ticket::with('evenement', 'tarif');

        if ($search) {
            $query->where(function ($sub) use ($search) {
                $sub->where('nom_acheteur', 'like', '%' . $search . '%')
                    ->orWhere('telephone_acheteur', 'like', '%' . $search . '%')
                    ->orWhere('code_unique', 'like', '%' . $search . '%');
            });
        }

        $tickets = $query->orderBy('date_achat', 'desc')->paginate(15);

        $statsQuery = Ticket::query();

        if ($search) {
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

    public function show($id)
    {
        $ticket = Ticket::with('evenement', 'tarif', 'notifications')->findOrFail($id);
        $logs = Log::where('ticket_id', $id)->orderByDesc('created_at')->get();

        return view('tickets.show', compact('ticket', 'logs'));
    }

    public function downloadPdf($id)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($id);

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 200);

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'PassEvent-' . $ticket->code_unique . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadTicket($id)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($id);

        if ($ticket->statut_paiement !== 'payé') {
            return back()->with('error', 'Le ticket n\'est pas disponible tant que le paiement n\'est pas confirme.');
        }

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 200);

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'PassEvent-' . $ticket->code_unique . '.pdf';

        return $pdf->download($filename);
    }

    public function recuperer()
    {
        return view('site.recuperer');
    }

    public function rechercher(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'telephone' => 'required|string|min:6|max:20',
        ]);

        $tickets = Ticket::with('evenement', 'tarif')
            ->where('nom_acheteur', $request->nom)
            ->where('email_acheteur', strtolower($request->email))
            ->where('telephone_acheteur', $request->telephone)
            ->orderBy('date_achat', 'desc')
            ->get();

        if ($tickets->isEmpty()) {
            return back()->with('error', 'Aucun billet trouve avec ces informations. Verifiez le nom, l\'email et le numero saisis lors de l\'achat.');
        }

        return view('site.resultats', compact('tickets'));
    }

    public function renvoyer($id)
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
            'details' => json_encode(['methode' => 'renvoi_admin', 'email' => $ticket->email_acheteur]),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Ticket renvoye a ' . $ticket->email_acheteur);
    }

    public function annuler($id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->statut_paiement !== 'payé' && $ticket->statut_paiement !== 'en_attente') {
            return back()->with('error', 'Ce ticket ne peut pas etre annule.');
        }

        $ancienStatut = $ticket->statut_paiement;

        $ticket->update([
            'statut_paiement' => 'remboursé',
        ]);

        Log::create([
            'ticket_id' => $ticket->id,
            'type_operation' => 'remboursement',
            'details' => json_encode(['ancien_statut' => $ancienStatut, 'motif' => 'annulation_admin']),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Ticket annule avec succes.');
    }

    public function create() { return view('tickets.create'); }
    public function store(Request $request) { return back(); }
    public function edit($id) { return back(); }
    public function update(Request $request, $id) { return back(); }
    public function destroy($id) { return back(); }
}
