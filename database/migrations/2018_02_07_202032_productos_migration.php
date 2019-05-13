<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProductosMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->float('precio')->nullable();
            //$table->string('imagen')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado'); // ON/OFF
            $table->string('codigo'); //Codigo aleatorio unico

            $table->integer('subcategoria_id')->unsigned();
            $table->foreign('subcategoria_id')->references('id')->on('subcategorias');

            $table->integer('establecimiento_id')->unsigned();
            $table->foreign('establecimiento_id')->references('id')->on('establecimientos');
            
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
        Schema::drop('productos');
    }
}
