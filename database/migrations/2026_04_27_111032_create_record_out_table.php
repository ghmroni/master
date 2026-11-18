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
        Schema::create('record_out', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('project_detail');
            $table->string('nomor_surat')->nullable();
            $table->string('nip');
            $table->string('nama');
            $table->string('ttl');
            $table->string('jabatan')->nullable();
            $table->string('nama_project')->nullable();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar');
            $table->string('lokasi_ttd')->nullable();
            $table->enum('status', ['WAITING', 'SUCCESS']);
            $table->enum('status_out', ['MENGUNDURKAN DIRI', 'HABIS KONTRAK', 'MENINGGAL DUNIA']);
            $table->text('catatan_keluar')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('file')->nullable();
            $table->text('barcode')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_out');
    }
};
