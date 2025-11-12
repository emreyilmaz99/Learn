<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Simple page to test API register/login from the browser
Route::view('/auth', 'auth');

// Simple page to test Messages CRUD with Bearer token
Route::view('/messages', 'messages');

// Debug routes were removed per user request (kept web routes minimal)
