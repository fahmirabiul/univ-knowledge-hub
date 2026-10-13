<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\AcademicStatus;
use App\Http\Controllers\Controller;
use App\Services\Auth\SsoAuthService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SsoAuthController extends Controller
{
    /**
     * Redirect the user to the univ-iam OAuth2 authorization page.
     */
    public function redirect(Request $request, SsoAuthService $ssoService): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('sso_oauth_state', $state);

        $authorizationUrl = $ssoService->getAuthorizationUrl($state);

        return redirect()->away($authorizationUrl);
    }

    /**
     * Handle the OAuth2 callback from univ-iam.
     */
    public function callback(Request $request, SsoAuthService $ssoService): RedirectResponse
    {
        // Check for error response from IAM
        if ($request->has('error')) {
            $errorDescription = $request->query('error_description', $request->query('error', 'Otorisasi SSO dibatalkan.'));
            Log::warning('SSO Authorization Error from IAM:', ['error' => $errorDescription]);

            return redirect()->route('home')->with('error', 'Proses masuk SSO dibatalkan: '.$errorDescription);
        }

        // Validate code presence
        $code = $request->query('code');
        if (! is_string($code) || empty($code)) {
            return redirect()->route('home')->with('error', 'Kode otorisasi dari IAM tidak ditemukan.');
        }

        // Validate state token for CSRF protection
        $savedState = $request->session()->pull('sso_oauth_state');
        $currentState = $request->query('state');

        if ($savedState && (! is_string($currentState) || ! hash_equals($savedState, $currentState))) {
            Log::warning('SSO State Token Mismatch (CSRF prevention):', [
                'expected' => $savedState,
                'received' => $currentState,
            ]);

            return redirect()->route('home')->with('error', 'Validasi sesi SSO tidak cocok. Silakan coba masuk kembali.');
        }

        try {
            $user = $ssoService->handleCallback($code);

            if ($user->academic_status === AcademicStatus::NONAKTIF) {
                return redirect()->route('home')->with('error', 'Akun Anda berstatus Non-Aktif. Akses masuk tidak diizinkan.');
            }

            Auth::login($user);
            $request->session()->regenerate();

            $targetRoute = match (true) {
                $user->isAdminLPPM() => route('admin.review.index'),
                $user->isDosen() => route('publications.index'),
                default => route('home'),
            };

            $intended = $request->session()->pull('url.intended');
            if ($intended && ! in_array($intended, [route('home'), route('login'), route('dashboard'), url('/'), ''], true)) {
                return redirect()->to($intended)->with('success', "Selamat datang kembali, {$user->name}!");
            }

            return redirect()->to($targetRoute)->with('success', "Selamat datang kembali, {$user->name}!");
        } catch (Exception $e) {
            Log::error('SSO Callback Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('home')->with('error', 'Gagal memproses autentikasi SSO: '.$e->getMessage());
        }
    }

    /**
     * Log the user out of Knowledge Hub and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Anda telah berhasil keluar dari sesi.');
    }
}
