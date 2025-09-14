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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->decimal('pending_amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'partial'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('fee_structure_id')
                ->references('id')
                ->on('fee_structures')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->unique(['student_id', 'fee_structure_id', 'due_date'], 'unique_fee_entry');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
