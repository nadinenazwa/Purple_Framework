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
                if (! Schema::hasColumn('customers', 'name')) $table->string('name')->nullable();
                if (! Schema::hasColumn('customers', 'photo_blob')) $table->binary('photo_blob')->nullable();
                if (! Schema::hasColumn('customers', 'photo_path')) $table->string('photo_path')->nullable();
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
                if (Schema::hasColumn('customers', 'photo_path')) $table->dropColumn('photo_path');
                if (Schema::hasColumn('customers', 'photo_blob')) $table->dropColumn('photo_blob');
                if (Schema::hasColumn('customers', 'name')) $table->dropColumn('name');
            });
        }
    }
};
