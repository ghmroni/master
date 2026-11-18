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
        Schema::create('simc_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simc_id'); // relasi ke pengajuan
            $table->year('tahun_saldo'); // tahun saldo yang dipakai
            $table->date('tanggal_pengajuan'); // tanggal cuti
            $table->enum('source_type', ['saldo_sebelumnya', 'saldo_berjalan']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simc_detail');
    }
};
