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
        Schema::create('knitting_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Mencatat siapa operatornya
            $table->string('sap_no')->nullable(); // Menghubungkan ke order marketing
            
            // MESIN
            $table->date('tanggal');
            $table->string('no_mesin');
            $table->string('type_mesin');
            $table->integer('gauge_inch');
            $table->integer('jml_feeder');
            $table->integer('jml_jarum');

            // HASIL GREIGE
            $table->float('lebar');
            $table->integer('gramasi');
            $table->integer('kg');
            $table->integer('roll');

            // PENGGUNAAN BENANG
            $table->string('benang_1')->nullable();
            $table->string('benang_2')->nullable();
            $table->string('benang_3')->nullable();
            $table->string('benang_4')->nullable();
            $table->integer('yl_1')->nullable();
            $table->integer('yl_2')->nullable();
            $table->integer('yl_3')->nullable();
            $table->integer('yl_4')->nullable();

            // LAIN-LAIN
            $table->text('note')->nullable();
            $table->integer('produksi_per_day');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knitting_logs');
    }
};
