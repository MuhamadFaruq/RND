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
            // Menambahkan kolom kg (decimal untuk angka koma) dan roll (integer)
            // Diletakkan setelah kolom technical_data
            $table->decimal('kg', 10, 2)->nullable()->after('technical_data');
            $table->integer('roll')->nullable()->after('kg');
        });
    }

    public function down(): void
    {
        Schema::table('production_activities', function (Blueprint $table) {
            $table->dropColumn(['kg', 'roll']);
        });
    }
};
