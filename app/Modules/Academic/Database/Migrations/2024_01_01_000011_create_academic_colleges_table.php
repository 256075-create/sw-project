<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_colleges', function (Blueprint $table) {
            $table->increments('college_id');
            $table->unsignedInteger('university_id');
            $table->string('name', 200);
            $table->string('code', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('university_id')
                ->references('university_id')
                ->on('academic_universities')
                ->onDelete('cascade');

            $table->unique(['university_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_colleges');
    }
};
