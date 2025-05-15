<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Username dengan panjang 30 karakter (untuk kompatibilitas Oracle)
            $table->string('username', 30)->unique();
            
            // Password dengan panjang maksimal 120 untuk hash
            $table->string('password', 120);
            
            // Avatar path
            $table->string('avatar', 100)->default('assets/avatars/default.png');
            
            // Level dan score untuk progres user
            $table->integer('level')->default(1);
            $table->integer('score')->default(0);
            
            // Remember token untuk fitur "remember me"
            $table->string('remember_token', 100)->nullable();
            
            // Oracle database memiliki batasan pada nama kolom, maka kita buat manual
            // timestamps() biasanya membuat created_at dan updated_at
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};