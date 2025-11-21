<?php

namespace App\Http\Controllers;

use App\Services\Eloquent\MessageCacheService;
use App\Models\Message;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
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
            // Determine the user whose messages should be cached.
            // Priority: authenticated user from middleware, else optional 'token' input (Sanctum personal access token).
            $user = $request->user();
            if (!$user && $request->filled('token')) {
                $pat = PersonalAccessToken::findToken($request->input('token'));
                if ($pat) {
                    $user = $pat->tokenable;
                }
            }

            if (!$user) {
                return $this->serviceResponse(
                    new ServiceResponse(401, false, 'Kullanıcı doğrulanamadı. Lütfen geçerli bir token sağlayın.', null)
                );
            }

            $userId = $user->id;
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
            // Delegate clearing to the cache service (maintains index set)
            $deletedCount = $this->cacheService->clearAll();

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
        $ids = $this->cacheService->allIds();
        if (empty($ids)) {
            return [];
        }

        $messages = [];

        foreach ($ids as $id) {
            $msg = $this->cacheService->get((int) $id);

            if (!$msg) {
                Log::warning('Message not found in Redis', ['id' => $id]);
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
    // Redis key helper removed — MessageCacheService maintains the index and controllers
    // should use the service APIs (allIds(), clearAll(), get(), set(), delete()).

    public function testRedisConnection()
    {
        // removed: diagnostic moved to CacheMonitorController::testRedisConnection
        return response()->json(['message' => 'Diagnostic endpoint moved to CacheMonitorController'], 200);
    }
}
