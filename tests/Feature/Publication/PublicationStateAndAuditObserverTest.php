<?php

declare(strict_types=1);

namespace Tests\Feature\Publication;

use App\Enums\PublicationStatus;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\AuditLog;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationStateAndAuditObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_publication_can_be_approved_by_admin_lppm_and_records_audit_log(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $publication = Publication::factory()->pending()->create();

        $publication->approve($admin, 'Kelengkapan naskah dan DOI telah terverifikasi LPPM.');

        $publication->refresh();

        $this->assertEquals(PublicationStatus::APPROVED, $publication->status);
        $this->assertTrue($publication->isApproved());
        $this->assertNotNull($publication->published_at);
        $this->assertNull($publication->rejection_reason);

        $this->assertDatabaseHas('audit_logs', [
            'publication_id' => $publication->id,
            'actor_id' => $admin->id,
            'status_before' => 'pending',
            'status_after' => 'approved',
            'notes' => 'Kelengkapan naskah dan DOI telah terverifikasi LPPM.',
        ]);

        $this->assertEquals(1, $publication->auditLogs()->count());
        $this->assertEquals($admin->id, $publication->auditLogs()->first()->actor->id);
    }

    public function test_non_admin_cannot_approve_publication(): void
    {
        $dosen = User::factory()->dosen()->create();
        $publication = Publication::factory()->pending()->create();

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Hanya Admin LPPM yang memiliki wewenang untuk menyetujui publikasi ilmiah.');

        $publication->approve($dosen);
    }

    public function test_pending_publication_can_be_rejected_by_admin_lppm_and_records_audit_log(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $publication = Publication::factory()->pending()->create();

        $rejectionNotes = 'Format lampiran PDF dan metadata jurnal belum sesuai standar akreditasi.';
        $publication->reject($admin, $rejectionNotes);

        $publication->refresh();

        $this->assertEquals(PublicationStatus::REJECTED, $publication->status);
        $this->assertTrue($publication->isRejected());
        $this->assertEquals($rejectionNotes, $publication->rejection_reason);

        $this->assertDatabaseHas('audit_logs', [
            'publication_id' => $publication->id,
            'actor_id' => $admin->id,
            'status_before' => 'pending',
            'status_after' => 'rejected',
            'notes' => $rejectionNotes,
        ]);
    }

    public function test_reject_requires_non_empty_reason(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $publication = Publication::factory()->pending()->create();

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Alasan penolakan publikasi wajib diisi.');

        $publication->reject($admin, '   ');
    }

    public function test_rejected_publication_cannot_be_approved_directly_enforcing_tdd_constraint(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $publication = Publication::factory()->rejected()->create();

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Publikasi yang berstatus ditolak tidak dapat langsung disetujui');

        $publication->approve($admin);
    }

    public function test_direct_illegal_status_update_on_eloquent_is_blocked_by_observer(): void
    {
        $publication = Publication::factory()->rejected()->create();

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Transisi status publikasi dari [rejected] ke [approved] tidak diizinkan');

        // Attempting direct Eloquent status change bypassing state methods
        $publication->status = PublicationStatus::APPROVED;
        $publication->save();
    }

    public function test_rejected_publication_can_be_resubmitted_by_submitter_and_records_audit_log(): void
    {
        $submitter = User::factory()->dosen()->create();
        $publication = Publication::factory()->rejected()->create([
            'submitter_id' => $submitter->id,
        ]);

        $resubmitNotes = 'Naskah versi revisi dengan perbaikan referensi telah diunggah.';
        $publication->resubmit($submitter, $resubmitNotes);

        $publication->refresh();

        $this->assertEquals(PublicationStatus::PENDING, $publication->status);
        $this->assertTrue($publication->isPending());

        $this->assertDatabaseHas('audit_logs', [
            'publication_id' => $publication->id,
            'actor_id' => $submitter->id,
            'status_before' => 'rejected',
            'status_after' => 'pending',
            'notes' => $resubmitNotes,
        ]);
    }

    public function test_non_submitter_cannot_resubmit_rejected_publication(): void
    {
        $submitter = User::factory()->dosen()->create();
        $otherDosen = User::factory()->dosen()->create();

        $publication = Publication::factory()->rejected()->create([
            'submitter_id' => $submitter->id,
        ]);

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Hanya dosen pengusul naskah yang berwenang');

        $publication->resubmit($otherDosen);
    }

    public function test_approved_publication_is_final_and_cannot_be_modified(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $publication = Publication::factory()->approved()->create();

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Publikasi sudah berstatus disetujui');

        $publication->approve($admin);
    }

    public function test_approved_publication_cannot_be_rejected(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $publication = Publication::factory()->approved()->create();

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage('Publikasi yang telah disetujui LPPM bersifat final');

        $publication->reject($admin, 'Coba tolak publikasi');
    }
}
