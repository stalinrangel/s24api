<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MsgBlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Primero comprobaremos si estamos recibiendo todos los campos.
        if ( !$request->input('blog_id') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro blog_id.'],422);
        }
        if ( !$request->input('usuario_id') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro usuario_id.'],422);
        }

        // Comprobamos si el blog existe o no.
        $blog=\App\Blog::find($request->input('blog_id'));

        if (count($blog)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el blog con id '.$id], 404);
        }

        if($msg=\App\MsgBlog::create($request->all())){
           return response()->json(['message'=>'Mensaje creado con éxito.',
             'mensaje'=>$msg], 200);
        }else{
            return response()->json(['error'=>'Error al crear el mensaje.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Comprobamos si el mensaje que nos están pasando existe o no.
        $mensaje=\App\MsgBlog::find($id);

        if (count($mensaje)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el mensaje con id '.$id], 404);
        }      

        // Listado de campos recibidos teóricamente.
        $msg=$request->input('msg');

        // Creamos una bandera para controlar si se ha modificado algún dato.
        $bandera = false;

        // Actualización parcial de campos.
        if ($msg != null && $msg!='')
        {
            $mensaje->msg = $msg;
            $bandera=true;
        }

        if ($bandera)
        {
            // Almacenamos en la base de datos el registro.
            if ($mensaje->save()) {
                return response()->json(['message'=>'Mensaje editado con éxito.',
                    'mensaje'=>$mensaje], 200);
            }else{
                return response()->json(['error'=>'Error al actualizar el mensaje.'], 500);
            }
            
        }
        else
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
            // Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
            return response()->json(['error'=>'No se ha modificado ningún dato al mensaje.'],409);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Comprobamos si el msg existe o no.
        $msg=\App\MsgBlog::find($id);

        if (count($msg)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el mensaje con id '.$id], 404);
        }

        // Eliminamos la msg si no tiene relaciones.
        $msg->delete();

        return response()->json(['message'=>'Se ha eliminado correctamente el mensaje.'], 200);
    }
}
