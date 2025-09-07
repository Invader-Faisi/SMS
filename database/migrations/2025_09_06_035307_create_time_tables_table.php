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
        Schema::create('time_tables', function (Blueprint $table) {
            $table->id();
            $table->string('class_id');
            $table->string('teacher_id');
            $table->enum('subject', ['Math','English','Physics','Biology','Chemistry','Computer','Urdu','History','Social Studies','Islamiat','Science']);
            $table->enum('days',['Mon','Tue','Wed','Thu','Fri','Sat']);
            $table->integer('period');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreign('class_id')
                ->references('class_id')
                ->on('classes')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('teacher_id')
                ->references('teacher_id')
                ->on('teachers')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->unique(['teacher_id','days','period']);
            $table->unique(['class_id','days','period']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_tables');
    }
};
