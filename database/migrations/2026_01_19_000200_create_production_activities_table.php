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
        Schema::create('production_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_order_id')->constrained('marketing_orders')->cascadeOnDelete();
            $table->string('division_name');
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'knitting', 'dyeing', 'finishing', 'qc', 'completed'])->default('pending');
            $table->json('technical_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_activities');
    }
};

