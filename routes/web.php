<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserController;

// Redirect halaman utama ke daftar user
Route::get('/', [UserController::class, 'index'])->name('home');

// CRUD user
Route::resource('users', UserController::class);

// Route untuk mengubah password
Route::put('users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');

// Route untuk menampilkan avatar
Route::get('api/avatars/{filename}', 'App\Http\Controllers\AvatarController@show')->name('avatar.show');

use Illuminate\Support\Facades\DB;

Route::get('/cek-db', function () {
    try {
        DB::connection()->getPdo();
        return "Koneksi ke Oracle berhasil! DB: " . DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        return "Koneksi gagal: " . $e->getMessage();
    }
});