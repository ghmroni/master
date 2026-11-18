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
        Schema::create('master_lembur', function (Blueprint $table) {
            $table->id();
            $table->string('project_detail');
            $table->string('cutoff');
            $table->string('hari_kerja');
            $table->decimal('um', 15, 0)->default(0);
            $table->decimal('uml', 15, 0)->default(0);
            $table->decimal('ut', 15, 0)->default(0);
            $table->decimal('utl', 15, 0)->default(0);
            $table->string('type_lembur');
            $table->decimal('nilai_biasa', 15, 0)->default(0);
            $table->decimal('nilai_libur', 15, 0)->default(0);
            $table->enum('nilai_type', ['JAM', 'HARI'])->nullable();
            $table->decimal('b1')->default(0);
            $table->decimal('b2')->default(0);
            $table->decimal('nb1')->default(0);
            $table->decimal('nb2')->default(0);
            $table->decimal('l2')->default(0);
            $table->decimal('l3')->default(0);
            $table->decimal('l4')->default(0);
            $table->decimal('nl2')->default(0);
            $table->decimal('nl3')->default(0);
            $table->decimal('nl4')->default(0);
            $table->decimal('nln2')->default(0);
            $table->decimal('nln3')->default(0);
            $table->decimal('nln4')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_lembur');
    }
};
