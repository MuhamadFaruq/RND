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
        Schema::create('marketing_orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sap_no')->unique();
            $table->string('art_no')->nullable();
            $table->date('tanggal');
            $table->text('pelanggan');
            $table->string('mkt');
            $table->string('keperluan');
            $table->string('konstruksi_greige');
            $table->string('material');
            $table->string('benang');
            $table->string('kelompok_kain');
            $table->string('target_lebar'); // UBAH KE STRING
            $table->string('belah_bulat');
            $table->string('target_gramasi'); // UBAH KE STRING
            $table->string('warna');
            $table->string('handfeel');
            $table->string('treatment_khusus')->nullable();
            $table->unsignedInteger('roll_target');
            $table->unsignedInteger('kg_target');
            $table->text('keterangan_artikel')->nullable();
            
            // UPDATE STATUS AGAR SESUAI ESTAFET 9 DIVISI
            $table->enum('status', [
                'knitting', 'dyeing', 'relax-dryer', 'finishing', 
                'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished'
            ])->default('knitting'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_orders');
    }
};

