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
        Schema::create('status_perkawinan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status', 3);
            $table->string('nama_status');
            $table->float('nilai_ptkp')->default(0);
            $table->string('kategori_ptkp', 1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_perkawinan');
    }
};
