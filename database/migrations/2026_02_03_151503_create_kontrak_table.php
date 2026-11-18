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
        Schema::create('kontrak', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->unsignedBigInteger('statuskontrak_id');
            $table->string('nomor_kontrak');
            $table->date('kontrak_dari');
            $table->date('kontrak_sampai')->nullable();
            $table->string('kontrak_ke', 2)->default(0);
            $table->string('lokasi_kontrak', 30);
            $table->date('tanggal_kontrak');
            $table->date('tanggal_ptko')->nullable();
            $table->string('keterangan_ptko')->nullable();
            $table->enum('status_kontrak', ['AKTIF', 'TIDAK AKTIF']);
            $table->string('upload_pkwt')->nullable();
            $table->unsignedBigInteger('pkwt_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak');
    }
};
