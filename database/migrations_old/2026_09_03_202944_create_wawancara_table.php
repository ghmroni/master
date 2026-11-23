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
        Schema::create('wawancara', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pemanggilan_id');
            $table->unsignedBigInteger('pelamar_id');
            $table->unsignedBigInteger('user_id');
            $table->date('tglpanggil');
            $table->string('nama');
            $table->date('tgllahir');
            $table->text('jenjang_pendidikan')->nullable();
            $table->string('kategori')->nullable();
            $table->string('bb')->nullable();
            $table->string('tb')->nullable();
            $table->string('referensi', 1)->nullable();
            $table->string('pendidikan', 1)->nullable();
            $table->string('pengalaman', 1)->nullable();
            $table->string('pengetahuan', 1)->nullable();
            $table->string('keterampilan', 1)->nullable();
            $table->string('motivasi', 1)->nullable();
            $table->string('kerjasama', 1)->nullable();
            $table->string('etika', 1)->nullable();
            $table->string('komunikasi', 1)->nullable();
            $table->string('bahasa', 1)->nullable();
            $table->string('penampilan', 1)->nullable();
            $table->string('memimpin', 1)->nullable();
            $table->string('bahasa_asing', 50)->nullable();
            $table->string('hasil', 50)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wawancara');
    }
};
