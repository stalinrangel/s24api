<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class VarSistemaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //cargar todas las varSistema
        $varSistema = \App\VarSistema::
                orderBy('id', 'asc')
                ->get();

        if(count($varSistema) == 0){
            return response()->json(['error'=>'No existen variables del sistema.'], 404);          
        }else{

            return response()->json(['varSistema'=>$varSistema[0]], 200);
        }
    }

    public function ubicacion()
    {
        //cargar la ubicacion publica y privada
        $ubicacion = \App\VarSistema::all();

        return response()->json(['ubicacion'=>$ubicacion], 200);
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
        /*// Primero comprobaremos si estamos recibiendo todos los campos.
        if ( !$request->input('costoxkm') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro costoxkm.'],422);
        }

        if ( !$request->input('gastos_envio') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro gastos_envio.'],422);
        }*/

        if($varSistema=\App\VarSistema::create($request->all())){
           return response()->json(['message'=>'Sistema configurado con éxito.',
             'varSistema'=>$varSistema], 200);
        }else{
            return response()->json(['error'=>'Error al configurar el sistema.'], 500);
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
        // Comprobamos si la varSistema que nos están pasando existe o no.
        $varSistema=\App\VarSistema::find($id);

        if (count($varSistema)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe la varSistema con id '.$id], 404);
        }  

        $varSistema->fill($request->all());

        // Almacenamos en la base de datos el registro.
        if ($varSistema->save()) {
            return response()->json(['message'=>'Sistema configurado con éxito.',
                'varSistema'=>$varSistema], 200);
        }else{
            return response()->json(['error'=>'Error al configurar el sistema.'], 500);
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
        //
    }

    public function getContacto()
    {
        $dataArray = array("direccion" => "Boulevard IMAQ número exterior: 323 Fracc villas de IMAQ",
            "correo" => "soporte@mouvers.mx",
            "telefono" => "+52 1 899 124 9038",);

        //$contacto = json_encode($dataArray);

        return response()->json(['contacto'=>$dataArray], 200);
    }
}
