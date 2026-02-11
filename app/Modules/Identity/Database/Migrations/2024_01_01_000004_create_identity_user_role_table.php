<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_user_role', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamp('assigned_at')->useCurrent();

            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('user_id')->on('identity_users')->onDelete('cascade');
            $table->foreign('role_id')->references('role_id')->on('identity_roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_user_role');
    }
};
