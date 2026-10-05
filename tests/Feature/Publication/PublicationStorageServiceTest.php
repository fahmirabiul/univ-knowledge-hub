<?php

declare(strict_types=1);

namespace Tests\Feature\Publication;

use App\Models\Publication;
use App\Services\Publication\PublicationStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class PublicationStorageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_store_file_stores_pdf_in_private_directory(): void
    {
        $file = UploadedFile::fake()->create('research_paper.pdf', 1024, 'application/pdf');

        $service = new PublicationStorageService;
        $result = $service->storeFile($file);

        $this->assertArrayHasKey('file_path', $result);
        $this->assertEquals('research_paper.pdf', $result['original_filename']);
        $this->assertGreaterThan(0, $result['file_size_bytes']);

        Storage::assertExists($result['file_path']);
    }

    public function test_store_file_rejects_non_pdf_file(): void
    {
        $file = UploadedFile::fake()->create('image.png', 500, 'image/png');

        $service = new PublicationStorageService;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hanya dokumen format PDF yang diizinkan untuk diunggah.');

        $service->storeFile($file);
    }

    public function test_store_file_rejects_oversized_file(): void
    {
        // 11MB file
        $file = UploadedFile::fake()->create('huge.pdf', 11 * 1024, 'application/pdf');

        $service = new PublicationStorageService;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ukuran file dokumen PDF melebihi batas maksimal 10MB.');

        $service->storeFile($file);
    }

    public function test_generate_temporary_download_url_creates_valid_signed_url(): void
    {
        $pub = Publication::factory()->approved()->create();

        $service = new PublicationStorageService;
        $url = $service->generateTemporaryDownloadUrl($pub, 15);

        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString((string) $pub->id, $url);
    }

    public function test_delete_file_removes_file_from_storage(): void
    {
        $file = UploadedFile::fake()->create('to_delete.pdf', 500, 'application/pdf');

        $service = new PublicationStorageService;
        $stored = $service->storeFile($file);

        Storage::assertExists($stored['file_path']);

        $deleted = $service->deleteFile($stored['file_path']);
        $this->assertTrue($deleted);
        Storage::assertMissing($stored['file_path']);
    }

    public function test_download_file_returns_streamed_response(): void
    {
        $file = UploadedFile::fake()->create('paper.pdf', 500, 'application/pdf');

        $service = new PublicationStorageService;
        $stored = $service->storeFile($file);

        $pub = Publication::factory()->approved()->create([
            'file_path' => $stored['file_path'],
            'original_filename' => 'paper.pdf',
        ]);

        $response = $service->downloadFile($pub);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
    }
}
