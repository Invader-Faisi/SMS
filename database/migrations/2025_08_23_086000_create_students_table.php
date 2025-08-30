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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('image');
            $table->string('parent_id');
            $table->string('name');
            $table->string('password');
            $table->string('class',['Nursery','Prep','I','II','III','IV','V','VI','VII','VIII','IX','X']);
            $table->enum('section', ['A', 'B', 'C', 'D', 'E', 'F']);
            $table->foreign('parent_id')->references('parent_id')->on('parents');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
