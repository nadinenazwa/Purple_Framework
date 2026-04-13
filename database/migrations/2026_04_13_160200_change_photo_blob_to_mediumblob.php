<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'photo_blob')) {
            // Change to MEDIUMBLOB to support larger PNG/JPEG images
            DB::statement('ALTER TABLE `customers` MODIFY `photo_blob` MEDIUMBLOB NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'photo_blob')) {
            // revert back to BLOB (max ~64KB)
            DB::statement('ALTER TABLE `customers` MODIFY `photo_blob` BLOB NULL');
        }
    }
};
