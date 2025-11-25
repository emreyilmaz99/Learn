<?php

namespace App\Services\Eloquent;

use App\Services\Interfaces\IMessageCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class MessageCacheService implements IMessageCacheService
{
    protected string $store = 'redis';
    protected string $prefix = 'myapp_db_message:';
    protected CacheStatsService $stats;

    public function __construct(CacheStatsService $stats)
    {
        $this->stats = $stats;
    }

    public function get(int $id): ?array
    {
        $key = $this->getKey($id);
        $value = Cache::store($this->store)->get($key);
        
        if (!$value) {
            $this->stats->recordMiss($key);
            return null;
        }
        
        $this->stats->recordHit($key);
        return is_array($value) ? $value : json_decode($value, true);
    }

    public function set(array $payload, int $ttl = 3600): void
    {
        if (!isset($payload['id'])) return;
        $key = $this->getKey((int) $payload['id']);
        $value = $payload;
        
        Cache::store($this->store)->put($key, $value, $ttl);
        
        // İstatistiğe kaydet
        $this->stats->recordSet($key, $ttl);

        // Maintain index set for efficient listing (avoid KEYS)
        try {
            $index = config('message_cache.index', 'message_index');
            $id = (int) $payload['id'];
            Redis::connection('cache')->sadd($index, $id);
        } catch (\Throwable $e) {
            // ignore index failures
        }
    }

    public function delete(int $id): void
    {
        $key = $this->getKey($id);
        Cache::store($this->store)->forget($key);
        
        $this->stats->recordDelete($key);

        // Remove from index set
        try {
            $index = config('message_cache.index', 'message_index');
            Redis::connection('cache')->srem($index, $id);
        } catch (\Throwable $e) {
            // ignore index failures
        }
    }

    /**
     * Return all cached message ids from the index set.
     * Falls back to empty array if index not present.
     */
    public function allIds(): array
    {
        try {
            $index = config('message_cache.index', 'message_index');
            $ids = Redis::connection('cache')->smembers($index) ?: [];
            return array_map('intval', $ids);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Clear all cached messages tracked in the index set. Returns deleted count.
     */
    public function clearAll(): int
    {
        $ids = $this->allIds();
        $deleted = 0;

        foreach ($ids as $id) {
            $this->delete($id);
            $deleted++;
        }

        // try to delete the index set itself
        try {
            $index = config('message_cache.index', 'message_index');
            Redis::connection('cache')->del([$index]);
        } catch (\Throwable $e) {
            // ignore
        }

        return $deleted;
    }

    protected function getKey(int $id): string
    {
        // Use config-driven prefix to avoid duplication and keep a single source of truth
        $prefix = config('message_cache.prefix', 'myapp_db_message:');
        return $prefix . $id;
    }
}
