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
        Schema::create('web_layanan', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['TENTANG', 'TIM', 'NILAI', 'LAYANAN', 'LEGALITAS', 'PORTOFOLIO', 'VISIMISI']);
            $table->string('title', 100);
            $table->text('deskripsi_singkat');
            $table->text('deskripsi_lengkap')->nullable(true);
            $table->string('gambar_layanan');
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_layanan');
    }
};
