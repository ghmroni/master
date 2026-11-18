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
        Schema::create('flight_lampiran', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->string('cabang');
            $table->json('data_flight');
            $table->json('data_rekap');
            $table->json('data_layanan')->nullable();
            $table->json('data_petugas');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_lampiran');
    }
};
