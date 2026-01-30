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
        if (!Schema::hasColumn('marketing_orders', 'tanggal')) {
            $table->string('tanggal')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'mkt')) {
            $table->string('mkt')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'keperluan')) {
            $table->string('keperluan')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'konstruksi_greige')) {
            $table->string('konstruksi_greige')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'material')) {
            $table->string('material')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'benang')) {
            $table->string('benang')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'kelompok_kain')) {
            $table->string('kelompok_kain')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'belah_bulat')) {
            $table->string('belah_bulat')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'handfeel')) {
            $table->string('handfeel')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'treatment_khusus')) {
            $table->text('treatment_khusus')->nullable();
        }
        if (!Schema::hasColumn('marketing_orders', 'roll')) {
            $table->integer('roll')->default(0);
        }
        if (!Schema::hasColumn('marketing_orders', 'kg')) {
            $table->integer('kg')->default(0);
        }
        if (!Schema::hasColumn('marketing_orders', 'keterangan_artikel')) {
            $table->text('keterangan_artikel')->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            //
        });
    }
};
