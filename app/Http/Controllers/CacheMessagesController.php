<?php

namespace App\Http\Controllers;

use App\Services\Eloquent\MessageCacheService;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Core\Class\ServiceResponse;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;

class CacheMessagesController extends Controller
{
    use ApiResponseTrait;

    protected MessageCacheService $cacheService;

    public function __construct(MessageCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Cache'deki tüm mesajları listele (Web View)
     */
    public function index()
    {
        $messages = $this->getCachedMessages();
        return view('cache_messages', ['messages' => $messages]);
    }

    /**
     * Database'deki TÜM mesajları Redis'e cache'le
     * API Endpoint: GET /api/cache/set
     */
    public function cacheAllMessages(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $messages = Message::where('sender_id', $userId)
                ->orWhere('receiver_id', $userId)
                ->with(['sender', 'receiver'])
                ->get();

            $cachedCount = 0;
            foreach ($messages as $message) {
                $this->cacheService->set($message->toArray(), 3600); // 1 saat TTL
                $cachedCount++;
            }

            return $this->serviceResponse(
                new ServiceResponse(
                    200, 
                    true, 
                    "Toplam {$cachedCount} mesaj başarıyla cache'lendi",
                    ['cached_count' => $cachedCount]
                )
            );
        } catch (\Exception $e) {
            return $this->serviceResponse(
                new ServiceResponse(
                    500,
                    false,
                    'Cache işlemi sırasında hata: ' . $e->getMessage(),
                    null
                )
            );
        }
    }

    /**
     * Belirli bir mesajı cache'den getir
     */
    public function get(int $id): ?array
    {
        return $this->cacheService->get($id);
    }

    /**
     * Mesajı cache'e kaydet
     */
    public function set(array $payload, int $ttl = 3600): void
    {
        $this->cacheService->set($payload, $ttl);
    }

    /**
     * Cache'den mesaj sil
     */
    public function delete(int $id): void
    {
        $this->cacheService->delete($id);
    }

    /**
     * Cache'i tamamen temizle
     */
    public function clearCache()
    {
        try {
            // match configured message prefix, allow any leading prefixes
            $msgPrefix = config('message_cache.prefix', 'myapp_db_message:');
            $keys = $this->getRedisKeys('*' . $msgPrefix . '*');
            $deletedCount = 0;

            foreach ($keys as $key) {
                // Extract ID robustly: use substring after last ':' to tolerate any prefixes
                $pos = strrpos($key, ':');
                if ($pos === false) {
                    continue;
                }

                $id = (int) substr($key, $pos + 1);
                $this->cacheService->delete($id);
                $deletedCount++;
            }

            return $this->serviceResponse(
                new ServiceResponse(
                    200,
                    true,
                    "Toplam {$deletedCount} mesaj cache'den temizlendi",
                    ['deleted_count' => $deletedCount]
                )
            );
        } catch (\Exception $e) {
            return $this->serviceResponse(
                new ServiceResponse(
                    500,
                    false,
                    'Cache temizleme hatası: ' . $e->getMessage(),
                    null
                )
            );
        }
    }

    /**
     * Helper: Cache'deki tüm mesajları getir
     */
    protected function getCachedMessages(): array
    {
    // Pattern: match whatever prefix is configured for message cache (allow any extra prefixes)
    $msgPrefix = config('message_cache.prefix', 'myapp_db_message:');
    $keys = $this->getRedisKeys('*' . $msgPrefix . '*');
        if (empty($keys)) {
            Log::warning('Redis bağlantısı başarısız veya anahtar bulunamadı.');
            return [];
        }

        $messages = [];

        foreach ($keys as $key) {
            // Extract ID robustly: take substring after last ':' so any leading prefixes are ignored
            $pos = strrpos($key, ':');
            if ($pos === false) {
                Log::warning('Unable to extract id from redis key', ['raw_key' => $key]);
                continue;
            }

            $id = (int) substr($key, $pos + 1);

            $msg = $this->cacheService->get($id);

            if (!$msg) {
                Log::warning('Message not found in Redis', ['id' => $id, 'raw_key' => $key]);
                continue;
            }

            $messages[] = $msg;
        }

        // En yeni mesajlar önce gelsin
        usort($messages, function($a, $b) {
            return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
        });

        return $messages;
    }

    /**
     * Helper: Redis'ten key'leri getir
     */
    protected function getRedisKeys(string $pattern): array
    {
        try {
            // Do not attempt to double-prefix here. Caller should pass a pattern that matches
            // the actual keys in Redis (including the service prefix if used).
            $redis = Redis::connection('cache');
            $keys = $redis->keys($pattern);

            if (empty($keys)) {
                throw new \Exception('Redis bağlantısı başarısız veya anahtar bulunamadı.');
            }

            // return raw keys as stored in Redis
            return is_array($keys) ? $keys : iterator_to_array($keys);
        } catch (\Exception $e) {
            logger()->error('Redis key retrieval error: ' . $e->getMessage());
            return [];
        }
    }

    public function testRedisConnection()
    {
        try {
            $redis = Redis::connection('cache');
            $redis->set('test_key', 'test_value');
            $value = $redis->get('test_key');
            $keys = $redis->keys('*');

            return response()->json([
                'test_key_value' => $value,
                'keys' => $keys,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
