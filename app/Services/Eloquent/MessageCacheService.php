<?php

namespace App\Services\Eloquent;

use App\Services\Interfaces\IMessageCacheService;
use Illuminate\Support\Facades\Cache;

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
    }

    public function delete(int $id): void
    {
        $key = $this->getKey($id);
        Cache::store($this->store)->forget($key);
        
        $this->stats->recordDelete($key);
    }

    protected function getKey(int $id): string
    {
        // Use config-driven prefix to avoid duplication and keep a single source of truth
        $prefix = config('message_cache.prefix', 'myapp_db_message:');
        return $prefix . $id;
    }
}
