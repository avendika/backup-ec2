<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\LeaderboardController; // Import controller baru

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/avatars', [UserController::class, 'getAvailableAvatars']);

// Leaderboard route - Bisa diakses public atau protected (pilih salah satu)
// Option 1: Public access (semua orang bisa lihat leaderboard)
Route::get('/leaderboard', [LeaderboardController::class, 'index']);

// Option 2: Protected access (hanya user yang login bisa lihat)
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/leaderboard', [LeaderboardController::class, 'index']);
// });

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
    
    // Additional leaderboard endpoints yang memerlukan authentication
    Route::prefix('leaderboard')->group(function () {
        // Get user's position in leaderboard
        Route::get('/my-position', [LeaderboardController::class, 'getUserPosition']);
        
        // Get leaderboard by specific criteria
        Route::get('/by-level', [LeaderboardController::class, 'getByLevel']);
        Route::get('/weekly', [LeaderboardController::class, 'getWeekly']);
        Route::get('/monthly', [LeaderboardController::class, 'getMonthly']);
    });
});
