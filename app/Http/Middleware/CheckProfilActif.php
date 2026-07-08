<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProfilActif
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || $user->statut === 'actif') {
            return $next($request);
        }

        if ($user->statut === 'incomplet') {
            $url = route('profil.step2');
            $link = '<a href="' . $url . '">finaliser votre profil</a>';
            return redirect()->route('dashboard')->with('error', 'Veuillez d\'abord ' . $link . ' avant de créer.');
        }

        return redirect()->route('dashboard')->with('error', 'Votre profil doit être vérifié avant d\'utiliser cette fonctionnalité.');
    }
}
