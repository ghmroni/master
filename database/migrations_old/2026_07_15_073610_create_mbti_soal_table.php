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
        Schema::create('mbti_soal', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_soal');
            $table->string('soal');
            $table->string('pilihan_a');
            $table->string('pilihan_b');
            $table->string('target_a', 5);
            $table->string('target_b', 5);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbti_soal');
    }
};
