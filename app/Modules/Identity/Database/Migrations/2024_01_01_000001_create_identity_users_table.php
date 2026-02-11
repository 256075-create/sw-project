<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('username', 100)->unique();
            $table->string('email', 200)->unique();
            $table->string('password_hash', 255);
            $table->boolean('is_active')->default(true);
            $table->boolean('mfa_enabled')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_login')->nullable();

            $table->index(['email']);
            $table->index(['username']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_users');
    }
};
