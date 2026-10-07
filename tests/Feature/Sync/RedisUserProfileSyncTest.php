<?php

declare(strict_types=1);

namespace Tests\Feature\Sync;

use App\Enums\AcademicStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Sync\UserProfileSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RedisUserProfileSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_service_updates_existing_user_from_redis_json_payload(): void
    {
        $user = User::factory()->dosen()->create([
            'sso_id' => '11111111-2222-3333-4444-555555555555',
            'name' => 'Dr. Nama Lama',
            'email' => 'lama@univ.ac.id',
            'academic_status' => AcademicStatus::AKTIF,
        ]);

        $payload = json_encode([
            'sso_id' => '11111111-2222-3333-4444-555555555555',
            'name' => 'Prof. Dr. Nama Baru, M.Sc.',
            'email' => 'baru@univ.ac.id',
            'role' => 'dosen',
            'academic_status' => 'aktif',
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Teknik Elektro',
        ], JSON_THROW_ON_ERROR);

        $service = new UserProfileSyncService;
        $syncedUser = $service->handle($payload);

        $this->assertNotNull($syncedUser);
        $this->assertEquals($user->id, $syncedUser->id);
        $this->assertEquals('Prof. Dr. Nama Baru, M.Sc.', $syncedUser->name);
        $this->assertEquals('baru@univ.ac.id', $syncedUser->email);
        $this->assertEquals('Fakultas Teknik', $syncedUser->faculty);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Prof. Dr. Nama Baru, M.Sc.',
            'email' => 'baru@univ.ac.id',
        ]);
    }

    public function test_sync_service_creates_new_user_mirror_if_not_exists(): void
    {
        $payload = json_encode([
            'sso_id' => '99999999-8888-7777-6666-555555555555',
            'name' => 'Dosen Baru Datang',
            'email' => 'baru.datang@univ.ac.id',
            'role' => 'dosen',
            'academic_status' => 'aktif',
            'faculty' => 'Fakultas Kedokteran',
            'study_program' => 'Ilmu Bedah',
        ], JSON_THROW_ON_ERROR);

        $service = new UserProfileSyncService;
        $syncedUser = $service->handle($payload);

        $this->assertNotNull($syncedUser);
        $this->assertDatabaseHas('users', [
            'sso_id' => '99999999-8888-7777-6666-555555555555',
            'email' => 'baru.datang@univ.ac.id',
            'name' => 'Dosen Baru Datang',
            'faculty' => 'Fakultas Kedokteran',
        ]);
    }

    public function test_sync_service_handles_wrapped_event_payload(): void
    {
        $payload = [
            'event' => 'university.user.profile_updated',
            'data' => [
                'sso_id' => '22222222-3333-4444-5555-666666666666',
                'name' => 'Dr. Rina Novita',
                'email' => 'rina@univ.ac.id',
                'role' => 'dosen',
                'academic_status' => 'cuti',
            ],
        ];

        $service = new UserProfileSyncService;
        $syncedUser = $service->handle($payload);

        $this->assertNotNull($syncedUser);
        $this->assertEquals(AcademicStatus::CUTI, $syncedUser->academic_status);
    }

    public function test_sync_service_handles_indonesian_keys(): void
    {
        $payload = json_encode([
            'sso_id' => '33333333-4444-5555-6666-777777777777',
            'nama' => 'Ir. Hartono, M.T.',
            'email' => 'hartono@univ.ac.id',
            'role_lokal' => 'dosen',
            'status_akademik' => 'nonaktif',
            'fakultas' => 'Fakultas MIPA',
            'program_studi' => 'Fisika',
        ], JSON_THROW_ON_ERROR);

        $service = new UserProfileSyncService;
        $syncedUser = $service->handle($payload);

        $this->assertNotNull($syncedUser);
        $this->assertEquals('Ir. Hartono, M.T.', $syncedUser->name);
        $this->assertEquals(UserRole::DOSEN, $syncedUser->role);
        $this->assertEquals(AcademicStatus::NONAKTIF, $syncedUser->academic_status);
        $this->assertEquals('Fakultas MIPA', $syncedUser->faculty);
        $this->assertEquals('Fisika', $syncedUser->study_program);
    }

    public function test_sync_service_safely_ignores_invalid_json_or_missing_keys(): void
    {
        $service = new UserProfileSyncService;

        $this->assertNull($service->handle('broken-not-a-json'));
        $this->assertNull($service->handle(json_encode(['name' => 'No ID User'], JSON_THROW_ON_ERROR)));
        $this->assertNull($service->handle(json_encode(['sso_id' => 'only-id-no-email'], JSON_THROW_ON_ERROR)));
    }

    public function test_redis_listen_users_command_is_registered_in_artisan(): void
    {
        $commands = Artisan::all();
        $this->assertArrayHasKey('redis:listen-users', $commands);
    }

    public function test_sync_service_maps_admin_lppm_and_reconciles_by_email(): void
    {
        $seededAdmin = User::factory()->create([
            'sso_id' => 'a0000000-0000-0000-0000-000000000001',
            'email' => 'admin.lppm@univ.ac.id',
            'role' => UserRole::ADMIN_LPPM,
        ]);

        $payload = json_encode([
            'sso_id' => '77777777-6666-5555-4444-333333333333',
            'email' => 'admin.lppm@univ.ac.id',
            'name' => 'Dr. Ir. Hendra Gunawan, M.T.',
            'role_global' => 'admin_lppm',
            'academic_status' => 'aktif',
        ], JSON_THROW_ON_ERROR);

        $service = new UserProfileSyncService;
        $syncedUser = $service->handle($payload);

        $this->assertNotNull($syncedUser);
        $this->assertEquals($seededAdmin->id, $syncedUser->id);
        $this->assertEquals('77777777-6666-5555-4444-333333333333', $syncedUser->sso_id);
        $this->assertEquals(UserRole::ADMIN_LPPM, $syncedUser->role);
    }

    public function test_sync_service_maps_admin_lppm_from_unit_and_is_admin_flag(): void
    {
        $payload = [
            'event' => 'UserProfileUpdated',
            'timestamp' => '2026-10-07T14:10:36Z',
            'data' => [
                'sso_id' => '987e6543-e21b-43d2-b654-426614174999',
                'email' => 'admin.lppm@univ.ac.id',
                'nama_lengkap' => 'Suryo Utomo, S.T.',
                'role_global' => 'karyawan',
                'is_admin' => true,
                'unit' => [
                    'id' => 3,
                    'kode' => '503',
                    'nama' => 'LPPM',
                ],
            ],
        ];

        $service = new UserProfileSyncService;
        $syncedUser = $service->handle($payload);

        $this->assertNotNull($syncedUser);
        $this->assertEquals(UserRole::ADMIN_LPPM, $syncedUser->role);
        $this->assertEquals('Suryo Utomo, S.T.', $syncedUser->name);
        $this->assertEquals('admin.lppm@univ.ac.id', $syncedUser->email);
    }

    public function test_sync_service_updates_existing_user_without_email_in_payload(): void
    {
        $user = User::factory()->dosen()->create([
            'sso_id' => '01924b8e-9999-7000-8000-000000000001',
            'name' => 'Nama Lama',
            'email' => 'dosen.asli@univ.ac.id',
        ]);

        $payload = [
            'event' => 'UserProfileUpdated',
            'data' => [
                'sso_id' => '01924b8e-9999-7000-8000-000000000001',
                'nama_lengkap' => 'Nama Baru Update',
                'role_global' => 'dosen',
            ],
        ];

        $service = new UserProfileSyncService;
        $syncedUser = $service->handle($payload);

        $this->assertNotNull($syncedUser);
        $this->assertEquals($user->id, $syncedUser->id);
        $this->assertEquals('Nama Baru Update', $syncedUser->name);
        $this->assertEquals('dosen.asli@univ.ac.id', $syncedUser->email);
    }

    public function test_sync_service_maps_admin_lppm_from_roles_array(): void
    {
        $payload = json_encode([
            'sso_id' => '01924b8e-5555-7000-8000-000000000099',
            'email' => 'staff.lppm@univ.ac.id',
            'role_global' => 'karyawan',
            'roles' => ['karyawan', 'admin_lppm'],
            'profil' => [
                'nama_lengkap' => 'Suryo Utomo, S.T.',
                'nomor_induk' => 'LPPM-202001',
                'status_akademik' => 'aktif',
            ],
        ], JSON_THROW_ON_ERROR);

        $service = new UserProfileSyncService;
        $syncedUser = $service->handle($payload);

        $this->assertNotNull($syncedUser);
        $this->assertEquals(UserRole::ADMIN_LPPM, $syncedUser->role);
        $this->assertEquals('Suryo Utomo, S.T.', $syncedUser->name);
    }
}
