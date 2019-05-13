<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'municipios';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    protected $primaryKey = 'cve_mun';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cve_ent', 'cve_mun', 'nom_mun',
		'cve_cab', 'nom_cab'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at','updated_at','cve_cab','nom_cab'];

    // Relación de categoria con subcategorias:
    public function subcategorias()
    {
        // 1 categoria puede tener varias subcategorias
        return $this->hasMany('App\Subcategoria', 'categoria_id');
    }
}
