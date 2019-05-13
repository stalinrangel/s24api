<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatRepartidor extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chats_repartidores';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    //protected $primaryKey = 'id';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['admin_id', 'usuario_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    // Relación de chats_repartidores con usuarios (admin):
	public function admin()
	{
		// 1 msg pertenece a un usuario (admin)
		return $this->belongsTo('App\User', 'admin_id');
	}

	// Relación de chats_repartidores con usuarios (repartidor):
	public function usuario()
	{
		// 1 msg pertenece a un usuario (repartidor)
		return $this->belongsTo('App\User', 'usuario_id');
	}

	// Relación de chats_repartidores con msgs_chats_repartidores:
    public function mensajes()
    {
        // 1 chats_repartidores puede tener varios mensajes
        return $this->hasMany('App\MsgChatRepartidor', 'chat_id');
    }
}
