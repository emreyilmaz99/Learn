<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageSearchRequest;
use App\Services\Elasticsearch\MessageSearchService;
use Illuminate\Http\JsonResponse;

class MessageSearchController extends Controller
{
    protected MessageSearchService $searchService;

    public function __construct(MessageSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Search messages using Elasticsearch. Expects query param `q`.
     * Returns JSON in the same shape the front-end expects: { data: [ ... ] }
     *
     * @param MessageSearchRequest $request
     * @return JsonResponse
     */
    public function search(MessageSearchRequest $request): JsonResponse
    {
        $q = (string) $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));

        try {
            $result = $this->searchService->search($q, $page, $perPage);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => 'Search error'], 500);
        }
    }
}
