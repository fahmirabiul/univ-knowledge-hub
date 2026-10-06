<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Services\Publication\CrossrefMetadataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoiFetchController extends Controller
{
    /**
     * Fetch publication metadata live via Crossref API.
     */
    public function fetch(Request $request, CrossrefMetadataService $crossrefService): JsonResponse
    {
        $doi = (string) $request->query('doi', '');

        if (empty(trim($doi))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter nomor DOI wajib disertakan.',
            ], 422);
        }

        $metadata = $crossrefService->fetch($doi);

        if ($metadata === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Metadata publikasi dengan nomor DOI tersebut tidak ditemukan atau server Crossref tidak merespons.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $metadata,
        ]);
    }
}
