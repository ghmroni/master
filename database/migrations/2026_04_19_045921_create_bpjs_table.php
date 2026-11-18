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
        Schema::create('bpjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id')->unique();
            $table->decimal('gapok_bpjstk', 15, 0)->default(0);
            $table->decimal('gapok_bpjskes', 15, 0)->default(0);
            $table->integer('jkk')->default(0);
            $table->integer('jkm')->default(0);
            $table->integer('jht')->default(0);
            $table->integer('jp')->default(0);
            $table->integer('jkn')->default(0);
            $table->string('nobpjs_tk')->nullable();
            $table->string('nobpjs_kes')->nullable();
            $table->string('status_kepesertaan')->nullable();
            $table->enum('status_bpjs', ['ENABLE', 'DISABLE']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpjs');
    }
};
