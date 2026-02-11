<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_classrooms', function (Blueprint $table) {
            $table->increments('classroom_id');
            $table->string('room_number', 20);
            $table->string('building', 100);
            $table->unsignedInteger('capacity');
            $table->timestamps();

            $table->unique(['building', 'room_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_classrooms');
    }
};
