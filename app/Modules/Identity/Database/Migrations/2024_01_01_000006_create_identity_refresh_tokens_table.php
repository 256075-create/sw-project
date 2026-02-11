<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_refresh_tokens', function (Blueprint $table) {
            $table->uuid('token_id')->primary();
            $table->uuid('user_id');
            $table->string('token_hash', 255);
            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('user_id')->on('identity_users')->onDelete('cascade');
            $table->index(['token_hash']);
            $table->index(['user_id', 'revoked']);
            $table->index(['expires_at', 'revoked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_refresh_tokens');
    }
};
