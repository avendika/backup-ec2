<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// User routes
Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::put('/avatar', [UserController::class, 'updateAvatar']);
    Route::put('/progress', [UserController::class, 'updateProgress']);
    Route::put('/password', [UserController::class, 'changePassword']);
});

// Public routes
Route::get('/avatars', [UserController::class, 'getAvatars']);

// Fallback route for any not found API endpoints
Route::fallback(function(){
    return response()->json([
        'success' => false,
        'message' => 'Endpoint tidak ditemukan'
    ], 404);
});