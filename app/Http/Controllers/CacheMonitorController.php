<?php

namespace App\Http\Controllers;

use App\Services\Eloquent\CacheStatsService;
use App\Services\Interfaces\IMessageCacheService;
use App\Models\Message;
use App\Core\Class\ServiceResponse;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class CacheMonitorController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CacheStatsService $stats,
        protected IMessageCacheService $messageCache
    ) {}

    public function stats(): JsonResponse
    {
        $ids = $this->messageCache->allIds();

        $data = [
            'stats' => $this->stats->getStats(),
            'redis_info' => [
                // use message cache index to determine totals (avoids KEYS)
                'total_message_keys' => count($ids),
                'sample_ids' => array_slice($ids, 0, 10),
            ],
        ];

        return $this->serviceResponse(
            new ServiceResponse(200, true, 'Cache istatistikleri basariyla getirildi', $data)
        );
    }

    public function keys(): JsonResponse
    {
        $ids = $this->messageCache->allIds();
        $data = ['total_keys' => count($ids), 'ids' => $ids];

        return $this->serviceResponse(
            new ServiceResponse(200, true, 'Redis keyleri basariyla getirildi', $data)
        );
    }

    public function recent(): JsonResponse
    {
        $data = ['recent_operations' => $this->stats->getRecentOps(50)];

        return $this->serviceResponse(
            new ServiceResponse(200, true, 'Son işlemler basariyla getirildi', $data)
        );
    }

    public function reset(): JsonResponse
    {
        // Reset stats
        $this->stats->resetStats();

        // Delegate clearing all cached messages to messageCache service
        $deleted = 0;
        try {
            $deleted = $this->messageCache->clearAll();
        } catch (\Exception $e) {
            $deleted = 0;
        }

        return $this->serviceResponse(
            new ServiceResponse(200, true, 'İstatistikler sifirlandi ve cache temizlendi', ['deleted_keys' => $deleted])
        );
    }

    public function inspect(int $id): JsonResponse
    {
        $cached = $this->messageCache->get($id);
        $dbMessage = Message::find($id);
        
        $data = [
            'id' => $id,
            'exists_in_redis' => $cached !== null,
            'exists_in_db' => $dbMessage !== null,
            'cached_data' => $cached,
            'db_data' => $dbMessage?->toArray(),
            'match' => ($cached && $dbMessage) ? 'Uyumlu' : 'Uyumsuz veya veri yok',
        ];

        return $this->serviceResponse(
            new ServiceResponse(200, true, 'Mesaj inceleme başarılı', $data)
        );
    }

    public function testRedisConnection(): JsonResponse
    {
        try {
            $redis = Redis::connection('cache');
            $redis->set('test_key', 'test_value');
            $value = $redis->get('test_key');
            $keys = $redis->keys('*');

            return $this->serviceResponse(new ServiceResponse(200, true, 'Redis diagnostics', [
                'test_key_value' => $value,
                'keys' => $keys,
            ]));
        } catch (\Exception $e) {
            return $this->serviceResponse(new ServiceResponse(500, false, 'Redis diagnostic failed: ' . $e->getMessage(), null));
        }
    }
}
