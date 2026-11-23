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
        Schema::create('papi_hasil', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pelamar_id');
            $table->string('noreg');
            $table->date('tglpanggil');
            $table->string('nama');
            $table->date('tgllahir');
            $table->string('kelamin');
            $table->string('usia')->nullable();
            $table->json('jawaban')->nullable();
            $table->string('total_atas')->nullable();
            $table->string('total_bawah')->nullable();
            $table->json('analisis')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papi_hasil');
    }
};
