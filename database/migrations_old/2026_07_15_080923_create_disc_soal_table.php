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
        Schema::create('disc_soal', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_soal');
            $table->string('nomor_baris');
            $table->string('pernyataan');
            $table->string('faktor_paling', 2);
            $table->string('faktor_kurang', 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_soal');
    }
};
