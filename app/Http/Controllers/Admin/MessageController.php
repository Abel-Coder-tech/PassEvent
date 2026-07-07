<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::where('user_id', auth()->id())
            ->orderByDesc('created_at')->paginate(20);
        $nonLus = Message::where('user_id', auth()->id())->where('lu', false)->count();

        return view('admin.messages.index', compact('messages', 'nonLus'));
    }

    public function show($id)
    {
        $message = Message::where('user_id', auth()->id())->findOrFail($id);

        if (!$message->lu) {
            $message->update(['lu' => true]);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function repondre(Request $request, $id)
    {
        $validated = $request->validate([
            'reponse_admin' => 'required|string|min:10',
        ]);

        $message = Message::where('user_id', auth()->id())->findOrFail($id);

        $message->update([
            'reponse_admin' => $validated['reponse_admin'],
            'date_reponse' => now(),
        ]);

        Mail::raw(
            "Reponse de {$message->user?->nom} a votre message :\n\n" .
            "Objet : {$message->objet}\n\n" .
            "Votre message :\n{$message->message}\n\n" .
            "Reponse :\n{$validated['reponse_admin']}",
            function ($m) use ($message) {
                $m->to($message->email, $message->nom_complet)
                  ->replyTo($message->user?->email, $message->user?->nom)
                  ->subject("Re: {$message->objet}");
            }
        );

        return back()->with('success', 'Reponse envoyee avec succes.');
    }

    public function destroy($id)
    {
        $message = Message::where('user_id', auth()->id())->findOrFail($id);
        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'Message supprime avec succes.');
    }
}
