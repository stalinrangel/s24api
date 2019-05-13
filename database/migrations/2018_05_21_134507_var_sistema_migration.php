<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VarSistemaMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('var_sistema', function (Blueprint $table) {
            $table->increments('id');
            $table->float('costoxkm')->nullable();
            $table->float('gastos_envio')->nullable(); //costo del traslado del repartidor al primer establecimineto

            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('var_sistema');
    }
}
