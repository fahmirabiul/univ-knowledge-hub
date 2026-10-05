<?php

declare(strict_types=1);

namespace App\Services\Publication;

use App\Enums\AuthorRole;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrossrefMetadataService
{
    protected string $apiBaseUrl = 'https://api.crossref.org/works/';

    protected int $timeoutSeconds = 8;

    /**
     * Fetch publication metadata from Crossref API using DOI.
     */
    public function fetch(string $doi): ?array
    {
        $normalizedDoi = $this->normalizeDoi($doi);

        if (empty($normalizedDoi)) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'UnivKnowledgeHub/1.0 (mailto:lppm@univ.ac.id)',
                ])
                ->get($this->apiBaseUrl.urlencode($normalizedDoi));

            if ($response->status() === 404) {
                Log::info("Crossref API: DOI [{$normalizedDoi}] tidak ditemukan.");

                return null;
            }

            if (! $response->successful()) {
                Log::warning("Crossref API Error [{$response->status()}]: {$response->body()}");

                return null;
            }

            $message = $response->json('message');

            if (! is_array($message)) {
                return null;
            }

            return $this->parseCrossrefResponse($message, $normalizedDoi);
        } catch (Exception $e) {
            Log::warning("Crossref API Exception for DOI [{$normalizedDoi}]: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Clean and normalize a DOI string.
     */
    public function normalizeDoi(string $doi): string
    {
        $doi = trim($doi);
        $doi = preg_replace('#^https?://(dx\.)?doi\.org/#i', '', $doi) ?? $doi;

        return trim($doi);
    }

    /**
     * Parse Crossref JSON response into standardized publication array.
     */
    protected function parseCrossrefResponse(array $message, string $normalizedDoi): array
    {
        $titleArray = $message['title'] ?? [];
        $title = ! empty($titleArray) ? (string) $titleArray[0] : 'Untitled Publication';

        $containerTitleArray = $message['container-title'] ?? [];
        $journalName = ! empty($containerTitleArray) ? (string) $containerTitleArray[0] : ($message['publisher'] ?? 'Unknown Publisher');

        $year = null;
        if (isset($message['published']['date-parts'][0][0])) {
            $year = (int) $message['published']['date-parts'][0][0];
        } elseif (isset($message['published-print']['date-parts'][0][0])) {
            $year = (int) $message['published-print']['date-parts'][0][0];
        } elseif (isset($message['created']['date-parts'][0][0])) {
            $year = (int) $message['created']['date-parts'][0][0];
        }

        $abstract = null;
        if (isset($message['abstract']) && is_string($message['abstract'])) {
            // Strip XML JATS tags from Crossref abstract
            $abstract = trim(strip_tags($message['abstract']));
        }

        $volume = isset($message['volume']) ? (string) $message['volume'] : null;
        $issue = isset($message['issue']) ? (string) $message['issue'] : ($message['journal-issue']['issue'] ?? null);
        $pages = isset($message['page']) ? (string) $message['page'] : null;

        $issn = null;
        if (isset($message['ISSN']) && is_array($message['ISSN']) && ! empty($message['ISSN'])) {
            $issn = (string) $message['ISSN'][0];
        }

        $citationCount = (int) ($message['is-referenced-by-count'] ?? 0);
        $publisher = isset($message['publisher']) ? (string) $message['publisher'] : null;

        // Extract author list
        $authors = [];
        $rawAuthors = $message['author'] ?? [];

        if (is_array($rawAuthors)) {
            foreach ($rawAuthors as $index => $rawAuthor) {
                $given = $rawAuthor['given'] ?? '';
                $family = $rawAuthor['family'] ?? '';
                $authorName = trim("{$given} {$family}");

                if (empty($authorName)) {
                    $authorName = $rawAuthor['name'] ?? 'Co-Author';
                }

                $affiliation = null;
                if (isset($rawAuthor['affiliation'][0]['name'])) {
                    $affiliation = (string) $rawAuthor['affiliation'][0]['name'];
                }

                $isFirst = ($index === 0);
                $isCorresponding = ($rawAuthor['sequence'] ?? '') === 'additional' ? false : $isFirst;

                $authors[] = [
                    'author_order' => $index + 1,
                    'author_name' => $authorName,
                    'author_email' => null,
                    'affiliation' => $affiliation,
                    'author_role' => $isFirst ? AuthorRole::FIRST_AUTHOR->value : AuthorRole::CO_AUTHOR->value,
                    'is_corresponding' => $isCorresponding,
                    'is_external' => true,
                ];
            }
        }

        return [
            'doi' => $normalizedDoi,
            'title' => $title,
            'journal_name' => (string) $journalName,
            'publication_year' => $year ?? (int) date('Y'),
            'volume' => $volume,
            'issue' => $issue ? (string) $issue : null,
            'pages' => $pages,
            'isbn_issn' => $issn,
            'abstract' => $abstract,
            'citation_count' => $citationCount,
            'publisher' => $publisher,
            'authors' => $authors,
            'raw_metadata' => $message,
        ];
    }
}
