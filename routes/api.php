<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\AvatarController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/avatars', [UserController::class, 'getAvailableAvatars']);

// Direct avatar access route - NO AUTH REQUIRED
Route::get('/avatars/{filename}', [AvatarController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/progress', [UserController::class, 'updateProgress']);
        Route::put('/password', [UserController::class, 'changePassword']);
        
        // Avatar endpoints
        Route::post('/avatar', [FileController::class, 'uploadAvatar']);
        Route::put('/avatar', [UserController::class, 'updateAvatar']);
    });
});