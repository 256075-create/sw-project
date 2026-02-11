<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_universities', function (Blueprint $table) {
            $table->increments('university_id');
            $table->string('name', 200);
            $table->string('code', 20)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_universities');
    }
};
