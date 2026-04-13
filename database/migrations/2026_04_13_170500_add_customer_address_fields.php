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
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (! Schema::hasColumn('customers', 'alamat')) $table->string('alamat')->nullable();
                if (! Schema::hasColumn('customers', 'province_name')) $table->string('province_name')->nullable();
                if (! Schema::hasColumn('customers', 'regency_name')) $table->string('regency_name')->nullable();
                if (! Schema::hasColumn('customers', 'kodepos')) $table->string('kodepos')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'kodepos')) $table->dropColumn('kodepos');
                if (Schema::hasColumn('customers', 'regency_name')) $table->dropColumn('regency_name');
                if (Schema::hasColumn('customers', 'province_name')) $table->dropColumn('province_name');
                if (Schema::hasColumn('customers', 'alamat')) $table->dropColumn('alamat');
            });
        }
    }
};
