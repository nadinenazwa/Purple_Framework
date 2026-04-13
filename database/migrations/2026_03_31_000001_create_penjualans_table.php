<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->bigIncrements('id_penjualan');
            $table->timestamp('timestamp')->useCurrent();
            $table->integer('total');
        });
    }

    public function down()
    {
        Schema::dropIfExists('penjualans');
    }
};
