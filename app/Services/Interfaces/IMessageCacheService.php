<?php

namespace App\Services\Interfaces;

interface IMessageCacheService
{
    /**
     * Get cached message by ID or null if not present.
     * @param int $id
     * @return array|null
     */
    public function get(int $id): ?array;

    /**
     * Cache a message payload.
     * @param array $payload
     * @param int $ttl Seconds
     * @return void
     */
    public function set(array $payload, int $ttl = 3600): void;

    /**
     * Remove message from cache.
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;

    /**
     * Return all cached message ids.
     * @return int[]
     */
    public function allIds(): array;

    /**
     * Clear all cached messages. Return number of deleted entries.
     * @return int
     */
    public function clearAll(): int;
}
