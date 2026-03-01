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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Mencatat ID user yang menghapus
            $table->string('action'); // Berisi: "DELETE_PRODUCTION_LOG"
            $table->string('division'); // Nama divisi (knitting, dyeing, dll)
            $table->string('sap_no'); // Nomor SAP yang dihapus
            $table->text('details'); // Informasi KG/Roll yang hilang
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
