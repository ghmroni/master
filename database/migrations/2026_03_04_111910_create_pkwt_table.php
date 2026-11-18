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
        Schema::create('pkwt', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kontrak_id');
            $table->string('nomor');
            $table->string('nip');
            $table->string('ktp')->nullable();
            $table->string('nama');
            $table->string('ttl');
            $table->text('alamat')->nullable();
            $table->string('hp');
            $table->string('project_detail');
            $table->string('jabatan');
            $table->string('dari');
            $table->string('sampai');
            $table->string('lokasi_ttd');
            $table->string('pendapatan')->nullable();
            $table->string('pendapatan_lain')->nullable();
            $table->string('tunjangan')->nullable();
            $table->string('potongan')->nullable();
            $table->string('potongan_lain')->nullable();
            $table->string('template');
            $table->string('petugas');
            $table->string('jabatan_petugas');
            $table->text('barcode');
            $table->text('alasan')->nullable();
            $table->enum('status', ['CREATE', 'UPDATE', 'DISNAKER', 'DISNAKER UPDATE']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkwt');
    }
};
