<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.iam.base_url', 'http://localhost:8000');
        Config::set('services.iam.internal_url', 'http://localhost:8000');
        Config::set('services.iam.client_id', 'test-client-id');
        Config::set('services.iam.client_secret', 'test-client-secret');
        Config::set('services.iam.redirect_uri', 'http://localhost:8001/auth/sso/callback');
    }

    public function test_redirect_saves_state_and_redirects_to_iam_authorization_url(): void
    {
        $response = $this->get(route('auth.sso.redirect'));

        $response->assertRedirect();
        $this->assertStringStartsWith('http://localhost:8000/oauth/authorize?', (string) $response->headers->get('Location'));
        $response->assertSessionHas('sso_oauth_state');
    }

    public function test_callback_successfully_authenticates_user_and_syncs_mirror(): void
    {
        $state = 'valid-test-state-token-12345';

        Http::fake([
            'http://localhost:8000/oauth/token' => Http::response([
                'access_token' => 'mock-iam-token-abc',
                'token_type' => 'Bearer',
            ], 200),
            'http://localhost:8000/api/user' => Http::response([
                'data' => [
                    'id' => 'e0000000-0000-0000-0000-000000000001',
                    'name' => 'Prof. Dr. Andi Pratama',
                    'email' => 'andi.pratama@univ.ac.id',
                    'role' => 'dosen',
                    'academic_status' => 'aktif',
                    'faculty' => 'Fakultas Ilmu Komputer',
                    'study_program' => 'Teknik Informatika',
                ],
            ], 200),
        ]);

        $response = $this->withSession(['sso_oauth_state' => $state])
            ->get(route('auth.sso.callback', [
                'code' => 'valid-iam-code-777',
                'state' => $state,
            ]));

        $response->assertRedirect(route('publications.index'));
        $response->assertSessionHas('success');

        $this->assertTrue(Auth::check());
        $this->assertEquals('andi.pratama@univ.ac.id', Auth::user()->email);
        $this->assertEquals('e0000000-0000-0000-0000-000000000001', Auth::user()->sso_id);
        $this->assertDatabaseHas('users', [
            'sso_id' => 'e0000000-0000-0000-0000-000000000001',
            'email' => 'andi.pratama@univ.ac.id',
        ]);
    }

    public function test_callback_handles_iam_error_query(): void
    {
        $response = $this->get(route('auth.sso.callback', [
            'error' => 'access_denied',
            'error_description' => 'Pengguna membatalkan dialog izin.',
        ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
        $this->assertFalse(Auth::check());
    }

    public function test_callback_handles_missing_code(): void
    {
        $response = $this->get(route('auth.sso.callback'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Kode otorisasi dari IAM tidak ditemukan.');
        $this->assertFalse(Auth::check());
    }

    public function test_callback_handles_csrf_state_mismatch(): void
    {
        $response = $this->withSession(['sso_oauth_state' => 'original-state-123'])
            ->get(route('auth.sso.callback', [
                'code' => 'some-code',
                'state' => 'tampered-state-456',
            ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Validasi sesi SSO tidak cocok. Silakan coba masuk kembali.');
        $this->assertFalse(Auth::check());
    }

    public function test_callback_handles_token_exchange_failure(): void
    {
        $state = 'valid-test-state';

        Http::fake([
            'http://localhost:8000/oauth/token' => Http::response([
                'error' => 'invalid_grant',
                'message' => 'Authorization code has expired.',
            ], 400),
        ]);

        $response = $this->withSession(['sso_oauth_state' => $state])
            ->get(route('auth.sso.callback', [
                'code' => 'expired-code',
                'state' => $state,
            ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
        $this->assertFalse(Auth::check());
    }

    public function test_logout_invalidates_session_and_clears_auth(): void
    {
        $user = User::factory()->dosen()->create();

        $response = $this->actingAs($user)->post(route('auth.logout'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('info');
        $this->assertFalse(Auth::check());
    }
}
