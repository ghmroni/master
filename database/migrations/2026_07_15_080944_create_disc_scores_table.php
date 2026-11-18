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
        Schema::create('disc_scores', function (Blueprint $table) {
            $table->id();
            $table->string('line_type', 2);
            $table->integer('raw_score');
            $table->decimal('score_d', 4, 2);
            $table->decimal('score_i', 4, 2);
            $table->decimal('score_s', 4, 2);
            $table->decimal('score_c', 4, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_scores');
    }
};
