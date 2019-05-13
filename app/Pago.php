<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pagos';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    //protected $primaryKey = 'id';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['monto', 'establecimiento_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    //protected $hidden = ['created_at','updated_at'];

    // Relación de pagos con pedidos:
    public function pedidos(){
        // 1 pedido contiene muchos pedidos
        return $this->belongsToMany('\App\Pedido','pagos_pedidos','pago_id','pedido_id')
            /*->withPivot()->withTimestamps()*/; 
    }

    // Relación de pagos con establecimiento:
    public function establecimiento()
    {
        // 1 pago pertenece a un establecimiento
        return $this->belongsTo('App\Establecimiento', 'establecimiento_id');
    }

}
