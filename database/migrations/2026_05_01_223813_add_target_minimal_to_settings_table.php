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
        // Menyisipkan nilai default untuk target_minimal
        DB::table('settings')->updateOrInsert(
            ['key' => 'target_minimal'],
            ['value' => '400', 'group' => 'global', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'target_minimal')->delete();
    }
};
