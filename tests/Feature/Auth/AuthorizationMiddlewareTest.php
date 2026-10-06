<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AcademicStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthorizationMiddlewareTest extends TestCase
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

        // Define test routes for middleware testing
        Route::get('/test-dosen-only', fn () => response('OK Dosen'))
            ->middleware(['web', 'role:dosen']);

        Route::get('/test-lppm-only', fn () => response('OK LPPM'))
            ->middleware(['web', 'role:admin_lppm']);

        Route::get('/test-multi-role', fn () => response('OK Multi'))
            ->middleware(['web', 'role:dosen,admin_lppm']);

        Route::get('/test-active-only', fn () => response('OK Active'))
            ->middleware(['web', 'active']);
    }

    public function test_guest_is_redirected_to_sso_login_when_accessing_role_protected_route(): void
    {
        $response = $this->get('/test-dosen-only');

        $response->assertRedirect(route('auth.sso.redirect'));
    }

    public function test_user_with_allowed_role_can_access_route(): void
    {
        $dosen = User::factory()->dosen()->create();

        $response = $this->actingAs($dosen)->get('/test-dosen-only');

        $response->assertOk();
        $response->assertSee('OK Dosen');
    }

    public function test_user_with_unauthorized_role_gets_403_forbidden(): void
    {
        $mahasiswa = User::factory()->mahasiswa()->create();

        $response = $this->actingAs($mahasiswa)->get('/test-dosen-only');

        $response->assertForbidden();
    }

    public function test_multi_role_middleware_allows_multiple_roles(): void
    {
        $dosen = User::factory()->dosen()->create();
        $admin = User::factory()->adminLppm()->create();
        $mahasiswa = User::factory()->mahasiswa()->create();

        $this->actingAs($dosen)->get('/test-multi-role')->assertOk();
        $this->actingAs($admin)->get('/test-multi-role')->assertOk();
        $this->actingAs($mahasiswa)->get('/test-multi-role')->assertForbidden();
    }

    public function test_active_user_can_access_active_protected_route(): void
    {
        $user = User::factory()->dosen()->create(['academic_status' => AcademicStatus::AKTIF]);

        $response = $this->actingAs($user)->get('/test-active-only');

        $response->assertOk();
        $response->assertSee('OK Active');
    }

    public function test_cuti_user_can_still_access_active_route(): void
    {
        $user = User::factory()->dosen()->create(['academic_status' => AcademicStatus::CUTI]);

        $response = $this->actingAs($user)->get('/test-active-only');

        $response->assertOk();
    }

    public function test_nonaktif_user_is_logged_out_and_redirected_with_error(): void
    {
        $user = User::factory()->dosen()->create(['academic_status' => AcademicStatus::NONAKTIF]);

        $response = $this->actingAs($user)->get('/test-active-only');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_sso_callback_rejects_login_if_user_academic_status_is_nonaktif(): void
    {
        $state = 'state-token-nonaktif-test';

        Http::fake([
            'http://localhost:8000/oauth/token' => Http::response([
                'access_token' => 'mock-token-nonaktif',
            ], 200),
            'http://localhost:8000/api/user' => Http::response([
                'data' => [
                    'id' => 'f0000000-0000-0000-0000-000000000001',
                    'name' => 'Almarhum / Dosen Nonaktif',
                    'email' => 'nonaktif@univ.ac.id',
                    'role' => 'dosen',
                    'academic_status' => 'nonaktif',
                ],
            ], 200),
        ]);

        $response = $this->withSession(['sso_oauth_state' => $state])
            ->get(route('auth.sso.callback', [
                'code' => 'code-for-nonaktif-user',
                'state' => $state,
            ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Akun Anda berstatus Non-Aktif. Akses masuk tidak diizinkan.');
        $this->assertGuest();
    }
}
