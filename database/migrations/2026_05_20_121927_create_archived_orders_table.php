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
        Schema::create('archived_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_order_id')->nullable();
            $table->string('sap_no')->nullable();
            $table->string('art_no')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('pelanggan')->nullable();
            $table->string('mkt')->nullable();
            
            // Simpan seluruh raw data order dari DB asli
            $table->json('original_data')->nullable();
            // Simpan seluruh data production activity
            $table->json('production_logs')->nullable();
            
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_orders');
    }
};
