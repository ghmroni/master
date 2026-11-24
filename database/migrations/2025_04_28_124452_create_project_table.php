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
        Schema::create('project', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('inisial')->nullable();
            $table->enum('status', ['ENABLE', 'DISABLE']);
            $table->string('logo')->nullable();
            $table->boolean('sisa_cuti')->default(false);
            $table->date('batas_sisa_cuti')->nullable();
            $table->integer('max_sisa_cuti')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project');
    }
};
