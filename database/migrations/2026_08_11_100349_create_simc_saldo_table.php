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
        Schema::create('simc_saldo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->year('tahun');
            $table->integer('saldo')->default(12); // jatah cuti tahunan
            $table->integer('saldo_sebelumnya')->default(0); // sisa saldo tahun sebelumnya
            $table->date('saldo_expired')->nullable(); // batas kadaluarsa saldo_sebelumnya
            $table->integer('saldo_terpakai')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simc_saldo');
    }
};
