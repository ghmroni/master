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
        Schema::create('simc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('kategori')->nullable(); // TAHUNAN, GRAVIDA, ISTIMEWA
            $table->enum('type_simc', ['SAKIT', 'IZIN', 'CUTI']);
            $table->date('tanggal_dari');
            $table->date('tanggal_sampai');
            $table->date('tanggal_masuk')->nullable();
            $table->integer('jumlah_hari');
            $table->string('keterangan')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->dateTime('tanggal_approve')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('alasan_ditolak')->nullable();
            $table->string('lampiran')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simc');
    }
};
