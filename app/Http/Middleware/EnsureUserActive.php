<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\AcademicStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserActive
{
    /**
     * Handle an incoming request to ensure the user is not in nonaktif status.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('auth.sso.redirect'));
        }

        $user = Auth::user();

        if ($user->academic_status === AcademicStatus::NONAKTIF) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun Anda berstatus non-aktif.'], 403);
            }

            return redirect()->route('home')->with('error', 'Akun Anda berstatus Non-Aktif. Akses masuk tidak diizinkan.');
        }

        return $next($request);
    }
}
