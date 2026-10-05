<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\PublicationStatus;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DownloadLog;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationSchemaAndRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_can_be_created_with_full_metadata(): void
    {
        $dosen = User::factory()->dosen()->create();
        $category = Category::factory()->create();

        $publication = Publication::create([
            'submitter_id' => $dosen->id,
            'category_id' => $category->id,
            'doi' => '10.1145/3372278.3390678',
            'title' => 'Deep Learning for Edge Devices in Smart Agriculture',
            'abstract' => 'This research demonstrates edge inference for soil moisture anomaly detection.',
            'journal_name' => 'ACM Transactions on Internet of Things',
            'publication_year' => 2024,
            'volume' => '5',
            'issue' => '2',
            'pages' => '101-118',
            'status' => PublicationStatus::PENDING,
            'file_path' => 'publications/edge_devices_2024.pdf',
            'original_filename' => 'manuscript_v2.pdf',
            'file_size_bytes' => 1540200,
            'metadata_api' => [
                'DOI' => '10.1145/3372278.3390678',
                'publisher' => 'ACM',
            ],
            'citation_count' => 12,
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'doi' => '10.1145/3372278.3390678',
            'status' => 'pending',
            'publication_year' => 2024,
        ]);

        $this->assertInstanceOf(PublicationStatus::class, $publication->status);
        $this->assertTrue($publication->isPending());
        $this->assertFalse($publication->isApproved());
        $this->assertFalse($publication->isRejected());
        $this->assertIsArray($publication->metadata_api);
        $this->assertEquals('ACM', $publication->metadata_api['publisher']);
    }

    public function test_publication_doi_must_be_unique_if_provided(): void
    {
        $doi = '10.1000/182';
        Publication::factory()->create(['doi' => $doi]);

        $this->expectException(QueryException::class);
        Publication::factory()->create(['doi' => $doi]);
    }

    public function test_publication_submitter_and_category_relations(): void
    {
        $dosen = User::factory()->dosen()->create();
        $category = Category::factory()->create();
        $publication = Publication::factory()->create([
            'submitter_id' => $dosen->id,
            'category_id' => $category->id,
        ]);

        $this->assertEquals($dosen->id, $publication->submitter->id);
        $this->assertEquals($category->id, $publication->category->id);
    }

    public function test_publication_has_many_authors_and_author_users(): void
    {
        $pub = Publication::factory()->create();
        $coAuthorUser = User::factory()->dosen()->create();

        $author1 = PublicationAuthor::factory()->firstAuthor()->create([
            'publication_id' => $pub->id,
            'author_name' => 'Dr. Lead Researcher',
            'author_order' => 1,
        ]);

        $author2 = PublicationAuthor::factory()->create([
            'publication_id' => $pub->id,
            'user_id' => $coAuthorUser->id,
            'author_name' => $coAuthorUser->name,
            'author_order' => 2,
        ]);

        $this->assertEquals(2, $pub->authors->count());
        $this->assertEquals('Dr. Lead Researcher', $pub->first_author_name);
        $this->assertTrue($pub->authorUsers->contains($coAuthorUser));
    }

    public function test_publication_audit_and_download_log_relations(): void
    {
        $pub = Publication::factory()->create();

        $audit = AuditLog::factory()->create(['publication_id' => $pub->id]);
        $download = DownloadLog::factory()->create(['publication_id' => $pub->id]);

        $this->assertTrue($pub->auditLogs->contains($audit));
        $this->assertTrue($pub->downloadLogs->contains($download));
    }

    public function test_publication_status_scopes_and_search_scope(): void
    {
        $pubApproved = Publication::factory()->approved()->create([
            'title' => 'Quantum Computing for Optimization Problems',
            'abstract' => 'Comprehensive quantum annealing algorithm review.',
            'publication_year' => 2024,
        ]);

        $pubPending = Publication::factory()->pending()->create([
            'title' => 'Blockchain Consensus Protocols in Finance',
            'publication_year' => 2025,
        ]);

        $pubRejected = Publication::factory()->rejected()->create([
            'title' => 'Legacy Network Infrastructure',
            'publication_year' => 2023,
        ]);

        $this->assertEquals(1, Publication::approved()->count());
        $this->assertEquals(1, Publication::pending()->count());
        $this->assertEquals(1, Publication::rejected()->count());
        $this->assertEquals(1, Publication::byYear(2024)->count());

        // Search test
        $searchResults = Publication::search('Quantum')->get();
        $this->assertTrue($searchResults->contains($pubApproved));
        $this->assertFalse($searchResults->contains($pubPending));
    }

    public function test_deleting_publication_cascades_to_authors_and_logs(): void
    {
        $pub = Publication::factory()->create();
        $author = PublicationAuthor::factory()->create(['publication_id' => $pub->id]);
        $audit = AuditLog::factory()->create(['publication_id' => $pub->id]);
        $download = DownloadLog::factory()->create(['publication_id' => $pub->id]);

        $pub->delete();

        $this->assertDatabaseMissing('publications', ['id' => $pub->id]);
        $this->assertDatabaseMissing('publication_authors', ['id' => $author->id]);
        $this->assertDatabaseMissing('audit_logs', ['id' => $audit->id]);
        $this->assertDatabaseMissing('download_logs', ['id' => $download->id]);
    }
}
