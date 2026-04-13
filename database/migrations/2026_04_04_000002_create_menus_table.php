<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('menus')) {
            Schema::create('menus', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('price')->default(0);
                $table->timestamps();
                $table->index('user_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('menus');
    }
};
