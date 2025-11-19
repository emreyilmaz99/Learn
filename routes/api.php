<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CacheMessagesController;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/test-redis', [\App\Http\Controllers\CacheMonitorController::class, 'testRedisConnection']); // Moved outside middleware

// Ensure route parameter `message` is numeric so controller methods expecting numeric ids
// won't receive arbitrary strings.
Route::pattern('message', '[0-9]+');

// Protected routes (authentication required)
Route::middleware([
    \App\Http\Middleware\CheckTokenHeader::class,
    'auth:sanctum'
])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Kullanıcı listesi (mesaj göndermek için)
    Route::get('/users', [MessageController::class, 'getUsers']);

    // Gönderilen ve alınan mesajlar
    Route::get('/messages/sent', [MessageController::class, 'sent']);
    Route::get('/messages/inbox', [MessageController::class, 'inbox']);
    
    // Message routes
    Route::apiResource('messages', MessageController::class);

    // Konuşmalar
    Route::get('/conversations/{userId}', [MessageController::class, 'conversation']);
    Route::post('/conversations/{userId}/send', [MessageController::class, 'sendMessage']);
    // search messages via Elasticsearch
    Route::get('/messages/search', [\App\Http\Controllers\Api\MessageSearchController::class, 'search']);
    // suggestions for autocomplete (users)
    Route::get('/messages/suggestions', [\App\Http\Controllers\Api\MessageSearchController::class, 'suggestions']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'store']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);

    // Cache Operations
    Route::get('/cache/set', [CacheMessagesController::class, 'cacheAllMessages']);
    Route::get('/cache/clear', [CacheMessagesController::class, 'clearCache']);
});
