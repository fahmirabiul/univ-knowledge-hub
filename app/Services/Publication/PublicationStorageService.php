<?php

declare(strict_types=1);

namespace App\Services\Publication;

use App\Models\Publication;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicationStorageService
{
    protected string $disk = 'local'; // Maps to storage/app/private

    protected string $directory = 'publications';

    public const MAX_FILE_SIZE_BYTES = 10485760; // 10MB

    /**
     * Get the filesystem disk adapter instance.
     */
    protected function storage(): FilesystemAdapter
    {
        /** @var FilesystemAdapter */
        return Storage::disk($this->disk);
    }

    /**
     * Store an uploaded PDF file into the private storage directory.
     *
     * @return array{file_path: string, original_filename: string, file_size_bytes: int}
     */
    public function storeFile(UploadedFile $file): array
    {
        $mime = $file->getMimeType();
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext !== 'pdf' && $mime !== 'application/pdf') {
            throw new InvalidArgumentException('Hanya dokumen format PDF yang diizinkan untuk diunggah.');
        }

        if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            throw new InvalidArgumentException('Ukuran file dokumen PDF melebihi batas maksimal 10MB.');
        }

        $filePath = $this->storage()->putFile($this->directory, $file);

        if (! $filePath) {
            throw new InvalidArgumentException('Gagal menyimpan file PDF ke private storage.');
        }

        return [
            'file_path' => $filePath,
            'original_filename' => $file->getClientOriginalName(),
            'file_size_bytes' => $file->getSize(),
        ];
    }

    /**
     * Generate a temporary signed download URL valid for specified minutes.
     */
    public function generateTemporaryDownloadUrl(Publication $publication, int $minutes = 15): string
    {
        return URL::temporarySignedRoute(
            'publications.download',
            now()->addMinutes($minutes),
            ['publication' => $publication->id]
        );
    }

    /**
     * Alias for generateTemporaryDownloadUrl.
     */
    public function getTemporaryDownloadUrl(Publication $publication, int $minutes = 15): string
    {
        return $this->generateTemporaryDownloadUrl($publication, $minutes);
    }

    /**
     * Get a binary streamed download response for the stored PDF file.
     */
    public function downloadFile(Publication $publication): StreamedResponse
    {
        if (! $publication->file_path || ! $this->storage()->exists($publication->file_path)) {
            abort(404, 'File dokumen PDF publikasi tidak ditemukan di private storage.');
        }

        $filename = $publication->original_filename ?: "publication-{$publication->id}.pdf";

        return $this->storage()->download($publication->file_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Delete stored file from private storage.
     */
    public function deleteFile(?string $filePath): bool
    {
        if ($filePath && $this->storage()->exists($filePath)) {
            return $this->storage()->delete($filePath);
        }

        return false;
    }
}
