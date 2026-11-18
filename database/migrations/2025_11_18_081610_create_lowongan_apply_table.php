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
        Schema::create('lowongan_apply', function (Blueprint $table) {
            $table->string('no_registrasi')->unique(); // unik, bisa UUID atau kode custom
            $table->unsignedBigInteger('pelamar_id');
            $table->unsignedBigInteger('lowongan_id');
            $table->timestamps();

            $table->primary(['no_registrasi', 'pelamar_id', 'lowongan_id']);
            $table->foreign('pelamar_id')->references('id_pelamar')->on('pelamar')->onDelete('cascade');
            $table->foreign('lowongan_id')->references('id')->on('lowongan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lowongan_apply');
    }
};
