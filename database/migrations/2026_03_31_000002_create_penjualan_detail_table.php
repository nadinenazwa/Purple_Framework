<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penjualan_detail', function (Blueprint $table) {
            $table->bigIncrements('idpenjualan_detail');
            $table->unsignedBigInteger('id_penjualan');
            $table->string('id_barang');
            $table->integer('jumlah');
            $table->integer('subtotal');

            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualans')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('penjualan_detail');
    }
};
