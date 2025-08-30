<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id')->unique();
            $table->string('image');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('mobile');
            $table->string('address');
            $table->string('qualification');
            $table->enum('designation', ['ClassTeacher','SubjectSpecialist', 'Teacher'])->default('Teacher');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
