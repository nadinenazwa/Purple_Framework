<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            if (! Schema::hasColumn('penjualans', 'order_id')) {
                $table->string('order_id')->nullable()->after('total');
            }
            if (! Schema::hasColumn('penjualans', 'status_bayar')) {
                $table->string('status_bayar')->default('Belum')->after('order_id');
            }
            if (! Schema::hasColumn('penjualans', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('status_bayar');
            }
        });
    }

    public function down()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('penjualans', 'order_id')) $table->dropColumn('order_id');
            if (Schema::hasColumn('penjualans', 'status_bayar')) $table->dropColumn('status_bayar');
            if (Schema::hasColumn('penjualans', 'vendor_id')) $table->dropColumn('vendor_id');
        });
    }
};
