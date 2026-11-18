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
        Schema::create('cfit_master', function (Blueprint $table) {
            $table->id();
            $table->string('sub_test');
            $table->string('nomor_soal');
            $table->string('kunci_jawaban');
            $table->string('nilai_benar');
            $table->string('nilai_salah');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfit_master');
    }
};
