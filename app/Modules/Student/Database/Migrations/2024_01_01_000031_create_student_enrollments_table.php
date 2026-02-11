<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->bigIncrements('enrollment_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedInteger('section_id');
            $table->timestamp('enrollment_date')->useCurrent();
            $table->enum('status', ['enrolled', 'dropped', 'completed', 'failed'])->default('enrolled');
            $table->timestamps();

            $table->foreign('student_id')
                ->references('student_id')
                ->on('student_students')
                ->onDelete('cascade');

            $table->foreign('section_id')
                ->references('section_id')
                ->on('registration_sections')
                ->onDelete('cascade');

            $table->unique(['student_id', 'section_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
