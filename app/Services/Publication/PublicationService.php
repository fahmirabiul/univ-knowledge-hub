<?php

declare(strict_types=1);

namespace App\Services\Publication;

use App\Enums\AuthorRole;
use App\Enums\PublicationStatus;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicationService
{
    public function __construct(
        protected PublicationStorageService $storageService,
        protected CrossrefMetadataService $crossrefService
    ) {}

    /**
     * Create a new research publication submission along with its author collaborators.
     */
    public function createPublication(User $submitter, array $data, ?UploadedFile $file = null): Publication
    {
        return DB::transaction(function () use ($submitter, $data, $file): Publication {
            $registrationCode = $this->generateRegistrationCode();
            $fileData = $file ? $this->storageService->storeFile($file) : [];

            $doi = ! empty($data['doi']) ? $this->crossrefService->normalizeDoi((string) $data['doi']) : null;

            $publication = Publication::create([
                'registration_code' => $registrationCode,
                'submitter_id' => $submitter->id,
                'category_id' => $data['category_id'] ?? null,
                'doi' => $doi,
                'isbn_issn' => $data['isbn_issn'] ?? null,
                'title' => (string) $data['title'],
                'abstract' => (string) ($data['abstract'] ?? ''),
                'journal_name' => (string) $data['journal_name'],
                'publication_year' => (int) ($data['publication_year'] ?? date('Y')),
                'volume' => isset($data['volume']) ? (string) $data['volume'] : null,
                'issue' => isset($data['issue']) ? (string) $data['issue'] : null,
                'pages' => isset($data['pages']) ? (string) $data['pages'] : null,
                'status' => PublicationStatus::PENDING,
                'file_path' => $fileData['file_path'] ?? null,
                'original_filename' => $fileData['original_filename'] ?? null,
                'file_size_bytes' => $fileData['file_size_bytes'] ?? null,
                'metadata_api' => $data['metadata_api'] ?? null,
                'citation_count' => (int) ($data['citation_count'] ?? 0),
                'is_public' => (bool) ($data['is_public'] ?? true),
                'published_at' => null,
            ]);

            $authors = $data['authors'] ?? [];
            $this->syncAuthors($publication, $authors, $submitter);

            return $publication->load(['submitter', 'category', 'authors.user']);
        });
    }

    /**
     * Update an existing publication.
     */
    public function updatePublication(Publication $publication, array $data, ?UploadedFile $newFile = null): Publication
    {
        return DB::transaction(function () use ($publication, $data, $newFile): Publication {
            $fileData = [];
            if ($newFile) {
                // Delete existing old file if replaced
                $this->storageService->deleteFile($publication->file_path);
                $fileData = $this->storageService->storeFile($newFile);
            }

            $doi = ! empty($data['doi']) ? $this->crossrefService->normalizeDoi((string) $data['doi']) : $publication->doi;

            $updatePayload = [
                'category_id' => $data['category_id'] ?? $publication->category_id,
                'doi' => $doi,
                'isbn_issn' => $data['isbn_issn'] ?? $publication->isbn_issn,
                'title' => (string) ($data['title'] ?? $publication->title),
                'abstract' => (string) ($data['abstract'] ?? $publication->abstract),
                'journal_name' => (string) ($data['journal_name'] ?? $publication->journal_name),
                'publication_year' => (int) ($data['publication_year'] ?? $publication->publication_year),
                'volume' => isset($data['volume']) ? (string) $data['volume'] : $publication->volume,
                'issue' => isset($data['issue']) ? (string) $data['issue'] : $publication->issue,
                'pages' => isset($data['pages']) ? (string) $data['pages'] : $publication->pages,
                'is_public' => isset($data['is_public']) ? (bool) $data['is_public'] : $publication->is_public,
            ];

            if (! empty($fileData)) {
                $updatePayload['file_path'] = $fileData['file_path'];
                $updatePayload['original_filename'] = $fileData['original_filename'];
                $updatePayload['file_size_bytes'] = $fileData['file_size_bytes'];
            }

            $publication->update($updatePayload);

            if (isset($data['authors']) && is_array($data['authors'])) {
                $this->syncAuthors($publication, $data['authors'], $publication->submitter);
            }

            return $publication->fresh(['submitter', 'category', 'authors.user']);
        });
    }

    /**
     * Generate unique registration code for paper submission (e.g. PUB-2026-X8K9).
     */
    public function generateRegistrationCode(): string
    {
        $year = date('Y');

        do {
            $randomSuffix = strtoupper(Str::random(4));
            $code = "PUB-{$year}-{$randomSuffix}";
        } while (Publication::where('registration_code', $code)->exists());

        return $code;
    }

    /**
     * Synchronize and order publication authors list.
     */
    public function syncAuthors(Publication $publication, array $authorsList, User $submitter): void
    {
        $publication->authors()->delete();

        // If no author provided, fallback to the submitting user
        if (empty($authorsList)) {
            $publication->authors()->create([
                'user_id' => $submitter->id,
                'author_name' => $submitter->name,
                'author_email' => $submitter->email,
                'affiliation' => $submitter->faculty ?? 'Universitas Knowledge Hub',
                'author_role' => AuthorRole::FIRST_AUTHOR,
                'author_order' => 1,
                'is_corresponding' => true,
                'is_external' => false,
            ]);

            return;
        }

        foreach ($authorsList as $index => $item) {
            $authorName = trim((string) ($item['author_name'] ?? ''));
            if (empty($authorName)) {
                continue;
            }

            $userId = ! empty($item['user_id']) ? (int) $item['user_id'] : null;
            $email = ! empty($item['author_email']) ? trim((string) $item['author_email']) : null;

            // If user_id is null but email matches local user, link automatically
            if (! $userId && $email) {
                $matchedUser = User::where('email', $email)->first();
                if ($matchedUser) {
                    $userId = $matchedUser->id;
                }
            }

            $isExternal = (bool) ($item['is_external'] ?? ($userId === null));

            $roleValue = (string) ($item['author_role'] ?? AuthorRole::CO_AUTHOR->value);
            $authorRole = AuthorRole::tryFrom($roleValue) ?? AuthorRole::CO_AUTHOR;

            $isCorresponding = (bool) ($item['is_corresponding'] ?? false);
            if ($authorRole === AuthorRole::FIRST_CORRESPONDING || $authorRole === AuthorRole::CORRESPONDING) {
                $isCorresponding = true;
            }

            $publication->authors()->create([
                'user_id' => $userId,
                'author_name' => $authorName,
                'author_email' => $email,
                'affiliation' => isset($item['affiliation']) ? (string) $item['affiliation'] : null,
                'author_role' => $authorRole,
                'author_order' => (int) ($item['author_order'] ?? ($index + 1)),
                'is_corresponding' => $isCorresponding,
                'is_external' => $isExternal,
            ]);
        }
    }
}
