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
        Schema::create('pelamar', function (Blueprint $table) {
            $table->bigIncrements('id_pelamar');
            $table->string('nama', 100);
            $table->string('noktp', 16)->nullable();
            $table->string('tmptlahir')->nullable();
            $table->date('tgllahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('handphone', 14);
            $table->enum('agama', [
                'ISLAM',
                'KRISTEN',
                'PROTESTAN',
                'KATOLIK',
                'HINDU',
                'BUDDHA',
                'KONGHUCU'
            ])->nullable();

            $table->unsignedBigInteger('status_kawin')->nullable();
            $table->unsignedTinyInteger('nomor_sepatu')->nullable();
            $table->unsignedTinyInteger('berat_badan')->nullable();
            $table->unsignedTinyInteger('tinggi_badan')->nullable();
            $table->string('ukuran_baju', 5)->nullable();
            $table->unsignedTinyInteger('nomor_celana')->nullable();

            // KTP address foreign keys
            $table->text('alamat_ktp')->nullable();
            $table->unsignedBigInteger('provinsi_ktp')->nullable()->default(null);
            $table->unsignedBigInteger('kabkot_ktp')->nullable()->default(null);
            $table->unsignedBigInteger('kecamatan_ktp')->nullable()->default(null);
            $table->unsignedBigInteger('kelurahan_ktp')->nullable()->default(null);
            $table->string('kodepos_ktp', 10)->nullable();

            // Domisili address foreign keys
            $table->text('alamat_domisili')->nullable();
            $table->unsignedBigInteger('provinsi_domisili')->nullable()->default(null);
            $table->unsignedBigInteger('kabkot_domisili')->nullable()->default(null);
            $table->unsignedBigInteger('kecamatan_domisili')->nullable()->default(null);
            $table->unsignedBigInteger('kelurahan_domisili')->nullable()->default(null);
            $table->string('kodepos_domisili', 10)->nullable();

            // Pendidikan Formal
            $table->string('jenjang_pendidikan')->nullable();
            $table->string('nama_sekolah')->nullable();
            $table->string('jurusan_sekolah')->nullable();
            $table->string('tahun_lulus', 4)->nullable();
            $table->string('nilai_ijazah', 5)->nullable();

            $table->enum('status', ['WAITING', 'PROGRESS', 'COMPLETE']);
            $table->enum('register', ['registered', 'not registered']);
            $table->dateTime('registered_at')->nullable();
            $table->text('token_registrasi')->nullable();
            $table->unsignedInteger('otp_code')->nullable();
            $table->dateTime('otp_expired')->nullable();
            // FILE
            $table->string('photo_profile')->nullable();
            $table->string('file_cv')->nullable();
            $table->string('file_ijazah')->nullable();
            $table->string('file_kk')->nullable();
            $table->string('file_ktp')->nullable();
            $table->string('file_bank')->nullable();
            $table->enum('referensi', ['UMUM', 'AVIASI']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelamar');
    }
};
