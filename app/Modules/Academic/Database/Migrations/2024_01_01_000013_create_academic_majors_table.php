<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_majors', function (Blueprint $table) {
            $table->increments('major_id');
            $table->unsignedInteger('department_id');
            $table->string('name', 200);
            $table->string('code', 20);
            $table->unsignedInteger('total_credits');

            $table->foreign('department_id')
                ->references('department_id')
                ->on('academic_departments')
                ->onDelete('cascade');

            $table->unique(['department_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_majors');
    }
};
