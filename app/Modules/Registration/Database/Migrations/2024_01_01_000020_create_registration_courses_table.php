<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_courses', function (Blueprint $table) {
            $table->increments('course_id');
            $table->string('course_code', 20)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->unsignedInteger('credit_hours');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['course_code']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_courses');
    }
};
