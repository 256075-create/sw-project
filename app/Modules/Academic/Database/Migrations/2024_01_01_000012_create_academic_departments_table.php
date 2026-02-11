<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_departments', function (Blueprint $table) {
            $table->increments('department_id');
            $table->unsignedInteger('college_id');
            $table->string('name', 200);
            $table->string('code', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('college_id')
                ->references('college_id')
                ->on('academic_colleges')
                ->onDelete('cascade');

            $table->unique(['college_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_departments');
    }
};
