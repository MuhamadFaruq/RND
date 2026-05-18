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
            $table->dropUnique(['art_no']);
            // Tambahkan index biasa agar pencarian tetap cepat
            $table->index('art_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->dropIndex(['art_no']);
            $table->unique('art_no');
        });
    }
};
