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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('class',['Junior','Primary','Middle','Secondary']);
            $table->decimal('amount',10,2);
            $table->enum('type', ['Admission', 'Recurring','Fine','Misc'])->default('Recurring');
            $table->enum('frequency', ['Monthly', 'Yearly', 'One Time'])->default('Monthly');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
