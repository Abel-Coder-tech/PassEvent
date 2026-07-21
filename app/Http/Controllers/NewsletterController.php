<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterConfirmation;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    // Inscrit un email à la newsletter ou réactive un désabonnement
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $existing = Newsletter::where('email', $validated['email'])->first();

        if ($existing) {
            if (!$existing->actif) { // Réactivation d'un désabonnement
                $existing->update(['actif' => true, 'desabonne_le' => null]);
                Mail::to($validated['email'])->send(new NewsletterConfirmation($validated['email']));
                return response()->json(['success' => true, 'message' => 'Vous êtes de nouveau abonné !']);
            }
            return response()->json(['success' => true, 'message' => 'Vous êtes déjà abonné.']);
        }

        Newsletter::create([
            'email' => $validated['email'],
            'actif' => true,
        ]);

        Mail::to($validated['email'])->send(new NewsletterConfirmation($validated['email']));

        return response()->json(['success' => true, 'message' => 'Merci pour votre inscription ! Un email de confirmation vous a été envoyé.']);
    }

    // Désinscrit un email de la newsletter
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscriber = Newsletter::where('email', $validated['email'])->first();

        if ($subscriber && $subscriber->actif) {
            $subscriber->update([
                'actif' => false,
                'desabonne_le' => now(), // Enregistre la date de désinscription
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Vous êtes désabonné.']);
    }
}
