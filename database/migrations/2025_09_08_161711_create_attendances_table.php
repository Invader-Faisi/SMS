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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('class_id');
            $table->date('date');
            $table->enum('status', ['Present', 'Absent', 'Leave'])->default('Present');
            $table->string('remarks')->nullable();
            $table->foreign('class_id')
                ->references('class_id')
                ->on('classes')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->unique(['student_id','class_id','date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
