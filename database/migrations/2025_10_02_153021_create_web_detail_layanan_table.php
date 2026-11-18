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
        // Schema::create('web_detail_layanan', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });

        Schema::create('web_detail_layanan', function (Blueprint $table) {
            $table->uuid('id')->nullable();
            $table->unsignedBigInteger('layanan_id');
            $table->string('nourut', 2);
            $table->string('nama');
            $table->string('jabatan')->nullable(true);
            $table->text('quote');
            $table->string('photo')->nullable(true);

            // $table->primary(['layanan_id']);

            // $table->foreign('layanan_id')->references('id')->on('web_layanan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_detail_layanan');
    }
};
