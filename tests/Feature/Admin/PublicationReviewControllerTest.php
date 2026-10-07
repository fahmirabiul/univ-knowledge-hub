<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\PublicationStatus;
use App\Models\AuditLog;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_sso_when_accessing_admin_review(): void
    {
        $response = $this->get(route('admin.review.index'));

        $response->assertRedirect(route('auth.sso.redirect'));
    }

    public function test_dosen_is_forbidden_from_admin_review_queue(): void
    {
        $dosen = User::factory()->dosen()->create();

        $response = $this->actingAs($dosen)->get(route('admin.review.index'));

        $response->assertForbidden();
    }

    public function test_admin_lppm_can_access_review_queue_and_sees_pending_publications(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $pendingPub = Publication::factory()->pending()->create(['title' => 'Naskah Riset Pending']);
        $approvedPub = Publication::factory()->approved()->create(['title' => 'Naskah Riset Terbit']);

        $response = $this->actingAs($admin)->get(route('admin.review.index'));

        $response->assertOk();
        $response->assertViewIs('admin.review.index');
        $response->assertSee('Naskah Riset Pending');
        // By default status filter is 'pending', so approved paper is not in the pending tab
        $response->assertDontSee('Naskah Riset Terbit');
    }

    public function test_admin_lppm_can_approve_pending_publication(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $pub = Publication::factory()->pending()->create();

        $response = $this->actingAs($admin)->post(route('admin.review.approve', $pub), [
            'notes' => 'Dokumen naskah lengkap dan telah sesuai ketentuan LPPM.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pub->refresh();
        $this->assertEquals(PublicationStatus::APPROVED, $pub->status);
        $this->assertTrue($pub->isApproved());
        $this->assertNotNull($pub->published_at);

        $this->assertDatabaseHas('audit_logs', [
            'publication_id' => $pub->id,
            'actor_id' => $admin->id,
            'status_before' => 'pending',
            'status_after' => 'approved',
            'notes' => 'Dokumen naskah lengkap dan telah sesuai ketentuan LPPM.',
        ]);
    }

    public function test_admin_lppm_can_reject_publication_with_valid_reason(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $pub = Publication::factory()->pending()->create();

        $rejectionReason = 'Format sitasi belum mengikuti APA 7th edition dan berkas PDF terpotong.';
        $response = $this->actingAs($admin)->post(route('admin.review.reject', $pub), [
            'reason' => $rejectionReason,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pub->refresh();
        $this->assertEquals(PublicationStatus::REJECTED, $pub->status);
        $this->assertTrue($pub->isRejected());
        $this->assertEquals($rejectionReason, $pub->rejection_reason);

        $this->assertDatabaseHas('audit_logs', [
            'publication_id' => $pub->id,
            'actor_id' => $admin->id,
            'status_before' => 'pending',
            'status_after' => 'rejected',
            'notes' => $rejectionReason,
        ]);
    }

    public function test_admin_lppm_cannot_reject_without_reason_or_with_short_reason(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $pub = Publication::factory()->pending()->create();

        $response = $this->actingAs($admin)->post(route('admin.review.reject', $pub), [
            'reason' => 'Pendek',
        ]);

        $response->assertSessionHasErrors(['reason']);

        $pub->refresh();
        $this->assertEquals(PublicationStatus::PENDING, $pub->status);
    }

    public function test_admin_lppm_can_fetch_audit_logs_json(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $pub = Publication::factory()->create();

        AuditLog::create([
            'publication_id' => $pub->id,
            'actor_id' => $admin->id,
            'status_before' => 'pending',
            'status_after' => 'approved',
            'notes' => 'Catatan audit pertama.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.review.audit', $pub));

        $response->assertOk();
        $response->assertJsonStructure([
            'publication_id',
            'registration_code',
            'title',
            'current_status',
            'logs' => [
                '*' => [
                    'id',
                    'actor_name',
                    'actor_role',
                    'status_before',
                    'status_after',
                    'notes',
                    'created_at_human',
                    'created_at_formatted',
                ],
            ],
        ]);

        $response->assertJsonFragment([
            'actor_name' => $admin->name,
            'notes' => 'Catatan audit pertama.',
        ]);
    }
}
