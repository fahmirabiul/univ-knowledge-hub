<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\DownloadLog;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadLogSchemaAndRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_log_tracks_authenticated_user_download(): void
    {
        $mahasiswa = User::factory()->mahasiswa()->create();
        $pub = Publication::factory()->approved()->create();

        $log = DownloadLog::create([
            'publication_id' => $pub->id,
            'user_id' => $mahasiswa->id,
            'ip_address' => '10.0.0.15',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'downloaded_at' => now(),
        ]);

        $this->assertDatabaseHas('download_logs', [
            'id' => $log->id,
            'publication_id' => $pub->id,
            'user_id' => $mahasiswa->id,
        ]);

        $this->assertEquals($pub->id, $log->publication->id);
        $this->assertEquals($mahasiswa->id, $log->user->id);
    }

    public function test_download_log_tracks_guest_download(): void
    {
        $pub = Publication::factory()->approved()->create();

        $log = DownloadLog::create([
            'publication_id' => $pub->id,
            'user_id' => null,
            'ip_address' => '203.0.113.50',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'downloaded_at' => now(),
        ]);

        $this->assertDatabaseHas('download_logs', [
            'id' => $log->id,
            'publication_id' => $pub->id,
            'user_id' => null,
            'ip_address' => '203.0.113.50',
        ]);

        $this->assertNull($log->user);
    }
}
