<?php

namespace App\Services\Elasticsearch;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class MessageSearchService
{
    protected ElasticsearchClientFactory $factory;
    protected MessageQueryBuilder $builder;
    protected MessageResultMapper $mapper;

    public function __construct(ElasticsearchClientFactory $factory, MessageQueryBuilder $builder, MessageResultMapper $mapper)
    {
        $this->factory = $factory;
        $this->builder = $builder;
        $this->mapper = $mapper;
    }

    /**
     * Execute a search against the `messages` index.
     * Returns array: ['data' => [...], 'meta' => [...]]
     */
    public function search(string $q, int $page = 1, int $perPage = 20): array
    {
        $from = max(0, ($page - 1) * $perPage);

        $body = $this->builder->build($q, $from, $perPage);

        try {
            $client = $this->factory->client();
            $searchUrl = rtrim($this->factory->baseUrl(), '/') . '/messages/_search';
            // Log outgoing request body for debugging
            if (config('app.debug')) {
                Log::debug('ES request', ['url' => $searchUrl, 'body' => $body]);
            }
            $resp = $client->post($searchUrl, $body);
            if (!$resp->successful()) {
                Log::warning('ES search non-success', ['status' => $resp->status(), 'body' => $resp->body()]);
                throw new \RuntimeException('Search failed');
            }

            $data = $resp->json();
            // Log incoming response for debugging
            if (config('app.debug')) {
                Log::debug('ES response', ['status' => $resp->status(), 'body' => $data]);
            }
            $hits = $data['hits']['hits'] ?? [];
            $total = $data['hits']['total']['value'] ?? (int) ($data['hits']['total'] ?? count($hits));

            $items = $this->mapper->map($hits);

            $totalPages = (int) ceil($total / $perPage);

            return [
                'data' => $items,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'page' => $page,
                    'total_pages' => $totalPages,
                ]
            ];
        } catch (\Throwable $e) {
            Log::error('ES search failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
