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
        Schema::create('salary_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id')->nullable();
            $table->string('staff_id')->nullable();
            $table->enum('type', ['teacher', 'staff']);
            $table->enum('category', ['first', 'second','third','lower']);
            $table->enum('name',['Late','Loan','Leave']);
            $table->string('multiples');
            $table->decimal('amount',10,2);
            $table->timestamps();
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('staff_id')->references('staff_id')->on('staffs')->onDelete('restrict')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary-deductions');
    }
};
