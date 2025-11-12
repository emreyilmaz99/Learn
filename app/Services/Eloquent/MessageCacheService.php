<?php

namespace App\Services\Eloquent;

use App\Services\Interfaces\IMessageCacheService;
use Illuminate\Support\Facades\Cache;

class MessageCacheService implements IMessageCacheService
{
    protected string $store = 'redis';
    protected string $prefix = 'message:';

    public function get(int $id): ?array
    {
        $key = $this->getKey($id);
        $value = Cache::store($this->store)->get($key);
        if (!$value) return null;
        return is_array($value) ? $value : json_decode($value, true);
    }

    public function set(array $payload, int $ttl = 3600): void
    {
        if (!isset($payload['id'])) return;
        $key = $this->getKey((int) $payload['id']);
        $value = $payload;
        // store as array (Laravel cache will serialize)
        Cache::store($this->store)->put($key, $value, $ttl);
    }

    public function delete(int $id): void
    {
        $key = $this->getKey($id);
        Cache::store($this->store)->forget($key);
    }

    protected function getKey(int $id): string
    {
        return $this->prefix . $id;
    }
}
