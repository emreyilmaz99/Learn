<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CacheMonitorController;
use App\Http\Controllers\CacheMessagesController;


Route::get('/', function () {
    return view('welcome');
});

// Simple page to test API register/login from the browser
Route::view('/auth', 'auth');

// Simple page to test Messages CRUD with Bearer token
Route::view('/messages', 'messages');

// Cache Monitoring Routes (development/learning only)
Route::prefix('cache')->group(function () {
    Route::get('/stats', [CacheMonitorController::class, 'stats']);
    Route::get('/keys', [CacheMonitorController::class, 'keys']);
    Route::get('/recent', [CacheMonitorController::class, 'recent']);
    Route::get('/reset', [CacheMonitorController::class, 'reset']);
    Route::get('/inspect/{id}', [CacheMonitorController::class, 'inspect']);
});

Route::get('/cache-messages', [CacheMessagesController::class, 'index']);

