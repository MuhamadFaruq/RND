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
            if (!Schema::hasColumn('marketing_orders', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('production_activities', function (Blueprint $table) {
             if (!Schema::hasColumn('production_activities', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('production_activities', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
