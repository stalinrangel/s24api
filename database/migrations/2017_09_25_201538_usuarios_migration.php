<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsuariosMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('nombre');
            $table->string('ciudad');
            $table->string('estado'); //estado del pais
            $table->string('telefono');
            $table->string('imagen')->nullable();
            $table->integer('tipo_usuario'); //1=admin 2=cliente 3=repartidor 4=establecimiento
            $table->integer('tipo_registro'); //1=normal 2=facebook 3=twitter 4=instagram
            $table->string('id_facebook')->nullable();
            $table->string('id_twitter')->nullable();
            $table->string('id_instagram')->nullable();
            $table->string('codigo_verificacion')->nullable();
            $table->integer('validado'); //0=no validado 1=validado
            $table->text('token_notificacion')->nullable();
            $table->string('status'); // ON/OFF 
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
        Schema::drop('usuarios');
    }
}
