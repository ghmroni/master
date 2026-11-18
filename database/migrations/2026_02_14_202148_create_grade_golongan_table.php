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
        Schema::create('grade_golongan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('grade', 8)->nullable();
            $table->decimal('tunj_grade', 15, 0)->nullable()->default(0);
            $table->tinyInteger('golongan')->unsigned()->nullable()->default(0);
            $table->decimal('tunj_golongan', 15, 0)->nullable()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_golongan');
    }
};
