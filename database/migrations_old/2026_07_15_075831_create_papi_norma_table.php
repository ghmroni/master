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
        Schema::create('papi_norma', function (Blueprint $table) {
            $table->id();
            $table->string('bidang');
            $table->string('aspek');
            $table->string('factor_code', 2);
            $table->string('skor_min', 2);
            $table->string('skor_max', 2);
            $table->string('kategori', 50);
            $table->text('analisis');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papi_norma');
    }
};
