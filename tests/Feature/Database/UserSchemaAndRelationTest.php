<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\AcademicStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\DownloadLog;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserSchemaAndRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_sso_id_and_valid_attributes(): void
    {
        $user = User::factory()->create([
            'sso_id' => (string) Str::uuid(),
            'name' => 'Dr. Bambang Sutrisno, M.Kom.',
            'email' => 'bambang@univ.ac.id',
            'role' => UserRole::DOSEN,
            'academic_status' => AcademicStatus::AKTIF,
            'faculty' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Informatika',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'bambang@univ.ac.id',
            'role' => 'dosen',
            'academic_status' => 'aktif',
        ]);

        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertInstanceOf(AcademicStatus::class, $user->academic_status);
        $this->assertTrue($user->isDosen());
        $this->assertFalse($user->isAdminLPPM());
        $this->assertFalse($user->isMahasiswa());
        $this->assertTrue($user->canSubmitPublication());
    }

    public function test_user_sso_id_must_be_unique(): void
    {
        $ssoId = (string) Str::uuid();

        User::factory()->create(['sso_id' => $ssoId, 'email' => 'first@univ.ac.id']);

        $this->expectException(QueryException::class);
        User::factory()->create(['sso_id' => $ssoId, 'email' => 'second@univ.ac.id']);
    }

    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'duplicate@univ.ac.id']);

        $this->expectException(QueryException::class);
        User::factory()->create(['email' => 'duplicate@univ.ac.id']);
    }

    public function test_user_role_and_status_helper_methods(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $this->assertTrue($admin->isAdminLPPM());
        $this->assertTrue($admin->canReviewPublication());

        $mahasiswa = User::factory()->mahasiswa()->create();
        $this->assertTrue($mahasiswa->isMahasiswa());
        $this->assertFalse($mahasiswa->canSubmitPublication());
        $this->assertFalse($mahasiswa->canReviewPublication());

        $dosenCuti = User::factory()->dosen()->cuti()->create();
        $this->assertFalse($dosenCuti->canSubmitPublication());
    }

    public function test_user_scopes(): void
    {
        User::factory()->count(3)->dosen()->create();
        User::factory()->count(2)->adminLppm()->create();
        User::factory()->count(4)->mahasiswa()->create();
        User::factory()->dosen()->cuti()->create();

        $this->assertEquals(4, User::dosen()->count());
        $this->assertEquals(2, User::adminLppm()->count());
        $this->assertEquals(9, User::active()->count());
    }

    public function test_user_has_many_submitted_publications(): void
    {
        $dosen = User::factory()->dosen()->create();
        $publication = Publication::factory()->create(['submitter_id' => $dosen->id]);

        $this->assertTrue($dosen->publications->contains($publication));
        $this->assertEquals(1, $dosen->publications->count());
    }

    public function test_user_authored_publications_many_to_many_relation(): void
    {
        $dosen = User::factory()->dosen()->create();
        $publication = Publication::factory()->create();

        PublicationAuthor::factory()->create([
            'publication_id' => $publication->id,
            'user_id' => $dosen->id,
            'author_name' => $dosen->name,
            'author_email' => $dosen->email,
            'affiliation' => $dosen->faculty,
        ]);

        $this->assertTrue($dosen->authoredPublications->contains($publication));
        $this->assertEquals(1, $dosen->authorProfiles->count());
    }

    public function test_user_audit_and_download_log_relations(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $publication = Publication::factory()->create();

        $auditLog = AuditLog::factory()->create([
            'publication_id' => $publication->id,
            'actor_id' => $admin->id,
        ]);

        $this->assertTrue($admin->auditLogs->contains($auditLog));

        $mahasiswa = User::factory()->mahasiswa()->create();
        $downloadLog = DownloadLog::factory()->create([
            'publication_id' => $publication->id,
            'user_id' => $mahasiswa->id,
        ]);

        $this->assertTrue($mahasiswa->downloadLogs->contains($downloadLog));
    }
}
