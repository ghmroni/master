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
        Schema::create('kontrakinduk_biaya', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setting_harga_id');
            $table->unsignedBigInteger('unitkerja_id');
            $table->string('jenis_biaya');
            $table->decimal('harga', 15, 0);
            $table->integer('sort_order');
            $table->timestamps();
            $table->softDeletes();

            // Relasi
            $table->foreign('setting_harga_id')
                ->references('id')
                ->on('kontrakinduk_setting_harga')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrakinduk_biaya');
    }
};
