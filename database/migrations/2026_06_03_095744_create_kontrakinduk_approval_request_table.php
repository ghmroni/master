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
        Schema::create('kontrakinduk_approval_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kontrakinduk_id');
            $table->unsignedBigInteger('adendum_id')->nullable();
            $table->unsignedBigInteger('setting_id')->nullable();
            $table->string('aktivitas');
            $table->json('data_lama')->nullable();
            $table->json('payload_request')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED']);
            $table->text('keterangan')->nullable();
            $table->text('keterangan_reject')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrakinduk_approval_request');
    }
};
