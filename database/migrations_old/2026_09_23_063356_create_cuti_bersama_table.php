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
        Schema::create('cuti_bersama', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tahun', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuti_bersama');
    }
};
