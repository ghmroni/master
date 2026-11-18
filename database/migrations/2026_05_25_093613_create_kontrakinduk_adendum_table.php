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
        Schema::create('kontrakinduk_adendum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kontrakinduk_id');
            $table->unsignedBigInteger('setting_id');
            $table->string('judul_adendum');
            $table->string('nomor_adendum');
            $table->integer('adendum_ke');
            $table->date('tanggal_terbit');
            $table->date('tanggal_mulai_berlaku');
            $table->date('tanggal_akhir_berlaku');
            $table->text('keterangan')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Relasi ke tabel induk
            $table->foreign('kontrakinduk_id')
                ->references('id')
                ->on('kontrakinduk')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrakinduk_adendum');
    }
};
