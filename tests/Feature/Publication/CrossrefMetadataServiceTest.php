<?php

declare(strict_types=1);

namespace Tests\Feature\Publication;

use App\Services\Publication\CrossrefMetadataService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrossrefMetadataServiceTest extends TestCase
{
    public function test_normalize_doi_strips_url_prefixes_and_spaces(): void
    {
        $service = new CrossrefMetadataService;

        $this->assertEquals('10.1145/3372278.3390678', $service->normalizeDoi('https://doi.org/10.1145/3372278.3390678'));
        $this->assertEquals('10.1145/3372278.3390678', $service->normalizeDoi('http://dx.doi.org/10.1145/3372278.3390678 '));
        $this->assertEquals('10.1109/TPAMI.2024.123', $service->normalizeDoi('10.1109/TPAMI.2024.123'));
    }

    public function test_fetch_metadata_parses_crossref_api_response_correctly(): void
    {
        Http::fake([
            'https://api.crossref.org/works/*' => Http::response([
                'status' => 'ok',
                'message' => [
                    'DOI' => '10.1109/TPAMI.2024.3389012',
                    'title' => ['Vision Transformers for Multimodal Brain MRI Segmentation'],
                    'container-title' => ['IEEE Transactions on Pattern Analysis and Machine Intelligence'],
                    'published' => [
                        'date-parts' => [[2024, 5, 12]],
                    ],
                    'volume' => '46',
                    'issue' => '5',
                    'page' => '1024-1038',
                    'ISSN' => ['0162-8828'],
                    'abstract' => '<jats:p>This study explores vision transformers for medical imaging segmentation.</jats:p>',
                    'is-referenced-by-count' => 38,
                    'publisher' => 'IEEE',
                    'author' => [
                        [
                            'given' => 'Siti',
                            'family' => 'Rahmawati',
                            'sequence' => 'first',
                            'affiliation' => [['name' => 'Universitas Knowledge Hub']],
                        ],
                        [
                            'given' => 'Elena',
                            'family' => 'Rostova',
                            'sequence' => 'additional',
                            'affiliation' => [['name' => 'University of Oxford']],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new CrossrefMetadataService;
        $result = $service->fetch('10.1109/TPAMI.2024.3389012');

        $this->assertNotNull($result);
        $this->assertEquals('10.1109/TPAMI.2024.3389012', $result['doi']);
        $this->assertEquals('Vision Transformers for Multimodal Brain MRI Segmentation', $result['title']);
        $this->assertEquals('IEEE Transactions on Pattern Analysis and Machine Intelligence', $result['journal_name']);
        $this->assertEquals(2024, $result['publication_year']);
        $this->assertEquals('46', $result['volume']);
        $this->assertEquals('5', $result['issue']);
        $this->assertEquals('1024-1038', $result['pages']);
        $this->assertEquals('0162-8828', $result['isbn_issn']);
        $this->assertEquals('This study explores vision transformers for medical imaging segmentation.', $result['abstract']);
        $this->assertEquals(38, $result['citation_count']);
        $this->assertCount(2, $result['authors']);
        $this->assertEquals('Siti Rahmawati', $result['authors'][0]['author_name']);
        $this->assertTrue($result['authors'][0]['is_corresponding']);
        $this->assertEquals('Elena Rostova', $result['authors'][1]['author_name']);
    }

    public function test_fetch_returns_null_when_doi_not_found_or_error(): void
    {
        Http::fake([
            'https://api.crossref.org/works/*' => Http::response(['status' => 'not-found'], 404),
        ]);

        $service = new CrossrefMetadataService;
        $result = $service->fetch('10.9999/invalid.doi');

        $this->assertNull($result);
    }
}
