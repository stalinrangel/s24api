<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SubCategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //cargar todas las subcat
        $subcategorias = \App\Subcategoria::all();

        if(count($subcategorias) == 0){
            return response()->json(['error'=>'No existen subcategorías.'], 404);          
        }else{
            return response()->json(['subcategorias'=>$subcategorias], 200);
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
        // Listado de campos recibidos teóricamente.
        //$nombre=$request->input('nombre'); 

        // Primero comprobaremos si estamos recibiendo todos los campos.
        if ( !$request->input('nombre') ||
             !$request->input('estado') ||
             !$request->input('categoria_id'))
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
            return response()->json(['error'=>'Faltan datos necesarios para el proceso de alta.'],422);
        } 
        
        $aux = \App\Subcategoria::where('nombre', $request->input('nombre'))->get();
        if(count($aux)!=0){
           // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'Ya existe una subcategoría con ese nombre.'], 409);
        }

        $categoria = \App\Categoria::where('id',$request->input('categoria_id'))->get();
        if(count($categoria)==0){
           // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'No existe la categoría con id '.$request->input('categoria_id')], 409);
        }

        if($nuevaSubCategoria=\App\Subcategoria::create($request->all())){
           return response()->json(['message'=>'Subcategoría creada con éxito.',
             'subcategoria'=>$nuevaSubCategoria], 200);
        }else{
            return response()->json(['error'=>'Error al crear la subcategoría.'], 500);
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
        //cargar una subcat
        $subcategoria = \App\Subcategoria::find($id);

        if(count($subcategoria)==0){
            return response()->json(['error'=>'No existe la subcategoría con id '.$id], 404);          
        }else{
            return response()->json(['subcategoria'=>$subcategoria], 200);
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
        // Comprobamos si la subcategoria que nos están pasando existe o no.
        $subcategoria=\App\Subcategoria::find($id);

        if (count($subcategoria)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe la subcategoría con id '.$id], 404);
        }      

        // Listado de campos recibidos teóricamente.
        $nombre=$request->input('nombre');
        $imagen=$request->input('imagen');
        $categoria_id=$request->input('categoria_id');
        $estado=$request->input('estado');
        $productos=$request->input('productos');

        // Creamos una bandera para controlar si se ha modificado algún dato.
        $bandera = false;

        // Actualización parcial de campos.
        if ($nombre != null && $nombre!='')
        {
            $aux = \App\Subcategoria::where('nombre', $request->input('nombre'))
            ->where('id', '<>', $subcategoria->id)->get();

            if(count($aux)!=0){
               // Devolvemos un código 409 Conflict. 
                return response()->json(['error'=>'Ya existe otra subcategoría con ese nombre.'], 409);
            }

            $subcategoria->nombre = $nombre;
            $bandera=true;
        }

        if ($imagen != null && $imagen!='')
        {
            $subcategoria->imagen = $imagen;
            $bandera=true;
        }

        if ($categoria_id != null && $categoria_id!='')
        {
            // Comprobamos si la categoria que nos están pasando existe o no.
            $categoria = \App\Categoria::find($categoria_id);

            if(count($categoria)==0){
                return response()->json(['error'=>'No existe la categoría con id '.$categoria_id], 404);          
            } 

            if ($subcategoria->categoria_id != $categoria_id) {
                //Comprobar que no exista una subcat con el mismo nombre en la nueva categoria
                $aux2 = \App\Subcategoria::where('nombre', $subcategoria->nombre)
                ->where('categoria_id', $categoria_id)->get();

                if(count($aux2)!=0){
                   // Devolvemos un código 409 Conflict. 
                    return response()->json(['error'=>'Ya existe una subcategoria con el nombre '.$subcategoria->nombre.' asociada a la categoría '.$categoria->nombre.'.'], 409);
                }
            }

            $subcategoria->categoria_id = $categoria_id;
            $bandera=true;
        }

        if ($estado != null && $estado!='')
        {

            if ($estado == 'OFF') {
                $productos = $subcategoria->productos;

                if (sizeof($productos) > 0)
                {
                    for ($i=0; $i < count($productos) ; $i++) { 
                        $productos[$i]->estado = $estado;
                        $productos[$i]->save();
                    }
                }
            }

            $subcategoria->estado = $estado;
            $bandera=true;
        }

        if (sizeof($productos) > 0 )
        {
            $bandera=true;

            $productos = json_decode($productos);
            for ($i=0; $i < count($productos) ; $i++) {

                if ($productos[$i]->estado == 'ON') {

                    $producto = \App\Producto::find($productos[$i]->id);

                    if(count($producto) == 0){
                       // Devolvemos un código 409 Conflict. 
                        return response()->json(['error'=>'No existe el producto con id '.$productos[$i]->id], 409);
                    }else{
                        $producto->estado = $productos[$i]->estado;
                        $producto->save();
                    }
                }  
            }
        }

        if ($bandera)
        {
            // Almacenamos en la base de datos el registro.
            if ($subcategoria->save()) {
                return response()->json(['message'=>'Subcategoría editada con éxito.',
                    'subcategoria'=>$subcategoria], 200);
            }else{
                return response()->json(['error'=>'Error al actualizar la subcategoría.'], 500);
            }
            
        }
        else
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
            // Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
            return response()->json(['error'=>'No se ha modificado ningún dato a la subcategoría.'],409);
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
        // Comprobamos si la subcategoria existe o no.
        $subcategoria=\App\Subcategoria::find($id);

        if (count($subcategoria)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe la subcategoría con id '.$id], 404);
        }
       
        $productos = $subcategoria->productos;

        if (sizeof($productos) > 0)
        {
            // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'Esta subcategoría no puede ser eliminada porque posee productos asociados.'], 409);
        }

        // Eliminamos la subcategoria si no tiene relaciones.
        $subcategoria->delete();

        return response()->json(['message'=>'Se ha eliminado correctamente la subcategoría.'], 200);
    }

    public function subcatsProdsEst()
    {
        //cargar todas las subcategorias con sus productos y su establecimiento
        $subcategorias = \App\Subcategoria::with('productos.establecimiento')->get();

        if(count($subcategorias) == 0){
            return response()->json(['error'=>'No existen subcategorías.'], 404);          
        }else{
            return response()->json(['subcategorias'=>$subcategorias], 200);
        } 
    }

    public function subcategoriasCategoria()
    {
        //cargar todos las subcategorias con su categoria
        $subcategorias = \App\Subcategoria::with('categoria')->get();

        if(count($subcategorias) == 0){
            return response()->json(['error'=>'No existen subcategorías.'], 404);          
        }else{
            return response()->json(['subcategorias'=>$subcategorias], 200);
        } 
        
    }

    /*Retorna productos de la subcategoria.
    donde el estable al que pertenece el producto esta ON*/
    public function subcategoriaProductos($id)
    {
        //cargar una subcat con sus subcat
        $subcategoria = \App\Subcategoria::with('productos.establecimiento')->find($id);

        if(count($subcategoria)==0){
            return response()->json(['error'=>'No existe la subcategoría con id '.$id], 404);          
        }else{

            $aux = [];

            for ($i=0; $i < count($subcategoria->productos) ; $i++) { 
                if ($subcategoria->productos[$i]->establecimiento->estado == 'ON') {
                    array_push($aux, $subcategoria->productos[$i]);
                }
            }

            return response()->json(['productos'=>$aux], 200);
        } 
    }

    //Usada en el panel
    public function subcategoriasHabilitadas()
    {
        //cargar todas las subcat en estado ON
        $subcategorias = \App\Subcategoria::where('estado', 'ON')->get();

        if(count($subcategorias) == 0){
            return response()->json(['error'=>'No existen subcategorías habilitadas.'], 404);          
        }else{
            return response()->json(['subcategorias'=>$subcategorias], 200);
        }   
    }

    //Usada en el panel
    public function subcatHabCat($categoria_id)
    {
        //cargar todas las subcat en estado ON
        $subcategorias = \App\Subcategoria::where('categoria_id', $categoria_id)
            ->where('estado', 'ON')->get();

        if(count($subcategorias) == 0){
            return response()->json(['error'=>'No existen subcategorías habilitadas para esta categoría.'], 404);          
        }else{
            return response()->json(['subcategorias'=>$subcategorias], 200);
        }   
    }

}
