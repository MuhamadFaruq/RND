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
            $table->foreignId('processing_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processing_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->dropForeign(['processing_by']);
            $table->dropColumn(['processing_by', 'processing_at']);
        });
    }
};
