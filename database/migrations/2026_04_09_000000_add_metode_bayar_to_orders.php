<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetodeBayarToOrders extends Migration
{
    /**
     * Run the migrations.
     * Adds `metode_bayar` nullable string to `pesanan` and `penjualans` if they exist.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pesanan') && ! Schema::hasColumn('pesanan', 'metode_bayar')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->string('metode_bayar')->nullable()->after('metode_bayar')->change();
            });
            // Some MySQL versions/laravel setups don't like change() unless column exists — fallback:
            if (! Schema::hasColumn('pesanan', 'metode_bayar')) {
                Schema::table('pesanan', function (Blueprint $table) {
                    $table->string('metode_bayar')->nullable()->after('status_bayar');
                });
            }
        }

        if (Schema::hasTable('penjualans') && ! Schema::hasColumn('penjualans', 'metode_bayar')) {
            Schema::table('penjualans', function (Blueprint $table) {
                $table->string('metode_bayar')->nullable()->after('status_bayar');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('pesanan') && Schema::hasColumn('pesanan', 'metode_bayar')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropColumn('metode_bayar');
            });
        }
        if (Schema::hasTable('penjualans') && Schema::hasColumn('penjualans', 'metode_bayar')) {
            Schema::table('penjualans', function (Blueprint $table) {
                $table->dropColumn('metode_bayar');
            });
        }
    }
}
