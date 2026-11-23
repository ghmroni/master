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
        Schema::create('disc_definisi', function (Blueprint $table) {
            $table->id();
            $table->string('profile_number', 11);
            $table->string('profile_code', 50);
            $table->string('profile_title');
            $table->json('karakter');
            $table->text('kepribadian');
            $table->text('pekerjaan');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_definisi');
    }
};
