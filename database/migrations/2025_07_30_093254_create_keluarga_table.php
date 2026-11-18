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
        Schema::create('keluarga', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelamar_id');
            $table->enum('status_hubungan', ['SUAMI', 'ISTRI', 'ANAK', 'AYAH KANDUNG', 'IBU KANDUNG', 'AYAH MERTUA', 'IBU MERTUA', 'LAINNYA']);
            $table->string('nama_keluarga');
            // $table->string('kk_keluarga')->nullable();
            $table->string('ktp_keluarga')->nullable();
            $table->string('tmptlahir_keluarga')->nullable();
            $table->date('tgllahir_keluarga')->nullable();
            $table->enum('kelamin_keluarga', ['L', 'P'])->nullable();
            $table->string('no_bpjskes')->nullable();
            $table->string('status_bpjskes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keluarga');
    }
};
