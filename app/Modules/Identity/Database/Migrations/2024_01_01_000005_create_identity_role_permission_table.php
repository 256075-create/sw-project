<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_role_permission', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');

            $table->primary(['role_id', 'permission_id']);
            $table->foreign('role_id')->references('role_id')->on('identity_roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('permission_id')->on('identity_permissions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_role_permission');
    }
};
