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
        Schema::create('kontrakinduk_setting_harga', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kontrakinduk_id');
            $table->unsignedBigInteger('project_id');
            $table->string('project_detail');
            $table->date('tanggal_mulai_berlaku');
            $table->date('tanggal_akhir_berlaku');
            $table->timestamps();
            $table->softDeletes();

            // Relasi
            $table->foreign('kontrakinduk_id')
                ->references('id')
                ->on('kontrakinduk')
                ->onDelete('cascade');

            // Opsional jika Anda memiliki tabel master projects
            // $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrakinduk_setting_harga');
    }
};
