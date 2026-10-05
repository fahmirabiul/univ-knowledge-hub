<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AcademicStatus;
use App\Enums\UserRole;
use App\Services\Auth\SsoAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.iam.base_url', 'http://localhost:8000');
        Config::set('services.iam.client_id', 'test-client-id');
        Config::set('services.iam.client_secret', 'test-client-secret');
        Config::set('services.iam.redirect_uri', 'http://localhost:8001/auth/sso/callback');
    }

    public function test_get_authorization_url_generates_valid_oauth2_url(): void
    {
        $service = new SsoAuthService;
        $url = $service->getAuthorizationUrl('custom-state-token');

        $this->assertStringStartsWith('http://localhost:8000/oauth/authorize?', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('redirect_uri='.urlencode('http://localhost:8001/auth/sso/callback'), $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('state=custom-state-token', $url);
    }

    public function test_exchange_code_for_token_sends_correct_payload(): void
    {
        Http::fake([
            'http://localhost:8000/oauth/token' => Http::response([
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'access_token' => 'mock-iam-access-token-12345',
            ], 200),
        ]);

        $service = new SsoAuthService;
        $token = $service->exchangeCodeForToken('auth-code-xyz');

        $this->assertEquals('mock-iam-access-token-12345', $token);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://localhost:8000/oauth/token'
                && $request['grant_type'] === 'authorization_code'
                && $request['code'] === 'auth-code-xyz'
                && $request['client_id'] === 'test-client-id'
                && $request['client_secret'] === 'test-client-secret';
        });
    }

    public function test_fetch_user_profile_fetches_and_extracts_data(): void
    {
        Http::fake([
            'http://localhost:8000/api/user' => Http::response([
                'data' => [
                    'id' => 'c0000000-0000-0000-0000-000000000099',
                    'name' => 'Prof. Dr. Anton Wijaya',
                    'email' => 'anton@univ.ac.id',
                    'role' => 'dosen',
                    'academic_status' => 'aktif',
                    'faculty' => 'Fakultas Ilmu Komputer',
                    'study_program' => 'Teknik Informatika',
                ],
            ], 200),
        ]);

        $service = new SsoAuthService;
        $profile = $service->fetchUserProfile('mock-access-token');

        $this->assertEquals('c0000000-0000-0000-0000-000000000099', $profile['id']);
        $this->assertEquals('Prof. Dr. Anton Wijaya', $profile['name']);
        $this->assertEquals('anton@univ.ac.id', $profile['email']);
    }

    public function test_sync_local_user_updates_or_creates_user_mirror(): void
    {
        $service = new SsoAuthService;

        $user = $service->syncLocalUser([
            'id' => 'c0000000-0000-0000-0000-000000000099',
            'name' => 'Prof. Dr. Anton Wijaya',
            'email' => 'anton@univ.ac.id',
            'role' => 'dosen',
            'academic_status' => 'aktif',
            'faculty' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Teknik Informatika',
            'avatar_url' => 'https://ui-avatars.com/api/?name=Anton',
        ]);

        $this->assertDatabaseHas('users', [
            'sso_id' => 'c0000000-0000-0000-0000-000000000099',
            'email' => 'anton@univ.ac.id',
            'name' => 'Prof. Dr. Anton Wijaya',
            'role' => 'dosen',
            'academic_status' => 'aktif',
        ]);

        $this->assertEquals(UserRole::DOSEN, $user->role);
        $this->assertEquals(AcademicStatus::AKTIF, $user->academic_status);

        // Test update existing user
        $updatedUser = $service->syncLocalUser([
            'id' => 'c0000000-0000-0000-0000-000000000099',
            'name' => 'Prof. Dr. Anton Wijaya, Ph.D.',
            'email' => 'anton@univ.ac.id',
            'role' => 'dosen',
            'academic_status' => 'cuti',
            'faculty' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Teknik Informatika',
        ]);

        $this->assertEquals($user->id, $updatedUser->id);
        $this->assertEquals('Prof. Dr. Anton Wijaya, Ph.D.', $updatedUser->name);
        $this->assertEquals(AcademicStatus::CUTI, $updatedUser->academic_status);
    }

    public function test_handle_callback_executes_full_sso_sync_pipeline(): void
    {
        Http::fake([
            'http://localhost:8000/oauth/token' => Http::response([
                'access_token' => 'access-token-abc',
            ], 200),
            'http://localhost:8000/api/user' => Http::response([
                'data' => [
                    'id' => 'd0000000-0000-0000-0000-000000000088',
                    'name' => 'Dr. Maya Kartika',
                    'email' => 'maya@univ.ac.id',
                    'role' => 'dosen',
                    'academic_status' => 'aktif',
                ],
            ], 200),
        ]);

        $service = new SsoAuthService;
        $user = $service->handleCallback('valid-auth-code');

        $this->assertDatabaseHas('users', [
            'sso_id' => 'd0000000-0000-0000-0000-000000000088',
            'email' => 'maya@univ.ac.id',
            'name' => 'Dr. Maya Kartika',
        ]);

        $this->assertEquals('d0000000-0000-0000-0000-000000000088', $user->sso_id);
    }
}
