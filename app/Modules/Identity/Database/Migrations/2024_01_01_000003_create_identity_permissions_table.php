<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->string('permission_name', 100)->unique();
            $table->string('resource', 50);
            $table->string('action', 50);

            $table->unique(['resource', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_permissions');
    }
};
