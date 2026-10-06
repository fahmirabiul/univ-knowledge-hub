<?php

declare(strict_types=1);

namespace Tests\Feature\Publication;

use App\Enums\AuthorRole;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_guest_is_redirected_when_accessing_publications(): void
    {
        $response = $this->get(route('publications.index'));
        $response->assertRedirect(route('auth.sso.redirect'));
    }

    public function test_mahasiswa_is_forbidden_from_dosen_workspace(): void
    {
        $mahasiswa = User::factory()->mahasiswa()->create();

        $response = $this->actingAs($mahasiswa)->get(route('publications.index'));
        $response->assertForbidden();
    }

    public function test_dosen_can_access_index_and_sees_only_own_publications(): void
    {
        $dosen1 = User::factory()->dosen()->create();
        $dosen2 = User::factory()->dosen()->create();

        $pub1 = Publication::factory()->create(['submitter_id' => $dosen1->id, 'title' => 'Dosen 1 Unique Title']);
        $pub2 = Publication::factory()->create(['submitter_id' => $dosen2->id, 'title' => 'Dosen 2 Unique Title']);

        $response = $this->actingAs($dosen1)->get(route('publications.index'));

        $response->assertOk();
        $response->assertViewIs('publications.index');
        $response->assertViewHas('publications');

        $publications = $response->viewData('publications');
        $this->assertTrue($publications->contains($pub1));
        $this->assertFalse($publications->contains($pub2));
    }

    public function test_dosen_can_access_create_form(): void
    {
        $dosen = User::factory()->dosen()->create();
        $category = Category::factory()->create(['name' => 'Informatika', 'is_active' => true]);

        $response = $this->actingAs($dosen)->get(route('publications.create'));

        $response->assertOk();
        $response->assertViewIs('publications.create');
        $response->assertViewHas('categories');

        $categories = $response->viewData('categories');
        $this->assertTrue($categories->contains($category));
    }

    public function test_dosen_can_store_publication_with_file_and_authors(): void
    {
        $dosen = User::factory()->dosen()->create();
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->create('manuscript.pdf', 1024, 'application/pdf');

        $payload = [
            'category_id' => $category->id,
            'doi' => '10.1145/3372278.3390678',
            'isbn_issn' => '0162-8828',
            'title' => 'Deep Neural Networks for Medical Imaging',
            'abstract' => 'Detailed research abstract regarding segmentation.',
            'journal_name' => 'IEEE Transactions on Pattern Analysis',
            'publication_year' => 2025,
            'volume' => '47',
            'issue' => '1',
            'pages' => '10-25',
            'file' => $file,
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
                    'user_id' => null,
                    'author_name' => 'Prof. Elena Rostova',
                    'author_email' => 'elena@oxford.ac.uk',
                    'affiliation' => 'University of Oxford',
                    'author_role' => AuthorRole::CO_AUTHOR->value,
                    'author_order' => 2,
                    'is_corresponding' => false,
                    'is_external' => true,
                ],
            ],
        ];

        $response = $this->actingAs($dosen)->post(route('publications.store'), $payload);

        $response->assertRedirect(route('publications.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('publications', [
            'submitter_id' => $dosen->id,
            'title' => 'Deep Neural Networks for Medical Imaging',
            'doi' => '10.1145/3372278.3390678',
            'status' => 'pending',
        ]);

        $pub = Publication::where('doi', '10.1145/3372278.3390678')->first();
        $this->assertNotNull($pub);
        $this->assertCount(2, $pub->authors);
        $this->assertStringStartsWith('PUB-2026-', $pub->registration_code);
        Storage::disk('local')->assertExists($pub->file_path);
    }

    public function test_store_validation_rejects_non_pdf_file(): void
    {
        $dosen = User::factory()->dosen()->create();
        $file = UploadedFile::fake()->create('invalid.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $payload = [
            'title' => 'Invalid Document',
            'abstract' => 'Sample abstract',
            'journal_name' => 'Sample Journal',
            'publication_year' => 2025,
            'file' => $file,
        ];

        $response = $this->actingAs($dosen)->post(route('publications.store'), $payload);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseMissing('publications', ['title' => 'Invalid Document']);
    }

    public function test_doi_fetch_endpoint_returns_json_metadata(): void
    {
        $dosen = User::factory()->dosen()->create();

        Http::fake([
            'https://api.crossref.org/works/*' => Http::response([
                'status' => 'ok',
                'message' => [
                    'DOI' => '10.1145/3372278.3390678',
                    'title' => ['Learning to Select Elements for Graphic Design'],
                    'container-title' => ['ACM ICMR 2020'],
                    'published' => ['date-parts' => [[2020, 6, 8]]],
                    'author' => [
                        ['given' => 'Guolong', 'family' => 'Wang', 'sequence' => 'first'],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($dosen)->getJson(route('publications.doi.fetch', ['doi' => '10.1145/3372278.3390678']));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'doi' => '10.1145/3372278.3390678',
                'title' => 'Learning to Select Elements for Graphic Design',
                'journal_name' => 'ACM ICMR 2020',
                'publication_year' => 2020,
            ],
        ]);
    }

    public function test_doi_fetch_endpoint_returns_404_when_doi_not_found(): void
    {
        $dosen = User::factory()->dosen()->create();

        Http::fake([
            'https://api.crossref.org/works/*' => Http::response(['status' => 'not-found'], 404),
        ]);

        $response = $this->actingAs($dosen)->getJson(route('publications.doi.fetch', ['doi' => '10.9999/unknown']));

        $response->assertNotFound();
        $response->assertJson(['status' => 'error']);
    }

    public function test_dosen_cannot_edit_other_dosen_publication(): void
    {
        $dosen1 = User::factory()->dosen()->create();
        $dosen2 = User::factory()->dosen()->create();

        $pub = Publication::factory()->pending()->create(['submitter_id' => $dosen1->id]);

        $response = $this->actingAs($dosen2)->get(route('publications.edit', $pub));
        $response->assertForbidden();

        $updateResponse = $this->actingAs($dosen2)->put(route('publications.update', $pub), [
            'title' => 'Hacked Title',
            'journal_name' => 'Journal',
            'publication_year' => 2025,
            'abstract' => 'Abstract',
        ]);
        $updateResponse->assertForbidden();
    }

    public function test_dosen_cannot_edit_already_approved_publication(): void
    {
        $dosen = User::factory()->dosen()->create();
        $pub = Publication::factory()->approved()->create(['submitter_id' => $dosen->id]);

        $response = $this->actingAs($dosen)->get(route('publications.edit', $pub));
        $response->assertForbidden();
    }

    public function test_dosen_can_update_pending_publication(): void
    {
        $dosen = User::factory()->dosen()->create();
        $pub = Publication::factory()->pending()->create([
            'submitter_id' => $dosen->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($dosen)->put(route('publications.update', $pub), [
            'title' => 'Updated Paper Title',
            'journal_name' => 'Updated Journal',
            'publication_year' => 2025,
            'abstract' => 'Updated abstract',
        ]);

        $response->assertRedirect(route('publications.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('publications', [
            'id' => $pub->id,
            'title' => 'Updated Paper Title',
        ]);
    }

    public function test_dosen_can_delete_pending_publication(): void
    {
        $dosen = User::factory()->dosen()->create();
        $file = UploadedFile::fake()->create('to_del.pdf', 500, 'application/pdf');

        $pub = Publication::factory()->pending()->create([
            'submitter_id' => $dosen->id,
            'file_path' => 'publications/to_del.pdf',
        ]);
        Storage::disk('local')->put('publications/to_del.pdf', 'sample content');

        $response = $this->actingAs($dosen)->delete(route('publications.destroy', $pub));

        $response->assertRedirect(route('publications.index'));
        $response->assertSessionHas('info');

        $this->assertDatabaseMissing('publications', ['id' => $pub->id]);
        Storage::disk('local')->assertMissing('publications/to_del.pdf');
    }
}
