<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel users
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('action'); // Contoh: CREATE_USER, UPDATE_ORDER
            $table->string('module'); // Contoh: User Management, Marketing
            $table->text('details');  // Untuk menyimpan detail perubahan
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};