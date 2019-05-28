<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Hash;
use DB;

class EstablecimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //cargar todos los establecimientos
        $establecimientos = \App\Establecimiento::
            with(['usuario' => function ($query){
                    $query->select('id', 'email', 'nombre', 'ciudad', 'estado', 'telefono', 'imagen', 'tipo_usuario', 'token_notificacion')
                    ->where('tipo_usuario', 4);
                }])
                ->get();

        if(count($establecimientos) == 0){
            return response()->json(['error'=>'No existen establecimientos.'], 404);          
        }else{
            return response()->json(['establecimientos'=>$establecimientos], 200);
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
        if ( !$request->input('nombre') ||
             !$request->input('direccion') ||
            !$request->input('email') || !$request->input('password') ||
            !$request->input('telefono') ||
            !$request->input('ciudad') || !$request->input('estado_geo'))
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
           // return response()->json(['error'=>'Faltan datos necesarios para el proceso de alta.'],422);
        } 
        
        $aux = \App\Establecimiento::where('nombre', $request->input('nombre'))->get();
        if(count($aux)!=0){
           // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'Ya existe un establecimiento con ese nombre.'], 409);
        }

        $aux2 = \App\User::where('email', $request->input('email'))->get();
        if(count($aux2)!=0){
            return response()->json(['error'=>'Ya existe un usuario con esas credenciales.'], 409);    
        }

        /*Primero creo una instancia en la tabla usuarios*/
        $usuario = new \App\User;
        $usuario->email = $request->input('email');
        $usuario->password = Hash::make($request->input('password'));
        $usuario->nombre = $request->input('nombre');
        $usuario->ciudad = $request->input('ciudad');
        $usuario->estado = $request->input('estado_geo');
        $usuario->telefono = $request->input('telefono');
        $usuario->imagen = $request->input('imagen');
        $usuario->tipo_usuario = 4;
        $usuario->tipo_registro = 1;
        $usuario->id_facebook = $request->input('id_facebook');
        $usuario->id_twitter = $request->input('id_twitter');
       // $usuario->id_instagram = $request->input('id_instagram');
        $usuario->validado = 1;
        $usuario->status = 'ON';

        if($usuario->save()){

             /*Segundo creo una instancia en la tabla repartidores*/
            $repartidor = new \App\Repartidor;
            $repartidor->estado = 'ON';
            $repartidor->activo = 2;
            $repartidor->ocupado = 2;
            $repartidor->usuario_id = $usuario->id; 
            $repartidor->save();

            /*Segundo creo una instancia en la tabla repartidores*/
            $nuevoEstablecimiento = new \App\Establecimiento;
            $nuevoEstablecimiento->nombre = $request->input('nombre');
            $nuevoEstablecimiento->direccion = $request->input('direccion');
            $nuevoEstablecimiento->lat = $request->input('lat');
            $nuevoEstablecimiento->lng = $request->input('lng');
            $nuevoEstablecimiento->estado = 'ON';
            $nuevoEstablecimiento->num_pedidos = 0;
            $nuevoEstablecimiento->usuario_id = $usuario->id; 
            $nuevoEstablecimiento->lunes_i = $request->input('lunes_i');
            $nuevoEstablecimiento->lunes_f = $request->input('lunes_f');
            $nuevoEstablecimiento->martes_i = $request->input('martes_i');
            $nuevoEstablecimiento->martes_f = $request->input('martes_f');
            $nuevoEstablecimiento->miercoles_i = $request->input('miercoles_i');
            $nuevoEstablecimiento->miercoles_f = $request->input('miercoles_f');
            $nuevoEstablecimiento->jueves_i = $request->input('jueves_i');
            $nuevoEstablecimiento->jueves_f = $request->input('jueves_f');
            $nuevoEstablecimiento->viernes_i = $request->input('viernes_i');
            $nuevoEstablecimiento->viernes_f = $request->input('viernes_f');
            $nuevoEstablecimiento->sabado_i = $request->input('sabado_i');
            $nuevoEstablecimiento->sabado_f = $request->input('sabado_f');
            $nuevoEstablecimiento->domingo_i = $request->input('domingo_i');
            $nuevoEstablecimiento->domingo_f = $request->input('domingo_f');
            $nuevoEstablecimiento->save();

            $nuevoEstablecimiento->usuario = $usuario;

           return response()->json(['message'=>'Establecimiento creado con éxito.',
             'establecimiento'=>$nuevoEstablecimiento], 200);
        }else{
            return response()->json(['error'=>'Error al crear el establecimiento.'], 500);
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
        //cargar un establecimiento
        $establecimiento = \App\Establecimiento::with('usuario')->find($id);

        if(count($establecimiento)==0){
            return response()->json(['error'=>'No existe el establecimiento con id '.$id], 404);          
        }else{
            return response()->json(['establecimiento'=>$establecimiento], 200);
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
        // Comprobamos si el establecimiento que nos están pasando existe o no.
        $establecimiento=\App\Establecimiento::find($id);
        $usuario = \App\User::find($establecimiento->usuario_id);

        if (count($establecimiento)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el establecimiento con id '.$id], 404);
        }   

        if (count($usuario)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el usuario con id '.$establecimiento->usuario_id], 404);
        }   

        // Listado de campos recibidos teóricamente.
        $email=$request->input('email'); 
        $password=$request->input('password');  
        //$nombre=$request->input('nombre');
        $ciudad = $request->input('ciudad');
        $estado_geo = $request->input('estado_geo');
        $telefono = $request->input('telefono');
        $imagen=$request->input('imagen');
        //$tipo_usuario=$request->input('tipo_usuario');
        //$tipo_registro=$request->input('tipo_registro');
        //$codigo_verificacion=$request->input('codigo_verificacion');
        //$validado=$request->input('validado');
        $nombre=$request->input('nombre');
        $direccion=$request->input('direccion');
        $lat=$request->input('lat');
        $lng=$request->input('lng');
        $num_pedidos=$request->input('num_pedidos');
        $estado=$request->input('estado');
        $productos=$request->input('productos');
        $lunes_i = $request->input('lunes_i');
        $lunes_f = $request->input('lunes_f');
        $martes_i = $request->input('martes_i');
        $martes_f = $request->input('martes_f');
        $miercoles_i = $request->input('miercoles_i');
        $miercoles_f = $request->input('miercoles_f');
        $jueves_i = $request->input('jueves_i');
        $jueves_f = $request->input('jueves_f');
        $viernes_i = $request->input('viernes_i');
        $viernes_f = $request->input('viernes_f');
        $sabado_i = $request->input('sabado_i');
        $sabado_f = $request->input('sabado_f');
        $domingo_i = $request->input('domingo_i');
        $domingo_f = $request->input('domingo_f');

        // Creamos una bandera para controlar si se ha modificado algún dato.
        $bandera = false;

        // Actualización parcial de campos de usuario.
        if ($email != null && $email!='')
        {
            $aux = \App\User::where('email', $request->input('email'))
            ->where('id', '<>', $usuario->id)->get();

            if(count($aux)!=0){
               // Devolvemos un código 409 Conflict. 
                return response()->json(['error'=>'Ya existe otro usuario con ese email.'], 409);
            }

            $usuario->email = $email;
            $bandera=true;
        }

        if ($password != null && $password!='')
        {
            $usuario->password = Hash::make($request->input('password'));
            $bandera=true;
        }

        if ($ciudad != null && $ciudad!='')
        {
            $usuario->ciudad = $ciudad;
            $bandera=true;
        }

        if ($estado_geo != null && $estado_geo!='')
        {
            $usuario->estado = $estado_geo;
            $bandera=true;
        }

        if ($telefono != null && $telefono!='')
        {
            $usuario->telefono = $telefono;
            $bandera=true;
        }

        if ($imagen != null && $imagen!='')
        {
            $usuario->imagen = $imagen;
            $bandera=true;
        }

        // Actualización parcial de campos de establecimiento.
        if ($nombre != null && $nombre!='')
        {
            $aux = \App\Establecimiento::where('nombre', $request->input('nombre'))
            ->where('id', '<>', $establecimiento->id)->get();

            if(count($aux)!=0){
               // Devolvemos un código 409 Conflict. 
                return response()->json(['error'=>'Ya existe otro establecimiento con ese nombre.'], 409);
            }

            $usuario->nombre = $nombre;
            $establecimiento->nombre = $nombre;
            $bandera=true;
        }

        if ($direccion != null && $direccion!='')
        {
            $establecimiento->direccion = $direccion;
            $bandera=true;
        }

        if ($lat != null && $lat!='')
        {
            $establecimiento->lat = $lat;
            $bandera=true;
        }

        if ($lng != null && $lng!='')
        {
            $establecimiento->lng = $lng;
            $bandera=true;
        }

        if ($num_pedidos != null && $num_pedidos!='')
        {
            $establecimiento->num_pedidos = $num_pedidos;
            $bandera=true;
        }

        if ($estado != null && $estado!='')
        {
            if ($estado == 'OFF') {
                $productos = $establecimiento->productos;

                if (sizeof($productos) > 0)
                {
                    for ($i=0; $i < count($productos) ; $i++) { 
                        $productos[$i]->estado = $estado;
                        $productos[$i]->save();
                    }
                }
            }

            $establecimiento->estado = $estado;
            $bandera=true;
        }

        //Cambia el estado de los productos de un establecimiento
        if (sizeof($productos) > 0 )
        {
            $bandera=true;

            $productos = json_decode($productos);
            for ($i=0; $i < count($productos) ; $i++) {

                if ($productos[$i]->estado == 'ON') {

                    $prod = \App\Producto::find($productos[$i]->id);

                    if(count($prod) == 0){
                       // Devolvemos un código 409 Conflict. 
                        return response()->json(['error'=>'No existe el producto con id '.$productos[$i]->id], 409);
                    }else{
                        $prod->estado = $productos[$i]->estado;
                        $prod->save();
                    }
                }  
            }
        }

        if ($lunes_i != null && $lunes_i!='')
        {
            $establecimiento->lunes_i = $lunes_i;
            $bandera=true;
        }
        if ($lunes_f != null && $lunes_f!='')
        {
            $establecimiento->lunes_f = $lunes_f;
            $bandera=true;
        }
        if ($martes_i != null && $martes_i!='')
        {
            $establecimiento->martes_i = $martes_i;
            $bandera=true;
        }
        if ($martes_f != null && $martes_f!='')
        {
            $establecimiento->martes_f = $martes_f;
            $bandera=true;
        }
        if ($miercoles_i != null && $miercoles_i!='')
        {
            $establecimiento->miercoles_i = $miercoles_i;
            $bandera=true;
        }
        if ($miercoles_f != null && $miercoles_f!='')
        {
            $establecimiento->miercoles_f = $miercoles_f;
            $bandera=true;
        }

        if ($jueves_i != null && $jueves_i!='')
        {
            $establecimiento->jueves_i = $jueves_i;
            $bandera=true;
        }
        if ($jueves_f != null && $jueves_f!='')
        {
            $establecimiento->jueves_f = $jueves_f;
            $bandera=true;
        }
        if ($viernes_i != null && $viernes_i!='')
        {
            $establecimiento->viernes_i = $viernes_i;
            $bandera=true;
        }
        if ($viernes_f != null && $viernes_f!='')
        {
            $establecimiento->viernes_f = $viernes_f;
            $bandera=true;
        }
        if ($sabado_i != null && $sabado_i!='')
        {
            $establecimiento->sabado_i = $sabado_i;
            $bandera=true;
        }

         if ($sabado_f != null && $sabado_f!='')
        {
            $establecimiento->sabado_f = $sabado_f;
            $bandera=true;
        }

        if ($domingo_i != null && $domingo_i!='')
        {
            $establecimiento->domingo_i = $domingo_i;
            $bandera=true;
        }
        if ($domingo_f != null && $domingo_f!='')
        {
            $establecimiento->domingo_f = $domingo_f;
            $bandera=true;
        }

        if ($bandera)
        {
            // Almacenamos en la base de datos el registro.
            if ($establecimiento->save() && $usuario->save()) {
                $establecimiento->usuario = $usuario;
                return response()->json(['message'=>'Establecimiento editado con éxito.',
                    'establecimiento'=>$establecimiento], 200);
            }else{
                return response()->json(['error'=>'Error al actualizar el establecimiento.'], 500);
            }
            
        }
        else
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
            // Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
            return response()->json(['error'=>'No se ha modificado ningún dato al establecimiento.'],409);
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
        // Comprobamos si el establecimiento existe o no.
        $establecimiento=\App\Establecimiento::find($id);

        if (count($establecimiento)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el establecimiento con id '.$id], 404);
        }

        $productos = $establecimiento->productos;

        if (sizeof($productos) > 0)
        {
            //Verificar si los productos del establecimineto estan en pedidos
            for ($i=0; $i < count($productos); $i++) { 
                $productos[$i]->delete();

                $pedidos = $productos[$i]->pedidos;

                if (sizeof($pedidos) > 0)
                {
                    // Devolvemos un código 409 Conflict. 
                    return response()->json(['error'=>'Este establecimiento no puede ser eliminado porque su productos están asociados a pedidos.'], 409);
                }
            }
        }

        if (sizeof($productos) > 0)
        {
            //Eliminar los productos asociados al establecimiento
            for ($i=0; $i < count($productos); $i++) { 
                $productos[$i]->delete();
            }
        }

        $usuario=\App\User::find($establecimiento->usuario_id);

        // Eliminamos la establecimiento.
        $establecimiento->delete();

        // Eliminamos el usuario del establecimiento.
        $usuario->delete();

        return response()->json(['message'=>'Se ha eliminado correctamente el establecimiento.'], 200);
    }

    public function establecimientosProdsSubcat()
    {
        //cargar todos los establecimientos con sus productos y su categoria
        $establecimientos = \App\Establecimiento::with('productos.subcategoria')->get();

        if(count($establecimientos) == 0){
            return response()->json(['error'=>'No existen establecimientos.'], 404);          
        }else{
            return response()->json(['establecimientos'=>$establecimientos], 200);
        } 
    }

    //Usada en el panel
    public function stblcmtsHabilitados()
    {
        //cargar todos los establecimientos en estado ON
        $establecimientos = \App\Establecimiento::where('estado', 'ON')->get();

        if(count($establecimientos) == 0){
            return response()->json(['error'=>'No existen establecimientos habilitados.'], 404);          
        }else{
            return response()->json(['establecimientos'=>$establecimientos], 200);
        }   
    }

    /*Retorna productos del establecimiento.
    donde la subcat a la que pertenece el producto este ON*/
    public function establecimientoProductos($id)
    {
        $establecimiento = \App\Establecimiento::with('productos.subcategoria')->find($id);

        if(count($establecimiento)==0){
            return response()->json(['error'=>'No existe el establecimiento con id '.$id], 404);          
        }else{

            $aux = [];

            for ($i=0; $i < count($establecimiento->productos) ; $i++) { 
                if ($establecimiento->productos[$i]->subcategoria->estado == 'ON') {
                    array_push($aux, $establecimiento->productos[$i]);
                }
            }

            return response()->json(['productos'=>$aux], 200);
        } 
    }
}
