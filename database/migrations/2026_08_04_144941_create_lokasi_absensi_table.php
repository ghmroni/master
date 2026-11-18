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
        Schema::create('lokasi_absensi', function (Blueprint $table) {
            $table->id();
            $table->string('project_detail');
            $table->string('penempatan');
            $table->string('tag_location');
            $table->unsignedInteger('radius')->default(0);
            $table->string('nama_koordinat');
            $table->enum('status', ['A', 'T']);
            $table->text('url_map')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_absensi');
    }
};
