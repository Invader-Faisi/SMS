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
            $table->string('student_id', 24)->unique();
            $table->string('parent_id',11);
            $table->string('image');
            $table->string('name',24);
            $table->string('b_form',16)->unique();
            $table->string('password',12);
            $table->enum('class',['Nursery','Prep','I','II','III','IV','V','VI','VII','VIII','IX','X']);
            $table->enum('section', ['A', 'B', 'C', 'D', 'E', 'F']);
            $table->foreign('parent_id')
                ->references('parent_id')
                ->on('parents')
                ->onDelete('restrict')
                ->onUpdate('cascade');
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
