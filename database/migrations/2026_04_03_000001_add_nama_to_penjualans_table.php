<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('nama', 50)->nullable()->after('timestamp');
        });
    }

    public function down()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn('nama');
        });
    }
};
