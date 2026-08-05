<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCompteActif
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || $user->statut !== 'bloque' || $request->isMethod('get') || $request->routeIs('logout')) {
            return $next($request);
        }
        return redirect()->back()->with('error', 'Votre compte est temporairement désactivé. Vous ne pouvez effectuer aucune action pour le moment.');
    }
}
