<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{

    //Enviar notificacion a un dispositivo repartidor/panel mediante su token_notificacion
    public function enviarNotificacion($token_notificacion, $msg, $pedido_id = 'null', $accion = 0, $obj = 'null')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.alinstante.app/onesignal.php?contenido=".$msg."&token_notificacion=".$token_notificacion."&pedido_id=".$pedido_id."&accion=".$accion."&obj=".$obj);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic MmY2ZjVjZDUtMDFkZi00ZjcwLTg4NTMtNGQzNGE1NmRiOThj'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        ///curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
    }

 
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //cargar todos los blogs
        $blogs = \App\Blog::orderBy('id', 'desc')->get();

        if(count($blogs) == 0){
            return response()->json(['error'=>'No existen blogs.'], 404);          
        }else{

            /*Cargar el contador de mensajes*/
            for ($i=0; $i < count($blogs) ; $i++) { 
                $blogs[$i]->count_msgs = $blogs[$i]->msgs()->count();
            }

            return response()->json(['blogs'=>$blogs], 200);
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
        if ( !$request->input('tema') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro tema (Nombre del blog).'],422);
        }
        if ( !$request->input('creador') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro creador (Creador del blog).'],422);
        }

        // Comprobamos si el blog que nos están pasando existe o no.
        $blogAux=\App\Blog::where('tema',$request->input('tema'))->get();

        if (count($blogAux)!=0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'Ya existe un blog con el nombre '.$request->input('tema')], 409);
        }

        if($blog=\App\Blog::create($request->all())){

            //Cargar los datos del admin
            $admin=\App\User::where('tipo_usuario', 1)
                ->select('id', 'token_notificacion')
                ->get();

            if (count($admin)!=0) {
                if ($admin[0]->token_notificacion && $admin[0]->token_notificacion != '' && $admin[0]->token_notificacion != 'null') {

                    $order   = array("\r\n", "\n", "\r", " ", "&");
                    $replace = array('%20', '%20', '%20', '%20', '%26');
                    $creador = str_replace($order, $replace, $request->input('creador'));
                    $tema = str_replace($order, $replace, $request->input('tema'));

                    $contenido = $creador.'%20creó%20el%20blog:%20'.$tema;

                    $fecha = str_replace($order, $replace, $blog->created_at);

                    $obj = array('blog_id'=>$blog->id, 'creador'=>$creador, 'tema'=>$tema, 'created_at'=>$fecha);
                    $obj = json_encode($obj);

                    $this->enviarNotificacion($admin[0]->token_notificacion, $contenido, 'null', 4, $obj);
                }
            }

           return response()->json(['message'=>'Blog creado con éxito.',
             'blog'=>$blog], 200);
        }else{
            return response()->json(['error'=>'Error al crear el blog.'], 500);
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
        //cargar un blog
        $blog = \App\Blog::/*with('msgs.usuario')*/
            with(['msgs.usuario' => function ($query) {
                $query->select('usuarios.id', 'usuarios.nombre', 'usuarios.imagen', 'usuarios.tipo_usuario', 'usuarios.token_notificacion');
            }])
            ->find($id);

        if(count($blog)==0){
            return response()->json(['error'=>'No existe el blog con id '.$id], 404);          
        }else{

            return response()->json(['blog'=>$blog], 200);
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
        // Comprobamos si el blog que nos están pasando existe o no.
        $blog=\App\Blog::find($id);

        if (count($blog)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el blog con id '.$id], 404);
        }      

        // Listado de campos recibidos teóricamente.
        $tema=$request->input('tema');
        $creador=$request->input('creador');

        // Creamos una bandera para controlar si se ha modificado algún dato.
        $bandera = false;

        // Actualización parcial de campos.
        if ($tema != null && $tema!='')
        {
            $blog->tema = $tema;
            $bandera=true;
        }

        if ($creador != null && $creador!='')
        {
            $blog->creador = $creador;
            $bandera=true;
        }

        if ($bandera)
        {
            // Almacenamos en la base de datos el registro.
            if ($blog->save()) {
                return response()->json(['message'=>'Blog editado con éxito.',
                    'blog'=>$blog], 200);
            }else{
                return response()->json(['error'=>'Error al actualizar el blog.'], 500);
            }
            
        }
        else
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
            // Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
            return response()->json(['error'=>'No se ha modificado ningún dato al blog.'],409);
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
        // Comprobamos si el blog existe o no.
        $blog=\App\Blog::find($id);

        if (count($blog)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el blog con id '.$id], 404);
        }
       
        $msgs = $blog->msgs;

        if (sizeof($msgs) > 0)
        {
            for ($i=0; $i < count($msgs); $i++) { 
                $msgs[$i]->delete();
            }
        }

        // Eliminamos la blog si no tiene relaciones.
        $blog->delete();

        return response()->json(['message'=>'Se ha eliminado correctamente el blog.'], 200);
    }
}
