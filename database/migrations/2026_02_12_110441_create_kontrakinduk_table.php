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
        Schema::create('kontrakinduk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor');
            $table->string('judul');
            $table->date('tanggal_terbit');
            $table->string('jenis_kontrakinduk');
            $table->text('keterangan')->nullable();
            $table->date('start');
            $table->date('end');
            $table->enum('status', ['ENABLED', 'DISABLED']);
            $table->string('file')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrakinduk');
    }
};
