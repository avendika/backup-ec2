<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'username')) {
            $table->string('username')->unique();
        }
        if (!Schema::hasColumn('users', 'avatar')) {
            $table->string('avatar')->default('assets/avatars/default.png');
        }
        if (!Schema::hasColumn('users', 'level')) {
            $table->integer('level')->default(1);
        }
        if (!Schema::hasColumn('users', 'score')) {
            $table->integer('score')->default(0);
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
