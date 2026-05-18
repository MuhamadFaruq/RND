<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom flag alur kerja dinamis pada tabel marketing_orders.
     * Kolom ini digunakan untuk menentukan proses produksi mana yang diperlukan
     * untuk setiap order (opsional vs wajib).
     */
    public function up(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            // Proses opsional — default false (tidak wajib dilalui)
            $table->boolean('req_stenter')->default(false)->after('is_urgent');
            $table->boolean('req_compactor')->default(false)->after('req_stenter');
            $table->boolean('req_heat_setting')->default(false)->after('req_compactor');
            $table->boolean('req_tumbler')->default(false)->after('req_heat_setting');
            $table->boolean('req_fleece')->default(false)->after('req_tumbler');

            // Proses wajib — default true (selalu dilalui kecuali dikecualikan)
            $table->boolean('req_pengujian')->default(true)->after('req_fleece');
            $table->boolean('req_qe')->default(true)->after('req_pengujian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->dropColumn([
                'req_stenter',
                'req_compactor',
                'req_heat_setting',
                'req_tumbler',
                'req_fleece',
                'req_pengujian',
                'req_qe',
            ]);
        });
    }
};
