<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //cargar el slider
        $slider = \App\Slider::
                orderBy('id', 'asc')
                ->get();

        if(count($slider) == 0){
            return response()->json(['error'=>'No existe el slider.'], 404);          
        }else{

            return response()->json(['slider'=>$slider[0]], 200);
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
        if($slider=\App\Slider::create($request->all())){
           return response()->json(['message'=>'Slider creado con éxito.',
             'slider'=>$slider], 200);
        }else{
            return response()->json(['error'=>'Error al crear el slider.'], 500);
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
        // Comprobamos si el slider que nos están pasando existe o no.
        $slider=\App\Slider::find($id);

        if (count($slider)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el slider con id '.$id], 404);
        }  

        $slider->fill($request->all());

        // Almacenamos en la base de datos el registro.
        if ($slider->save()) {
            return response()->json(['message'=>'Slider configurado con éxito.',
                'slider'=>$slider], 200);
        }else{
            return response()->json(['error'=>'Error al configurar el slider.'], 500);
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
}
