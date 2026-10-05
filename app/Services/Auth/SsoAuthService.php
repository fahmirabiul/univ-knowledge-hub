<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\AcademicStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SsoAuthService
{
    protected string $baseUrl;

    protected ?string $clientId;

    protected ?string $clientSecret;

    protected string $redirectUri;

    protected string $authorizeEndpoint;

    protected string $tokenEndpoint;

    protected string $userinfoEndpoint;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.iam.base_url', 'http://localhost:8000'), '/');
        $this->clientId = config('services.iam.client_id');
        $this->clientSecret = config('services.iam.client_secret');
        $this->redirectUri = (string) config('services.iam.redirect_uri');
        $this->authorizeEndpoint = (string) config('services.iam.authorize_endpoint', '/oauth/authorize');
        $this->tokenEndpoint = (string) config('services.iam.token_endpoint', '/oauth/token');
        $this->userinfoEndpoint = (string) config('services.iam.userinfo_endpoint', '/api/user');
    }

    /**
     * Generate IAM OAuth2 Authorization URL.
     */
    public function getAuthorizationUrl(?string $state = null): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => '',
            'state' => $state ?? Str::random(40),
        ]);

        return "{$this->baseUrl}{$this->authorizeEndpoint}?{$query}";
    }

    /**
     * Handle authorization code callback: exchange code, fetch profile, and sync local user.
     */
    public function handleCallback(string $code): User
    {
        $accessToken = $this->exchangeCodeForToken($code);
        $iamProfile = $this->fetchUserProfile($accessToken);

        return $this->syncLocalUser($iamProfile);
    }

    /**
     * Exchange authorization code for an OAuth2 access token.
     */
    public function exchangeCodeForToken(string $code): string
    {
        $response = Http::asForm()->post("{$this->baseUrl}{$this->tokenEndpoint}", [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal menukar authorization code dengan access token dari IAM. Response: '.$response->body());
        }

        $token = $response->json('access_token');

        if (! is_string($token) || empty($token)) {
            throw new RuntimeException('Format access token dari IAM tidak valid.');
        }

        return $token;
    }

    /**
     * Fetch user demographic profile from IAM using the access token.
     */
    public function fetchUserProfile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get("{$this->baseUrl}{$this->userinfoEndpoint}");

        if (! $response->successful()) {
            throw new RuntimeException('Gagal mengambil data profil pengguna dari IAM. Response: '.$response->body());
        }

        $data = $response->json('data') ?? $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Respons data profil dari IAM tidak valid.');
        }

        return $data;
    }

    /**
     * Synchronize and mirror user identity into the local database without passwords.
     */
    public function syncLocalUser(array $iamProfile): User
    {
        $ssoId = $iamProfile['sso_id'] ?? $iamProfile['id'] ?? null;
        $email = $iamProfile['email'] ?? null;

        if (! $ssoId || ! $email) {
            throw new RuntimeException('Data SSO ID dan Email wajib ada dalam profil IAM.');
        }

        $roleValue = $iamProfile['role'] ?? $iamProfile['role_lokal'] ?? UserRole::MAHASISWA->value;
        $role = UserRole::tryFrom((string) $roleValue) ?? UserRole::MAHASISWA;

        $statusValue = $iamProfile['academic_status'] ?? $iamProfile['status_akademik'] ?? AcademicStatus::AKTIF->value;
        $academicStatus = AcademicStatus::tryFrom((string) $statusValue) ?? AcademicStatus::AKTIF;

        $name = $iamProfile['name'] ?? $iamProfile['nama'] ?? 'Pengguna IAM';
        $faculty = $iamProfile['faculty'] ?? $iamProfile['fakultas'] ?? null;
        $studyProgram = $iamProfile['study_program'] ?? $iamProfile['program_studi'] ?? null;
        $avatarUrl = $iamProfile['avatar_url'] ?? null;

        return User::updateOrCreate(
            ['sso_id' => (string) $ssoId],
            [
                'name' => (string) $name,
                'email' => (string) $email,
                'role' => $role,
                'academic_status' => $academicStatus,
                'faculty' => $faculty ? (string) $faculty : null,
                'study_program' => $studyProgram ? (string) $studyProgram : null,
                'avatar_url' => $avatarUrl ? (string) $avatarUrl : null,
            ]
        );
    }
}
