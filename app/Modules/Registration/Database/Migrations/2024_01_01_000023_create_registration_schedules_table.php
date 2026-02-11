<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_schedules', function (Blueprint $table) {
            $table->increments('schedule_id');
            $table->unsignedInteger('section_id');
            $table->enum('day_of_week', [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ]);
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->foreign('section_id')
                ->references('section_id')
                ->on('registration_sections')
                ->onDelete('cascade');

            $table->index(['section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_schedules');
    }
};
