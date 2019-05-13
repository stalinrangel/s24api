<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subcategorias';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    //protected $primaryKey = 'id';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'imagen', 'estado', 'categoria_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at','updated_at'];

    // Relación de subcategoria con categoria:
    public function categoria()
    {
        // 1 subcat pertenece a una categoria
        return $this->belongsTo('App\Categoria', 'categoria_id');
    }

     // Relación de subcategoria con productos:
    public function productos()
    {
        // 1 categoria puede tener varios productos
        return $this->hasMany('App\Producto', 'subcategoria_id');
    }
}
