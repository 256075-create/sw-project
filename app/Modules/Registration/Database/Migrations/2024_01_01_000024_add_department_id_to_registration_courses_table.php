<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_courses', function (Blueprint $table) {
            $table->unsignedInteger('department_id')->nullable()->after('course_id');

            $table->foreign('department_id')
                ->references('department_id')
                ->on('academic_departments')
                ->onDelete('set null');

            $table->index(['department_id']);
        });
    }

    public function down(): void
    {
        Schema::table('registration_courses', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
