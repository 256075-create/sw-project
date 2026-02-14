<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add updated_at to identity_users
        if (!Schema::hasColumn('identity_users', 'updated_at')) {
            Schema::table('identity_users', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }

        // Add timestamps to identity_roles
        if (!Schema::hasColumn('identity_roles', 'created_at')) {
            Schema::table('identity_roles', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        // Add timestamps to identity_permissions
        if (!Schema::hasColumn('identity_permissions', 'created_at')) {
            Schema::table('identity_permissions', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        // Add updated_at to identity_refresh_tokens
        if (!Schema::hasColumn('identity_refresh_tokens', 'updated_at')) {
            Schema::table('identity_refresh_tokens', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('identity_users', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
        Schema::table('identity_roles', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table('identity_permissions', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table('identity_refresh_tokens', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
