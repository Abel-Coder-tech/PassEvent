<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::orderByDesc('created_at')->paginate(20);
        $nonLus = Message::where('lu', false)->count();

        return view('admin.messages.index', compact('messages', 'nonLus'));
    }

    public function show($id)
    {
        $message = Message::findOrFail($id);

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

        $message = Message::findOrFail($id);

        $message->update([
            'reponse_admin' => $validated['reponse_admin'],
            'date_reponse' => now(),
        ]);

        return back()->with('success', 'Reponse enregistree avec succes.');
    }

    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'Message supprime avec succes.');
    }
}
