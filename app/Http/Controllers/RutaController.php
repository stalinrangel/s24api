<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class RutaController extends Controller
{

    /*Actualiza el pedido a despachado(2) en la tabla rutas*/
    public function despacharPedido(Request $request)
    {
        // Comprobamos si el pedido que nos están pasando existe o no.
        $rutaEst = \App\Ruta::
            where('pedido_id',$request->input('pedido_id'))
            ->where('establecimiento_id',$request->input('establecimiento_id'))
            ->get();

        if(count($rutaEst)==0){
            return response()->json(['error'=>'Error al recuperar el pedido M00'.$request->input('pedido_id')], 404);          
        } 

        DB::table('rutas')
                ->where('pedido_id',$request->input('pedido_id'))
                ->where('establecimiento_id',$request->input('establecimiento_id'))
                ->update(['despachado' => 2]);

        return response()->json(['message'=>'Pedido actualizado con éxito.'], 200);
    }

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
        //
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
