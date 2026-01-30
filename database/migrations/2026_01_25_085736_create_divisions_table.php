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
        // database/migrations/xxxx_create_divisions_table.php
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // Contoh: "Quality Control"
            $table->string('slug')->unique(); // Contoh: "qc"
            $table->string('icon')->nullable(); // Contoh: "🔬"
            $table->string('description')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
