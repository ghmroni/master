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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('pelamar_id');
            $table->unsignedBigInteger('pt_id');
            $table->unsignedBigInteger('project');
            $table->string('project_detail');
            $table->string('nip')->unique()->nullable();
            $table->string('kk');
            $table->string('ktp');
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat_ktp');
            $table->text('alamat_domisili')->nullable();
            $table->unsignedBigInteger('provinsi_id')->nullable()->default(null);
            $table->unsignedBigInteger('kabkot_id')->nullable()->default(null);
            $table->unsignedBigInteger('kecamatan_id')->nullable()->default(null);
            $table->unsignedBigInteger('kelurahan_id')->nullable()->default(null);
            $table->string('kodepos', 10)->nullable();
            $table->unsignedBigInteger('statuskawin_id');
            $table->enum('kelamin', ['L', 'P']);
            $table->string('agama', 20);
            $table->bigInteger('handphone')->unsigned();
            $table->unsignedBigInteger('ptko_id')->nullable();
            $table->unsignedBigInteger('jabatan')->nullable();
            $table->unsignedBigInteger('unitkerja')->nullable();
            $table->string('ukuranbaju', 5)->nullable();
            $table->unsignedTinyInteger('ukurancelana')->nullable();
            $table->unsignedTinyInteger('tinggibadan')->nullable();
            $table->unsignedTinyInteger('beratbadan')->nullable();
            $table->unsignedTinyInteger('nomorsepatu')->nullable();
            // PENDIDIKAN
            $table->string('pendidikan')->nullable();
            $table->string('namasekolah')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('tahunlulus')->nullable();
            $table->string('nilai')->nullable();
            // BANK
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('namabank')->nullable();
            $table->string('norek')->nullable();
            $table->boolean('percepatan')->default(false);
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->string('photo')->nullable();
            $table->string('password')->nullable();
            $table->text('fcm_id')->nullable();
            $table->string('ios_id')->nullable();
            $table->string('imei')->nullable();
            $table->text('token_apps')->nullable();
            $table->text('reset_token')->nullable();
            $table->datetime('register_apps')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
