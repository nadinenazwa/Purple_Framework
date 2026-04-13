<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('barangs')) {
            Schema::create('barangs', function (Blueprint $table) {
                $table->id();
                $table->string('id_barang')->unique()->nullable();
                $table->string('nama');
                $table->string('kategori')->nullable();
                $table->decimal('harga', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // Trigger to auto-generate id_barang like BRG0001, BRG0002, ...
        DB::unprepared('DROP TRIGGER IF EXISTS trg_barangs_id');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_barangs_id BEFORE INSERT ON barangs FOR EACH ROW
BEGIN
    DECLARE maxnum INT DEFAULT 0;
    IF NEW.id_barang IS NULL OR NEW.id_barang = '' THEN
        SELECT IFNULL(MAX(CAST(SUBSTRING(id_barang,4) AS UNSIGNED)),0) INTO maxnum FROM barangs;
        SET NEW.id_barang = CONCAT('BRG', LPAD(maxnum + 1,4,'0'));
    END IF;
END;
SQL
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_barangs_id');
        Schema::dropIfExists('barangs');
    }
};
