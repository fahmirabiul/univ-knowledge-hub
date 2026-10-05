<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DownloadLog;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_all_tables(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, User::count());
        $this->assertGreaterThan(0, Publication::count());
        $this->assertGreaterThan(0, PublicationAuthor::count());
        $this->assertGreaterThan(0, AuditLog::count());
        $this->assertGreaterThan(0, DownloadLog::count());

        // Verify specific seeded items
        $this->assertDatabaseHas('categories', [
            'slug' => 'computer-science-artificial-intelligence',
            'scopus_quartile' => 'Q1',
            'sinta_tier' => 'S1',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin.lppm@univ.ac.id',
            'role' => 'admin_lppm',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'siti.rahmawati@univ.ac.id',
            'role' => 'dosen',
        ]);

        // Verify external author in seeded publications
        $this->assertDatabaseHas('publication_authors', [
            'is_external' => true,
            'author_name' => 'Prof. Elena Rostova, Ph.D.',
        ]);
    }
}
