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
        if (!Schema::hasColumn('users', 'division_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('division_id')->nullable()->constrained('divisions');
            });
        }
    }

    public function down() // Tambahkan baris di bawah ini jika belum ada
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']); // Hapus relasi jika ada
            $table->dropColumn('division_id');    // Hapus kolomnya
        });
    }
};
