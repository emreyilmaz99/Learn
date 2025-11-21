<?php

namespace App\Http\Controllers;

use App\Services\Eloquent\CacheStatsService;
use App\Services\Interfaces\IMessageCacheService;
use App\Models\Message;
use App\Core\Class\ServiceResponse;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        $searchKeys = [];

        try {
            $redis = Redis::connection('cache');
            $prefix = config('cache.prefix');
            $pattern = $prefix . 'search:messages*';
            $foundKeys = $redis->keys($pattern);
            $searchKeys = $foundKeys;
        } catch (\Exception $e) {
            Log::error('Redis Stats Error: ' . $e->getMessage());
            $searchKeys = [];
        }

        
        //---------------------------------------------------------------------------------------
        $data = [
            'stats' => $this->stats->getStats(),
            'redis_info' => [
                'total_message_keys' => count($ids),
                'sample_ids' => array_slice($ids, 0, 10),
                'total_search_cache_keys' => count($searchKeys), 
                'sample_search_keys' => array_slice($searchKeys, 0, 5), 
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
        // 1. İstatistikleri sıfırla
        $this->stats->resetStats();

        // 2. Normal Mesaj ID cache'lerini temizle
        $deletedMessages = 0;
        try {
            $deletedMessages = $this->messageCache->clearAll();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Message Cache Clear Error: ' . $e->getMessage());
        }

        // 3. Arama (Search) Cache'lerini Temizle (Çift Prefix Sorunu Çözümü)
        $searchDeletedCount = 0;
        try {
            $redis = Redis::connection('cache');
            
            // Başında ne olursa olsun (myapp_db, myapp_cache vs.) içinde search:messages geçenleri bul
            $keys = $redis->keys('*search:messages*');

            foreach ($keys as $fullKey) {
                // Redis'ten gelen key: "myapp_db_myapp_cache_search:messages:emre:1:20"
                // Bizim Cache::forget'e vermemiz gereken: "search:messages:emre:1:20"
                
                // "search:messages" ifadesinin başladığı yeri bul
                $pos = strpos($fullKey, 'search:messages');
                
                if ($pos !== false) {
                    // Prefix kısmını atıp temiz keyi al
                    $cleanKey = substr($fullKey, $pos);
                    
                    // Laravel'in kendi forget metodunu kullan (Prefixleri o halleder)
                    Cache::store('redis')->forget($cleanKey);
                    $searchDeletedCount++;
                }
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Search Cache Clear Error: ' . $e->getMessage());
            $searchDeletedCount = 0;
        }

        return $this->serviceResponse(
            new ServiceResponse(200, true, 'İstatistikler sıfırlandı ve cache temizlendi', [
                'deleted_message_keys' => $deletedMessages,
                'deleted_search_keys' => $searchDeletedCount
            ])
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

            // Test cache koy
            try {
                Cache::put('test_cache_key', 'test_cache_value', 60);
                $cacheValue = Cache::get('test_cache_key');
            } catch (\Exception $e) {
                $cacheValue = 'Cache put failed: ' . $e->getMessage();
            }
            $allKeys = $redis->keys('*');

            return $this->serviceResponse(new ServiceResponse(200, true, 'Redis diagnostics', [
                'test_key_value' => $value,
                'cache_test_value' => $cacheValue,
                'keys' => $keys,
                'all_keys_after_cache' => $allKeys,
            ]));
        } catch (\Exception $e) {
            return $this->serviceResponse(new ServiceResponse(500, false, 'Redis diagnostic failed: ' . $e->getMessage(), null));
        }
    }
}
