<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repartidor extends Model
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'repartidores';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    //protected $primaryKey = 'id';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['lat', 'lng', 'estado', 'activo',
        'ocupado', 'usuario_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at','updated_at'];

    // Relación de repartidor con usuario(datos personales):
    public function usuario()
    {
        // 1 repartidor pertenece a un usuario
        return $this->belongsTo('App\User', 'usuario_id');
    }

    // Relación de repartidor con pedidos:
    public function pedidos()
    {
        // 1 repartidor puede estar en varios pedidos
        return $this->hasMany('App\Pedido', 'repartidor_id');
    }
}
