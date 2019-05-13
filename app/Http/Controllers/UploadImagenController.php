<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class UploadImagenController extends Controller
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
        if (!$request->hasFile('imagen')) {
            return response()->json(['error'=>'Img no detectada.'], 422);
        }else if(!$request->input('carpeta')){
            return response()->json(['error'=>'Especifique un directorio de destino.'], 422);
        }else if(!$request->input('url_imagen')){
            return response()->json(['error'=>'Especifique una URL base para la imagen.'], 422);
        } 

        $hoy = date("m.d.y.H.i.s");

        $destinationPath = public_path().'/images_uploads/'.$request->input('carpeta').'/';
        //$destinationPath = public_path().'/../../images_uploads/'.$request->input('carpeta').'/';
        $fileName = $hoy.'.png';
        $request->file('imagen')->move($destinationPath,$fileName);

        $imagen = $request->input('url_imagen').$request->input('carpeta').'/'.$fileName;

        return response()->json(['message'=>'Imagen cargada con éxito.',
             'imagen'=>$imagen], 200);
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
        //
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
}
