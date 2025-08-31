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
            $table->string('teacher_id',11)->unique();
            $table->string('image');
            $table->string('name',24);
            $table->string('cnic',16)->unique();
            $table->string('email',32)->unique();
            $table->string('password',12);
            $table->string('mobile',11)->unique();
            $table->string('address');
            $table->string('qualification',11);
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
