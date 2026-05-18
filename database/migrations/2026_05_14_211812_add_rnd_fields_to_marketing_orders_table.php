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
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->string('rnd_gramasi_greige')->nullable()->after('keterangan_artikel');
            $table->string('rnd_mesin_rajut')->nullable()->after('rnd_gramasi_greige');
            $table->string('rnd_jenis_mesin_rajut')->nullable()->after('rnd_mesin_rajut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->dropColumn(['rnd_gramasi_greige', 'rnd_mesin_rajut', 'rnd_jenis_mesin_rajut']);
        });
    }
};
