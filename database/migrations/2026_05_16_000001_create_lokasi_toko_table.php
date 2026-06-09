<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi_toko', function (Blueprint $table) {
            $table->string('barcode', 8)->primary();
            $table->string('nama_toko', 50);
            $table->double('latitude');
            $table->double('longitude');
            $table->double('accuracy');
            $table->timestamps();
        });

        Schema::create('kunjungan_toko', function (Blueprint $table) {
            $table->id();
            $table->string('barcode_toko', 8);
            $table->string('nama_toko', 50)->nullable();
            $table->double('lat_toko');
            $table->double('lng_toko');
            $table->double('acc_toko');
            $table->double('lat_sales');
            $table->double('lng_sales');
            $table->double('acc_sales');
            $table->double('jarak_meter');
            $table->double('threshold_efektif');
            $table->string('status', 10);   // DITERIMA | DITOLAK
            $table->timestamps();

            $table->foreign('barcode_toko')
                  ->references('barcode')
                  ->on('lokasi_toko')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_toko');
        Schema::dropIfExists('lokasi_toko');
    }
};
