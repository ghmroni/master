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
        Schema::create('pendidikan_nonformal', function (Blueprint $table) {
            $table->bigIncrements('id_nonformal');
            $table->unsignedBigInteger('pelamar_id')->nullable(false);
            $table->string('lembaga_nonformal');
            $table->string('program_nonformal');
            $table->date('tglmulai_nonformal');
            $table->date('tglselesai_nonformal');
            $table->string('file_nonformal')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendidikan_nonformal');
    }
};
