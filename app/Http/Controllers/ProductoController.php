<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //cargar todas los productos
        $productos = \App\Producto::all();

        if(count($productos) == 0){
            return response()->json(['error'=>'No existen productos.'], 404);          
        }else{
            return response()->json(['productos'=>$productos], 200);
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
            !$request->input('estado') ||
            !$request->input('subcategoria_id') ||
            !$request->input('establecimiento_id'))
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
            return response()->json(['error'=>'Faltan datos necesarios para el proceso de alta.'],422);
        } 

        // Comprobamos si la subcategoria que nos están pasando existe o no.
        $subcategoria = \App\Subcategoria::find($request->input('subcategoria_id'));

        if(count($subcategoria)==0){
            return response()->json(['error'=>'No existe la subcategoría con id '.$request->input('subcategoria_id')], 404);          
        } 

        // Comprobamos si el establecimiento que nos están pasando existe o no.
        $establecimiento = \App\Establecimiento::find($request->input('establecimiento_id'));

        if(count($establecimiento)==0){
            return response()->json(['error'=>'No existe el establecimiento con id '.$request->input('establecimiento_id')], 404);          
        }


        //Comprobamos que no exista un producto con las mismas caracteristicas asociado al establecimiento
        $aux = \App\Producto::where('nombre', $request->input('nombre'))
                ->where('establecimiento_id', $request->input('establecimiento_id'))->get();
        if(count($aux)!=0){
           // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'Ya existe un producto con ese nombre asociado al establecimiento.'], 409);
        }

        //Generar código alatorio
        $salt = 'abcdefghijklmnopqrstuvwxyz1234567890';

        $true = true;
        while ($true) {
            $rand = '';
            $i = 0;
            $length = 10;

            while ($i < $length) {
                //Loop hasta que el string aleatorio contenga la longitud ingresada.
                $num = rand() % strlen($salt);
                $tmp = substr($salt, $num, 1);
                $rand = $rand . $tmp;
                $i++;
            }

            $codigo = $rand;

            $auxProd = \App\Producto::where('codigo', $codigo)->get();
            if(count($auxProd)==0){
               $true = false; //romper el bucle
            }
        }

        if($nuevoProducto=\App\Producto::create([
            'nombre' => $request->input('nombre'),
            'estado' => $request->input('estado'),
            'imagen' => $request->input('imagen'),
            'precio' => $request->input('precio'),
            'descripcion' => $request->input('descripcion'),
            'subcategoria_id' => $request->input('subcategoria_id'),
            'establecimiento_id' => $request->input('establecimiento_id'),
            'codigo' => $codigo,
            //'imagen' => $imagen
        ])){
           return response()->json(['message'=>'Producto creado con éxito.',
             'producto'=>$nuevoProducto], 200);
        }else{
            return response()->json(['error'=>'Error al crear el producto.'], 500);
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
        //cargar un producto
        $producto = \App\Producto::find($id);

        if(count($producto)==0){
            return response()->json(['error'=>'No existe el producto con id '.$id], 404);          
        }else{
            return response()->json(['producto'=>$producto], 200);
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
        // Comprobamos si el producto que nos están pasando existe o no.
        $producto=\App\Producto::find($id);

        if (count($producto)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el producto con id '.$id], 404);
        }      

        // Listado de campos recibidos teóricamente.
        $nombre=$request->input('nombre');
        $precio=$request->input('precio');
        //$imagen=$request->input('imagen');
        $descripcion=$request->input('descripcion');
        $subcategoria_id=$request->input('subcategoria_id');
        $estado=$request->input('estado');
        $imagen=$request->input('imagen');

        // Creamos una bandera para controlar si se ha modificado algún dato.
        $bandera = false;

        // Actualización parcial de campos.
        if ($nombre != null && $nombre!='')
        {
            $aux = \App\Producto::where('nombre', $request->input('nombre'))
            ->where('subcategoria_id', $producto->subcategoria_id)
            ->where('establecimiento_id', $producto->establecimiento_id)
            ->where('id', '<>', $producto->id)->get();

            if(count($aux)!=0){
               // Devolvemos un código 409 Conflict. 
                return response()->json(['error'=>'Ya existe otro producto con el nombre '.$nombre.' asociado al establecimiento.'], 409);
            }

            $producto->nombre = $nombre;
            $bandera=true;
        }

        if ($precio != null && $precio!='')
        {
            $producto->precio = $precio;
            $bandera=true;
        }

        /*if ($imagen != null && $imagen!='')
        {
            $producto->imagen = $imagen;
            $bandera=true;
        }*/

        if ($descripcion != null && $descripcion!='')
        {
            $producto->descripcion = $descripcion;
            $bandera=true;
        }

        if ($subcategoria_id != null && $subcategoria_id!='')
        {
            // Comprobamos si la subcategoria que nos están pasando existe o no.
            $subcategoria = \App\Subcategoria::find($subcategoria_id);

            if(count($subcategoria)==0){
                return response()->json(['error'=>'No existe la subcategoría con id '.$subcategoria_id], 404);          
            } 

            if ($producto->subcategoria_id != $subcategoria_id) {
                //Comprobar que no exista un producto con el mismo nombre en la nueva subcategoria
                $aux2 = \App\Producto::where('nombre', $producto->nombre)
                ->where('establecimiento_id', $producto->establecimiento_id)
                ->where('subcategoria_id', $subcategoria_id)->get();

                if(count($aux2)!=0){
                   // Devolvemos un código 409 Conflict. 
                    return response()->json(['error'=>'Ya existe un producto con el nombre '.$producto->nombre.' asociado al establecimiento y a la subcategoría '.$subcategoria->nombre.'.'], 409);
                }
            }

            $producto->subcategoria_id = $subcategoria_id;
            $bandera=true;
        }

        if ($estado != null && $estado!='')
        {
            $producto->estado = $estado;
            $bandera=true;
        }
        if ($imagen != null && $imagen!='')
        {
            $producto->imagen = $imagen;
            $bandera=true;
        }

        if ($bandera)
        {
            // Almacenamos en la base de datos el registro.
            if ($producto->save()) {
                return response()->json(['message'=>'Producto editado con éxito.',
                    'producto'=>$producto], 200);
            }else{
                return response()->json(['error'=>'Error al actualizar el producto.'], 500);
            }
            
        }
        else
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
            // Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
            return response()->json(['error'=>'No se ha modificado ningún dato al producto.'],409);
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
        // Comprobamos si el producto que nos están pasando existe o no.
        $producto = \App\Producto::find($id);

        if(count($producto)==0){
            return response()->json(['error'=>'No existe el producto con id '.$id], 404);          
        } 

        $pedidos = $producto->pedidos;

        if (sizeof($pedidos) > 0)
        {
            // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'Este producto no puede ser eliminado porque posee pedidos asociados.'], 409);
        }

        // Eliminamos el producto si no tiene relaciones.
        $producto->delete();

        return response()->json(['message'=>'Se ha eliminado correctamente el producto.'], 200);
    }

    public function productosSubcatEst()
    {
        //cargar todos los productos con su subcategoria y establecimineto
        $productos = \App\Producto::with('subcategoria')->with('establecimiento')->get();

        if(count($productos) == 0){
            return response()->json(['error'=>'No existen productos.'], 404);          
        }else{
            return response()->json(['productos'=>$productos], 200);
        } 
    }

    public function buscarCodigos(Request $request)
    {
        // Primero comprobaremos si estamos recibiendo todos los campos obligatorios.
        if (!$request->input('codigos'))
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
            return response()->json(['error'=>'Falta el parametro codigos.'],422);
        }

        $codigos = json_decode($request->input('codigos'));

        $productos = [];

        for ($i=0; $i < count($codigos) ; $i++) { 
            $prod = \App\Producto::where('codigo', $codigos[$i]->codigo)
                ->where('estado', 'ON')->with('subcategoria')->get();
            if(count($prod) != 0){
               array_push($productos, $prod[0]);
            }   
        }    

        
        return response()->json(['productos'=>$productos], 200);
        
    }

    /*Cargar los productos asociados a un establecimiento*/
    public function productosEst($establecimiento_id)
    {
        //cargar todos los productos con su subcategoria y establecimineto
        $productos = \App\Producto::with('subcategoria')
            ->with('establecimiento')
            ->where('establecimiento_id', $establecimiento_id)
            ->get();

        if(count($productos) == 0){
            return response()->json(['error'=>'No existen productos asociados a este establecimiento.'], 404);          
        }else{
            return response()->json(['productos'=>$productos], 200);
        } 
    }

    /*Usada en el panel*/
    public function prodHabSubcatEst($subcategoria_id, $establecimiento_id)
    {
        //cargar todos los productos 
        $productos = \App\Producto::where('estado', 'ON')
            ->where('subcategoria_id', $subcategoria_id)
            ->where('establecimiento_id', $establecimiento_id)
            ->get();

        if(count($productos) == 0){
            return response()->json(['error'=>'No existen productos habilitados para esta subcategoría.'], 404);          
        }else{
            return response()->json(['productos'=>$productos], 200);
        } 
    }
}
