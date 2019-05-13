<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'entidades';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    protected $primaryKey = 'cve_ent';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cve_ent', 'nom_ent', 'nom_abr', 'id_pais'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at','updated_at','nom_abr','id_pais'];

    // Relación de entidad con municipios:
    public function municipios()
    {
        return $this->hasMany('App\Municipio', 'cve_ent');
    }
}
