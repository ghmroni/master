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
        Schema::create('disc_hasil', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pelamar_id');
            $table->string('noreg');
            $table->date('tglpanggil');
            $table->string('nama');
            $table->date('tgllahir');
            $table->string('kelamin');
            $table->string('usia')->nullable();
            $table->json('most')->nullable();
            $table->json('least')->nullable();
            $table->json('data_line')->nullable();
            $table->json('data_grafik')->nullable();
            $table->json('data_faktor')->nullable();
            $table->json('data_karakter')->nullable();
            $table->text('kepribadian')->nullable();
            $table->text('job_match')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_hasil');
    }
};
