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
        Schema::create('flight_haji', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->date('tgl_arrival');
            $table->date('tgl_departure');
            $table->string('aircraft_type', 25);
            $table->string('aircraft_categori', 25);
            $table->string('aircraft_registrasi', 50);
            $table->string('arrival_flight', 50);
            $table->string('arrival_from', 50);
            $table->time('arrival_sta');
            $table->time('arrival_ata')->nullable();
            $table->string('departure_flight', 50);
            $table->string('departure_to', 50);
            $table->time('departure_std');
            $table->time('departure_atd')->nullable();
            $table->string('status', 25);
            $table->string('remarks', 25);
            $table->string('cabang', 50);
            $table->string('operator');
            $table->string('area');
            $table->string('sheet');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_haji');
    }
};
