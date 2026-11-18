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
        Schema::create('web_kontak', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('kategori', ['PUSAT', 'PERWAKILAN']);
            $table->string('email');
            $table->string('handphone')->nullable();
            $table->string('tlp')->nullable();
            $table->string('fax')->nullable();
            $table->text('alamat');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_kontak');
    }
};
