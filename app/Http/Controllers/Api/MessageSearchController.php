<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageSearchRequest;
use App\Services\Interfaces\IMessageSearchService;
use App\Http\Traits\ApiResponseTrait;
use App\Core\Class\ServiceResponse;
use Illuminate\Http\JsonResponse;

class MessageSearchController extends Controller
{
    use ApiResponseTrait;

    protected IMessageSearchService $searchService;

    public function __construct(IMessageSearchService $searchService)
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
            if ($result instanceof ServiceResponse) {
                return $this->serviceResponse($result);
            }

            // Fallback: wrap raw array payload if implementation returns array
            $payload = is_array($result) ? $result : ['data' => []];
            return $this->serviceResponse(new ServiceResponse(200, true, 'Search successful', $payload));
        } catch (\Throwable $e) {
            return $this->serviceResponse(new ServiceResponse(500, false, 'Search error', ['data' => []]));
        }
    }
}
