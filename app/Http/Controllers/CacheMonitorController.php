<?php

namespace App\Http\Controllers;

use App\Services\Eloquent\CacheStatsService;
use App\Services\Interfaces\IMessageCacheService;
use App\Models\Message;
use App\Core\Class\ServiceResponse;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class CacheMonitorController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CacheStatsService $stats,
        protected IMessageCacheService $messageCache
    ) {}

    public function stats(): JsonResponse
    {
        $data = [
            'stats' => $this->stats->getStats(),
            'redis_info' => [
                'total_message_keys' => count($keys = $this->getRedisKeys('message:*')),
                'sample_keys' => array_slice($keys, 0, 10),
            ],
        ];

        return $this->serviceResponse(
            new ServiceResponse(200, true, 'Cache istatistikleri basariyla getirildi', $data)
        );
    }

    public function keys(): JsonResponse
    {
        $keys = $this->getRedisKeys('message:*');
        $data = ['total_keys' => count($keys), 'keys' => $keys];

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
        $this->stats->resetStats();

        return $this->serviceResponse(
            new ServiceResponse(200, true, 'İstatistikler sifirlandi', null)
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

    protected function getRedisKeys(string $pattern): array
    {
        try {
            $prefix = config('cache.prefix', '');
            $redis = \Illuminate\Support\Facades\Redis::connection('cache');
            $keys = $redis->keys($prefix . $pattern) ?? [];
            
            return array_map(fn($key) => str_replace($prefix, '', $key), $keys);
        } catch (\Exception $e) {
            return [];
        }
    }
}
