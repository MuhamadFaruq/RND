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
            // Update enum status to include missing production steps
            $table->enum('status', [
                'knitting', 'dyeing', 'relax-dryer', 'compactor', 'heat-setting', 
                'finishing', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished'
            ])->default('knitting')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
             $table->enum('status', [
                'knitting', 'dyeing', 'relax-dryer', 'finishing', 
                'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished'
            ])->default('knitting')->change();
        });
    }
};
