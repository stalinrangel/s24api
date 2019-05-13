<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MsgsChatsClientesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('msgs_chats_clientes', function (Blueprint $table) {
            $table->increments('id');

            $table->text('msg')->nullable();
            $table->integer('estado')->default(1); //1=sin leer 2=leido

            $table->integer('chat_id')->unsigned();
            $table->foreign('chat_id')->references('id')->on('chats_clientes');

            $table->integer('emisor_id')->unsigned();
            $table->foreign('emisor_id')->references('id')->on('usuarios');

            $table->integer('receptor_id')->unsigned();
            $table->foreign('receptor_id')->references('id')->on('usuarios');

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
        Schema::drop('msgs_chats_clientes');
    }
}
