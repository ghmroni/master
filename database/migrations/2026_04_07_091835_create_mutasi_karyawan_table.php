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
        Schema::create('mutasi_karyawan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('project_sebelumnya');
            $table->string('project_tujuan');
            $table->text('alasan_mutasi');
            $table->date('tanggal_efektif');
            $table->unsignedBigInteger('users_id');
            $table->enum('status_mutasi', ['PENDING', 'SELESAI', 'DITOLAK']);
            $table->text('alasan_ditolak')->nullable();
            $table->unsignedBigInteger('approval_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_karyawan');
    }
};
