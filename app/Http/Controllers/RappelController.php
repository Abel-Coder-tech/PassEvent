<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Ticket;
use App\Mail\TicketEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RappelController extends Controller
{
    public function index()
    {
        $evenements = Evenement::where('user_id', Auth::id())
            ->where('statut', 'publié')
            ->where('date_event', '>', now())
            ->orderBy('date_event', 'asc')
            ->withCount(['tickets' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->get();

        return view('admin.rappels.index', compact('evenements'));
    }

    public function envoyer(Request $request)
    {
        $validated = $request->validate([
            'evenement_id' => 'required|exists:evenement,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $evenement = Evenement::where('id', $validated['evenement_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $tickets = Ticket::where('evenement_id', $evenement->id)
            ->where('statut_paiement', 'payé')
            ->with('evenement')
            ->get();

        $envoyes = 0;
        $erreurs = 0;

        foreach ($tickets as $ticket) {
            if ($ticket->email_acheteur) {
                try {
                    Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
                    $envoyes++;
                } catch (\Exception $e) {
                    $erreurs++;
                }
            }
        }

        return back()->with('success', "Rappel envoye : {$envoyes} emails, {$erreurs} erreurs.");
    }
}
