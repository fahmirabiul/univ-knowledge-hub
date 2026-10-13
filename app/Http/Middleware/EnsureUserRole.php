<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('auth.sso.redirect'));
        }

        $user = Auth::user();
        if (in_array('admin_lppm', $roles, true)) {
            $user->isAdminLPPM();
        }
        $userRole = $user->role?->value ?? (string) $user->role;

        if (! empty($roles) && ! in_array($userRole, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki peran yang diizinkan.'], 403);
            }

            abort(403, 'Akses ditolak: Anda tidak memiliki peran yang diizinkan untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
