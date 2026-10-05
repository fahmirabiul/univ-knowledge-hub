<?php

declare(strict_types=1);

namespace Tests\Feature\Publication;

use App\Enums\AuthorRole;
use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\User;
use App\Services\Publication\CrossrefMetadataService;
use App\Services\Publication\PublicationService;
use App\Services\Publication\PublicationStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PublicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->service = new PublicationService(
            new PublicationStorageService,
            new CrossrefMetadataService
        );
    }

    public function test_create_publication_generates_registration_code_and_saves_data(): void
    {
        $dosen = User::factory()->dosen()->create();
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->create('manuscript.pdf', 1024, 'application/pdf');

        $coAuthor = User::factory()->dosen()->create();

        $data = [
            'category_id' => $category->id,
            'doi' => '10.1145/123456.789012',
            'isbn_issn' => '2160-9306',
            'title' => 'Edge AI Computing for Smart Grids',
            'abstract' => 'Comprehensive abstract describing decentralized edge consensus.',
            'journal_name' => 'IEEE Transactions on Smart Grid',
            'publication_year' => 2025,
            'volume' => '16',
            'issue' => '3',
            'pages' => '200-215',
            'authors' => [
                [
                    'user_id' => $dosen->id,
                    'author_name' => $dosen->name,
                    'author_email' => $dosen->email,
                    'affiliation' => $dosen->faculty,
                    'author_role' => AuthorRole::FIRST_CORRESPONDING->value,
                    'author_order' => 1,
                    'is_corresponding' => true,
                    'is_external' => false,
                ],
                [
                    'user_id' => $coAuthor->id,
                    'author_name' => $coAuthor->name,
                    'author_email' => $coAuthor->email,
                    'affiliation' => $coAuthor->faculty,
                    'author_role' => AuthorRole::CO_AUTHOR->value,
                    'author_order' => 2,
                    'is_corresponding' => false,
                    'is_external' => false,
                ],
                [
                    'user_id' => null,
                    'author_name' => 'Prof. Dr. John Doe',
                    'author_email' => 'johndoe@mit.edu',
                    'affiliation' => 'Massachusetts Institute of Technology',
                    'author_role' => AuthorRole::CO_AUTHOR->value,
                    'author_order' => 3,
                    'is_corresponding' => false,
                    'is_external' => true,
                ],
            ],
        ];

        $pub = $this->service->createPublication($dosen, $data, $file);

        $this->assertNotNull($pub);
        $this->assertStringStartsWith('PUB-'.date('Y').'-', $pub->registration_code);
        $this->assertEquals(PublicationStatus::PENDING, $pub->status);
        $this->assertEquals('10.1145/123456.789012', $pub->doi);
        $this->assertEquals('2160-9306', $pub->isbn_issn);
        $this->assertEquals($dosen->id, $pub->submitter_id);
        $this->assertEquals('manuscript.pdf', $pub->original_filename);

        $this->assertCount(3, $pub->authors);
        $this->assertEquals(1, $pub->authors->first()->author_order);
        $this->assertTrue($pub->authors->first()->is_corresponding);
        $this->assertTrue($pub->authors->last()->is_external);

        Storage::assertExists($pub->file_path);
    }

    public function test_update_publication_replaces_file_and_updates_authors(): void
    {
        $dosen = User::factory()->dosen()->create();
        $oldFile = UploadedFile::fake()->create('old.pdf', 500, 'application/pdf');

        $pub = $this->service->createPublication($dosen, [
            'title' => 'Original Title',
            'journal_name' => 'Original Journal',
            'publication_year' => 2024,
            'abstract' => 'Old abstract',
        ], $oldFile);

        $oldFilePath = $pub->file_path;
        Storage::assertExists($oldFilePath);

        $newFile = UploadedFile::fake()->create('updated_version.pdf', 800, 'application/pdf');
        $updatedPub = $this->service->updatePublication($pub, [
            'title' => 'Revised Title of Research',
            'journal_name' => 'Applied Soft Computing',
            'publication_year' => 2025,
            'abstract' => 'Updated abstract',
        ], $newFile);

        $this->assertEquals('Revised Title of Research', $updatedPub->title);
        $this->assertEquals('updated_version.pdf', $updatedPub->original_filename);

        // Old file should be deleted and new file stored
        Storage::assertMissing($oldFilePath);
        Storage::assertExists($updatedPub->file_path);
    }
}
