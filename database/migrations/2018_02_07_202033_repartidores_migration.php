<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RepartidoresMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repartidores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('estado'); // ON/OFF
            $table->integer('activo'); //1=SI (Trabajando) 2=NO (Descanso) 
            $table->integer('ocupado'); //1=SI (En un pedido) 2=NO (Disponible) 

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

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
        Schema::drop('repartidores');
    }
}
