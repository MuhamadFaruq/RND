<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cara paling aman: Ubah ke VARCHAR agar tidak bentrok dengan ENUM lama
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
    }

    public function down(): void
    {
        // Opsional: Kembalikan ke ENUM jika diperlukan
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
    }
};