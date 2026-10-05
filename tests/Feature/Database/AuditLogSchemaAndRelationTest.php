<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\PublicationStatus;
use App\Models\AuditLog;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogSchemaAndRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_records_status_transitions_and_reviewer_notes(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $pub = Publication::factory()->pending()->create();

        $audit = AuditLog::create([
            'publication_id' => $pub->id,
            'actor_id' => $admin->id,
            'status_before' => PublicationStatus::PENDING->value,
            'status_after' => PublicationStatus::APPROVED->value,
            'notes' => 'Kelengkapan dokumen telah diverifikasi dan memenuhi standar repositori LPPM.',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $audit->id,
            'publication_id' => $pub->id,
            'actor_id' => $admin->id,
            'status_before' => 'pending',
            'status_after' => 'approved',
        ]);

        $this->assertEquals($pub->id, $audit->publication->id);
        $this->assertEquals($admin->id, $audit->actor->id);
    }

    public function test_deleting_actor_user_sets_audit_log_actor_id_to_null(): void
    {
        $admin = User::factory()->adminLppm()->create();
        $pub = Publication::factory()->create();

        $audit = AuditLog::factory()->create([
            'publication_id' => $pub->id,
            'actor_id' => $admin->id,
        ]);

        $admin->delete();

        $this->assertDatabaseHas('audit_logs', [
            'id' => $audit->id,
            'actor_id' => null,
        ]);
    }
}
