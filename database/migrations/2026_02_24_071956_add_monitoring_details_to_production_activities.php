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
        Schema::table('production_activities', function (Blueprint $table) {
            // Detail Rajut (Monitoring Rajut)
            $table->string('pelanggan')->nullable();
            $table->string('mkt')->nullable(); 
            $table->string('konstruksi_greige')->nullable();
            $table->string('gramasi_greige')->nullable();
            $table->string('mesin_rajut')->nullable();
            $table->string('kelompok_mesin')->nullable(); 
            $table->string('target_lebar')->nullable();
            $table->string('belah_bulat')->nullable(); 
            $table->string('target_gramasi')->nullable();
            $table->string('treatment_khusus')->nullable();
            $table->text('keterangan_artikel')->nullable();

            // Detail Warna & Tracking (Monitoring Warna)
            $table->string('warna')->nullable();
            $table->string('handfeel')->nullable();
            $table->date('tgl_kirim_ddt2')->nullable();
            $table->string('terima_dpf3')->nullable(); 
            $table->date('tgl_selesai')->nullable();
            $table->string('timeline')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_activities', function (Blueprint $table) {
            //
        });
    }
};
