<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;
        /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    //protected $primaryKey = 'id';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'password', 'nombre',
         'ciudad', 'estado', 'telefono',
         'imagen', 'tipo_usuario', 'tipo_registro',
         'id_facebook', 'id_twitter', 'id_instagram',
         'codigo_verificacion', 'validado', 'token_notificacion', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    // Relación de usuario con pedidos:
    public function pedidos()
    {
        // 1 usuario puede tener varios pedidos
        return $this->hasMany('App\Pedido', 'usuario_id');
    }

    // Relación de usuario con repartidor:
    public function repartidor()
    {
        // 1 usuario puede tener(ser) un repartidor
        return $this->hasOne('App\Repartidor', 'usuario_id');
    }

    // Relación de usuario(Cliente) con chats_clientes:
    public function chat_cliente()
    {
        // 1 usuario cliente puede tener un chat
        return $this->hasOne('App\ChatCliente', 'usuario_id');
    }

    // Relación de usuario(Repartidor) con chats_repartidores:
    public function chat_repartidor()
    {
        // 1 usuario repartidor puede tener un chat
        return $this->hasOne('App\ChatRepartidor', 'usuario_id');
    }

    // Relación de usuario con establecimiento:
    public function establecimiento()
    {
        // 1 usuario puede tener(ser) un establecimiento
        return $this->hasOne('App\Establecimiento', 'usuario_id');
    }

}
