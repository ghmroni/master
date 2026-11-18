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
        Schema::create('master_gaji', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kontrakinduk_id')->nullable();
            $table->unsignedBigInteger('project_id');
            $table->string('project_detail');
            $table->decimal('gapok', 15, 0)->default(0);
            $table->decimal('natura', 15, 0)->default(0);
            $table->decimal('uang_makan', 15, 0)->default(0);
            $table->decimal('uang_transport', 15, 0)->default(0);
            $table->string('cutoff');
            $table->enum('type_pothadir', ['STANDAR', 'FLAT', 'CUSTOM']);
            $table->string('perhitungan_pothadir')->nullable();
            $table->decimal('nilai_pothadir', 15, 0)->default(0);
            $table->string('hari_kerja')->default(0);
            $table->integer('jkk')->default(0);
            $table->integer('jkm')->default(0);
            $table->integer('jht')->default(0);
            $table->integer('jp')->default(0);
            $table->integer('jkn')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_gaji');
    }
};
