<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add updated_at to academic_colleges
        if (!Schema::hasColumn('academic_colleges', 'updated_at')) {
            Schema::table('academic_colleges', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }

        // Add updated_at to academic_departments
        if (!Schema::hasColumn('academic_departments', 'updated_at')) {
            Schema::table('academic_departments', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }

        // Add timestamps to academic_majors
        if (!Schema::hasColumn('academic_majors', 'created_at')) {
            Schema::table('academic_majors', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('academic_colleges', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
        Schema::table('academic_departments', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
        Schema::table('academic_majors', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
