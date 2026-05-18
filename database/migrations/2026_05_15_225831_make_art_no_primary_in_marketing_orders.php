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
            $table->string('art_no')->nullable(false)->unique()->change();
            $table->bigInteger('sap_no')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->string('art_no')->nullable()->dropUnique(['art_no'])->change();
            $table->bigInteger('sap_no')->nullable(false)->change();
        });
    }
};
