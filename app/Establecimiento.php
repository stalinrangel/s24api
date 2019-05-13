<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'establecimientos';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    //protected $primaryKey = 'id';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'direccion', 'lat', 'lng', 'estado', 'num_pedidos', 'usuario_id','horarios','lunes_i','lunes_f','martes_i','martes_f','miercoles_i','miercoles_f','jueves_i','jueves_f','viernes_i','viernes_f','sabado_i','sabado_f','domingo_i','domingo_f'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at','updated_at'];

    // Relación de establecimiento con productos:
    public function productos()
    {
        // 1 establecimiento puede tener varios productos
        return $this->hasMany('App\Producto', 'establecimiento_id');
    }

     // Relación de establecimiento con pedidos:
    public function pedidos()
    {
        // 1 establecimiento puede tener varios pedidos
        return $this->hasMany('App\Pedido', 'establecimiento_id');
    }

    // Relación de establecimiento con usuario(datos de acceso):
    public function usuario()
    {
        // 1 establecimiento pertenece a un usuario
        return $this->belongsTo('App\User', 'usuario_id');
    }
}
