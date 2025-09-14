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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id')->nullable();
            $table->string('staff_id')->nullable();
            $table->unsignedBigInteger('salary_structure_id');
            $table->unsignedBigInteger('salary_deduction_id');

            $table->decimal('gross_salary', 10, 2);
            $table->decimal('total_deduction', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->timestamp('payment_date')->nullable();
            $table->string('account')->nullable();
            $table->enum('payment_method', ['Cash', 'Bank Transfer', 'Cheque'])->default('Cash');
            $table->enum('status', ['Paid', 'Pending'])->default('Pending');
            $table->timestamps();
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('staff_id')->references('staff_id')->on('staffs')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('salary_structure_id')->references('id')->on('salary_structures')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('salary_deduction_id')->references('id')->on('salary_deductions')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
