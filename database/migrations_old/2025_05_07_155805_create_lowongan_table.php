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
        Schema::create('lowongan', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('pekerjaan_dilamar');
            $table->string('penempatan');
            $table->text('kualifikasi');
            $table->text('persyaratan');
            $table->text('deskripsi');
            $table->string('pengalaman');
            $table->date('start');
            $table->date('end');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('permintaan_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->enum('prioritas', ['NORMAL', 'SEGERA']);
            $table->text('slug')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lowongan');
    }
};
