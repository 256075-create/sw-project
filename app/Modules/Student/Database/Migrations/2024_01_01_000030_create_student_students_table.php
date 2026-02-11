<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_students', function (Blueprint $table) {
            $table->bigIncrements('student_id');
            $table->unsignedInteger('major_id');
            $table->uuid('user_id')->nullable();
            $table->string('student_number', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 200)->unique();
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'inactive', 'graduated', 'suspended'])->default('active');
            $table->timestamps();

            $table->foreign('major_id')
                ->references('major_id')
                ->on('academic_majors')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('user_id')
                ->on('identity_users')
                ->onDelete('set null');

            $table->index(['student_number']);
            $table->index(['email']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_students');
    }
};
