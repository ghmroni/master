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
        Schema::create('disc_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_number');
            $table->string('operator_d', 3);
            $table->string('operator_i', 3);
            $table->string('operator_s', 3);
            $table->string('operator_c', 3);
            $table->string('special_rule', 25)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_rules');
    }
};
