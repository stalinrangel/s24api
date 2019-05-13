<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MsgBlog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'msgs_blogs';

    // Eloquent asume que cada tabla tiene una clave primaria con una columna llamada id.
    // Si éste no fuera el caso entonces hay que indicar cuál es nuestra clave primaria en la tabla:
    //protected $primaryKey = 'id';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['msg', 'blog_id', 'usuario_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    // Relación de msgs_blogs con usuarios (emisor):
	public function usuario()
	{
		// 1 msg pertenece a un usuario (emisor) de un msg
		return $this->belongsTo('App\User', 'usuario_id');
	}

	// Relación de msgs_blogs con blogs:
	public function blog()
	{
		// 1 msg pertenece a un blog
		return $this->belongsTo('App\Blog', 'blog_id');
	}
}
