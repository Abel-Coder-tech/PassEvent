<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('superadmin')->check()) {
            return redirect()->route('superadmin.login');
        }

        if (auth('superadmin')->user()->role !== 'super_admin') {
            abort(403, 'Acces reserve au super administrateur.');
        }

        return $next($request);
    }
}
