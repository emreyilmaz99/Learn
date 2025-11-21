<?php

namespace App\Services\Eloquent;

use Illuminate\Support\Facades\Cache;

class CacheStatsService
{
    protected string $statsKey = 'cache_stats';
    protected string $recentKey = 'cache_recent_ops';
    protected int $maxRecentOps = 100;

    public function recordHit(string $key): void
    {
        $this->increment('hits');
        $this->addRecentOp('HIT', $key);
    }

    public function recordMiss(string $key): void
    {
        $this->increment('misses');
        $this->addRecentOp('MISS', $key);
    }

    public function recordSet(string $key, int $ttl): void
    {
        $this->increment('sets');
        $this->addRecentOp('SET', $key, ['ttl' => $ttl]);
    }

    public function recordDelete(string $key): void
    {
        $this->increment('deletes');
        $this->addRecentOp('DELETE', $key);
    }

    public function getStats(): array
    {
        $stats = $this->getStatsArray();
        $total = $stats['hits'] + $stats['misses'];
        
        return array_merge($stats, [
            'total_requests' => $total,
            'hit_rate_percent' => $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0,
        ]);
    }

    public function getRecentOps(int $limit = 50): array
    {
        return array_slice(Cache::get($this->recentKey, []), -$limit);
    }

    public function resetStats(): void
    {
        Cache::store('redis')->forget($this->statsKey);
        Cache::store('redis')->forget($this->recentKey);
    }

    protected function getStatsArray(): array
    {
        return Cache::store('redis')->get($this->statsKey, ['hits' => 0, 'misses' => 0, 'sets' => 0, 'deletes' => 0]);
    }

    protected function increment(string $field): void
    {
        $stats = $this->getStatsArray();
        $stats[$field]++;
        Cache::store('redis')->put($this->statsKey, $stats, now()->addDays(7));
    }

    protected function addRecentOp(string $operation, string $key, array $extra = []): void
    {
        $recent = Cache::store('redis')->get($this->recentKey, []);
        $recent[] = compact('operation', 'key', 'extra') + ['timestamp' => now()->toDateTimeString()];
        
        Cache::store('redis')->put($this->recentKey, array_slice($recent, -$this->maxRecentOps), now()->addDays(7));
    }
}
