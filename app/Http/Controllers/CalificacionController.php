<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CalificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //cargar todas las calificaciones
        $calificaciones = \App\Calificacion::all();

        if(count($calificaciones) == 0){
            return response()->json(['error'=>'No existen calificaciones.'], 404);          
        }else{
            return response()->json(['calificaciones'=>$calificaciones], 200);
        } 
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
        if ( !$request->input('pedido_id') ||
             !$request->input('puntaje'))
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
            return response()->json(['error'=>'Faltan datos necesarios para el proceso de alta.'],422);
        } 
        
        $pedido = \App\Pedido::find($request->input('pedido_id'));
        if(count($pedido) == 0){
           // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'No existe el pedido que se quiere calificar.'], 409);
        }

        $aux = $pedido->calificacion;

        if (sizeof($aux) > 0 )
        {
            // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'Este pedido ya está calificado.'], 409);
        }

        //Calificar el pedido
        if($calificacion=\App\Calificacion::create($request->all())){
           return response()->json(['message'=>'Pedido calificado con éxito.',
             'categoria'=>$calificacion], 200);
        }else{
            return response()->json(['error'=>'Error al crear la calificación.'], 500);
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
        //cargar una calificacion
        $calificacion = \App\Calificacion::find($id);

        if(count($calificacion)==0){
            return response()->json(['error'=>'No existe la calificación con id '.$id], 404);          
        }else{
            return response()->json(['calificacion'=>$calificacion], 200);
        } 
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
        // Comprobamos si la calificacion que nos están pasando existe o no.
        $calificacion = \App\Calificacion::find($id);

        if(count($calificacion)==0){
            return response()->json(['error'=>'No existe la calificación con id '.$id], 404);          
        }

        // Listado de campos recibidos teóricamente.
        $puntaje=$request->input('puntaje');
        $comentario=$request->input('comentario');

        // Creamos una bandera para controlar si se ha modificado algún dato.
        $bandera = false;

        // Actualización parcial de campos.
        if ($puntaje != null && $puntaje!='')
        {
            $calificacion->puntaje = $puntaje;
            $bandera=true;
        }

        if ($comentario != null && $comentario!='')
        {
            $calificacion->comentario = $comentario;
            $bandera=true;
        }

        if ($bandera)
        {
            // Almacenamos en la base de datos el registro.
            if ($calificacion->save()) {
                return response()->json(['message'=>'Calificación editada con éxito.',
                    'calificacion'=>$calificacion], 200);
            }else{
                return response()->json(['error'=>'Error al actualizar la calificación.'], 500);
            }
            
        }
        else
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
            // Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
            return response()->json(['error'=>'No se ha modificado ningún dato a la la calificación.'],409);
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
        // Comprobamos si el pedido que nos están pasando existe o no.
        $calificacion=\App\Calificacion::find($id);

        if(count($calificacion)==0){
            return response()->json(['error'=>'No existe la calificación con id '.$id], 404);          
        }
        
        // Eliminamos la calificacion del pedido.
        $calificacion->delete();

        return response()->json(['message'=>'Se ha eliminado correctamente la calificación.'], 200);
    }
}
