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
        Schema::create('marketing_quotations', function (Blueprint $group) {
            $group->id();
            $group->foreignId('user_id')->constrained()->onDelete('cascade');
            $group->string('customer_name')->nullable();
            $group->string('article_name')->nullable();
            
            // Costs
            $group->decimal('yarn_price', 15, 2)->default(0);
            $group->decimal('chemical_price', 15, 2)->default(0);
            $group->decimal('knitting_fee', 15, 2)->default(0);
            $group->decimal('dyeing_fee', 15, 2)->default(0);
            $group->decimal('overhead', 15, 2)->default(0);
            
            // Factors
            $group->decimal('waste_knitting', 5, 2)->default(0);
            $group->decimal('waste_dyeing', 5, 2)->default(0);
            $group->decimal('margin', 5, 2)->default(0);
            $group->decimal('ppn', 5, 2)->default(0);
            
            // Results
            $group->decimal('hpp', 15, 2)->default(0);
            $group->decimal('selling_price', 15, 2)->default(0);
            
            $group->timestamps();
            $group->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_quotations');
    }
};
