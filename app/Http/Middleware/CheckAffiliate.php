<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAffiliate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('affiliate')->check()) {
            return redirect()->route('affiliate.login');
        }

        $affiliate = auth('affiliate')->user();

        if (!$affiliate->actif) {
            auth('affiliate')->logout();
            return redirect()->route('affiliate.login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        return $next($request);
    }
}
