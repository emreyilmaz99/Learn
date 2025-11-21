<?php

namespace App\Services\Elasticsearch;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Interfaces\IMessageSearchService;
use App\Services\Eloquent\CacheStatsService;
use App\Core\Class\ServiceResponse;

class MessageSearchService implements IMessageSearchService
{
    protected ElasticsearchClientFactory $factory;
    protected MessageQueryBuilder $builder;
    protected MessageResultMapper $mapper;
    protected CacheStatsService $stats;

    public function __construct(ElasticsearchClientFactory $factory, MessageQueryBuilder $builder, MessageResultMapper $mapper, CacheStatsService $stats)
    {
        $this->factory = $factory;
        $this->builder = $builder;
        $this->mapper = $mapper;
        $this->stats = $stats;
    }

    /**
     * Execute a search against the `messages` index.
     * Returns a ServiceResponse containing data and meta.
     */
    public function search(string $q, int $page = 1, int $perPage = 20): ServiceResponse
    {
        $startTime = microtime(true); // Başlangıç zamanı

        $cacheKey = "search:messages:{$q}:{$page}:{$perPage}";

        // Önce Redis cache'inden kontrol et
        $cachedResult = Cache::store('redis')->get($cacheKey);
        if ($cachedResult) {
            $this->stats->recordHit($cacheKey);
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2); 
            Log::info("Search (cached): {$q} - Duration: {$duration}ms");
            return new ServiceResponse(200, true, 'Search successful (cached)', $cachedResult);
        }

        $this->stats->recordMiss($cacheKey);

        $from = max(0, ($page - 1) * $perPage);

        $body = $this->builder->build($q, $from, $perPage);

        try {
            $client = $this->factory->client();
            $searchUrl = rtrim($this->factory->baseUrl(), '/') . '/messages/_search';
            
            if (config('app.debug')) {
                Log::debug('ES request', ['url' => $searchUrl, 'body' => $body]);
            }
            $resp = $client->post($searchUrl, $body);
            if (!$resp->successful()) {
                Log::warning('ES search non-success', ['status' => $resp->status(), 'body' => $resp->body()]);
                throw new \RuntimeException('Search failed');
            }

            $data = $resp->json();
            if (config('app.debug')) {
                Log::debug('ES response', ['status' => $resp->status(), 'body' => $data]);
            }
            $hits = $data['hits']['hits'] ?? [];
            $total = $data['hits']['total']['value'] ?? (int) ($data['hits']['total'] ?? count($hits));

            $items = $this->mapper->map($hits);

            $totalPages = (int) ceil($total / $perPage);

            $payload = [
                'data' => $items,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'page' => $page,
                    'total_pages' => $totalPages,
                ]
            ];

           
            try {
                Cache::store('redis')->put($cacheKey, $payload, 600);
                $this->stats->recordSet($cacheKey, 600);
                Log::info("Cache koyuldu: {$cacheKey}");
            } catch (\Exception $e) {
                Log::error("Cache put failed: {$cacheKey} - " . $e->getMessage());
            }

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            Log::info("Search (ES): {$q} - Duration: {$duration}ms");

            return new ServiceResponse(200, true, 'Search successful', $payload);
        } catch (\Throwable $e) {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            Log::error('ES search failed', ['error' => $e->getMessage(), 'duration' => $duration . 'ms']);
            return new ServiceResponse(500, false, 'Search error', ['data' => []]);
        }
    }
    
    /**
     * Suggest users by partial name using Elasticsearch collapse to deduplicate by sender_id.
     */
    public function suggestUsers(string $partialName): ServiceResponse
    {
        $body = $this->builder->buildUserSuggestions($partialName);
        
        try {
            $client = $this->factory->client();
            $searchUrl = rtrim($this->factory->baseUrl(), '/') . '/messages/_search';
            
            if (config('app.debug')) {
                Log::debug('ES suggest request', ['url' => $searchUrl, 'body' => $body]);
            }
            
            $resp = $client->post($searchUrl, $body);
            if (!$resp->successful()) {
                Log::warning('ES suggest non-success', ['status' => $resp->status(), 'body' => $resp->body()]);
                return new ServiceResponse(500, false, 'Suggestion error');
            }
            
            $data = $resp->json();
            if (config('app.debug')) {
                Log::debug('ES suggest response', ['status' => $resp->status(), 'body' => $data]);
            }
            $hits = $data['hits']['hits'] ?? [];

            // Use the mapper's lightweight suggestions formatter (no DB queries)
            $suggestions = $this->mapper->mapSuggestions($hits);

            return new ServiceResponse(200, true, 'Öneriler getirildi', $suggestions);
        } catch (\Throwable $e) {
            Log::error('ES suggestion failed', ['error' => $e->getMessage()]);
            return new ServiceResponse(500, false, 'Suggestion error');
        }
    }
}
