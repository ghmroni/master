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
        Schema::create('pemanggilan_pelamar', function (Blueprint $table) {
            $table->id();
            $table->string('no_registrasi'); // foreign key ke lowongan_apply
            $table->date('tgl_panggil');
            $table->enum('status', ['PENDING', 'PROSES', 'USER', 'HRD', 'DITERIMA', 'GAGAL']);
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->datetime('create_hadir')->nullable();
            $table->enum('hadir', ['YA', 'TIDAK'])->nullable()->default(null);
            $table->unsignedTinyInteger('hasil_test')->nullable();
            $table->unsignedBigInteger('cfit_id')->nullable();
            $table->unsignedBigInteger('mbti_id')->nullable();
            $table->unsignedBigInteger('papi_id')->nullable();
            $table->unsignedBigInteger('disc_id')->nullable();
            $table->unsignedBigInteger('wawancara_id')->nullable();
            $table->text('hasil_catatan')->nullable();
            $table->datetime('hasil_tanggal')->nullable();
            $table->datetime('user_tanggal')->nullable();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->string('lokasi_penempatan')->nullable();
            $table->datetime('approval_tanggal')->nullable();
            $table->datetime('hr_tanggal')->nullable();
            $table->string('hr_catatan')->nullable();
            $table->json('data_analisis')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('no_registrasi')->references('no_registrasi')->on('lowongan_apply')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemanggilan_pelamar');
    }
};
