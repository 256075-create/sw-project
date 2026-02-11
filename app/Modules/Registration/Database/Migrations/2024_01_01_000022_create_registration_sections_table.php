<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_sections', function (Blueprint $table) {
            $table->increments('section_id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('classroom_id');
            $table->string('section_number', 10);
            $table->string('instructor_name', 200);
            $table->unsignedInteger('max_capacity');
            $table->unsignedInteger('current_enrollment')->default(0);
            $table->string('semester', 20);
            $table->string('academic_year', 9);
            $table->timestamps();

            $table->foreign('course_id')
                ->references('course_id')
                ->on('registration_courses')
                ->onDelete('cascade');

            $table->foreign('classroom_id')
                ->references('classroom_id')
                ->on('registration_classrooms')
                ->onDelete('cascade');

            $table->unique(['course_id', 'section_number', 'semester', 'academic_year'], 'sections_course_section_semester_year_unique');
            $table->index(['semester', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_sections');
    }
};
