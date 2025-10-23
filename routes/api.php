<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
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

});
