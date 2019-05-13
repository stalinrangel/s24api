<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EstablecimientosMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre')->unique();
            $table->string('direccion');
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('estado'); // ON/OFF
            $table->integer('num_pedidos'); //Num de pedidos q ha recibido el establecimiento a lo largo del tiempo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('establecimientos');
    }
}
