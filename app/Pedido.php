<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pedidos';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    //protected $primaryKey = 'id';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['estado', 'lat', 'lng',
        'direccion', 'distancia', 'tiempo',
        'gastos_envio', 'costo_envio', 'subtotal', 'costo', 'usuario_id',
        'repartidor_id', 'repartidor_nom', 'estado_pago', 'api_tipo_pago',
        'destinatario', 'telefono', 'referencia'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    //protected $hidden = ['created_at','updated_at'];

    // Relación de pedidos con usuarios (cliente):
	public function usuario()
	{
		// 1 pedido pertenece a un usuario
		return $this->belongsTo('App\User', 'usuario_id');
	}

    // Relación de pedidos con productos:
    public function productos(){
        // 1 pedido contiene muchos productos
        return $this->belongsToMany('\App\Producto','pedido_productos','pedido_id','producto_id')
            ->withPivot('estado_deuda','cantidad','precio_unitario','observacion')/*->withTimestamps()*/; 
    }

    /*// Relación de pedidos con establecimiento:
    public function establecimiento()
    {
        // 1 pedido pertenece a un establecimiento
        return $this->belongsTo('App\Establecimiento', 'establecimiento_id');
    }*/

    // Relación de pedidos con repartidor:
    public function repartidor()
    {
        // 1 pedido pertenece a un repartidor
        return $this->belongsTo('App\Repartidor', 'repartidor_id');
    }

    // Relación de pedidos con calificaciones:
    public function calificacion()
    {
        // 1 pedido tiene una calificacion
        return $this->hasOne('App\Calificacion', 'pedido_id');
    }

    // Relación de pedidos con ruta:
    public function ruta()
    {
        // 1 pedido tiene una ruta (compuesta por varios puntos)
        return $this->hasMany('App\Ruta', 'pedido_id');
    }
}
