<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class ChatClienteController extends Controller
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



    //Enviar notificacion a un dispositivo cliente mediante su token_notificacion
    public function enviarNotificacionCliente($token_notificacion, $msg, $pedido_id = 'null', $accion = 0, $obj = 'null')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.alinstante.app/onesignalclientes.php?contenido=".$msg."&token_notificacion=".$token_notificacion."&pedido_id=".$pedido_id."&accion=".$accion."&obj=".$obj);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic N2JkMGVkNmQtN2VlOS00ZWQzLWFiZWYtMjFlZjVmODFiZTIw'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        //$fields = array('contenido'=>$msg);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "accion=t");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        //return $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //cargar todos los chats
        $chats = \App\ChatCliente::
            with(['usuario' => function ($query) {
                $query->select('id', 'nombre', 'imagen', 'tipo_usuario', 'token_notificacion');
            }])
            ->get();

        if(count($chats)!=0){
            //Cargar el ultimo mensaje
            for ($i=0; $i < count($chats) ; $i++) { 
                $chats[$i]->ultimo_msg = $chats[$i]
                    ->mensajes()
                    ->select('id', 'msg', 'created_at')
                    ->orderBy('id', 'desc')
                    ->take(1)->first(); 
            }          
        }

        return response()->json(['chats'=>$chats], 200);
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
    /*crear un mesage asociado a un chat*/
    public function storeMsg(Request $request)
    {
        // Primero comprobaremos si estamos recibiendo todos los campos.
        if ( !$request->input('emisor_id') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro emisor_id.'],422);
        }
        if ( !$request->input('receptor_id') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro receptor_id.'],422);
        }
        if ( !$request->input('msg') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro msg.'],422);
        }
        if ( !$request->input('emisor') )
        {
            // Se devuelve un array error con los errors encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para messagees de validación.
            return response()->json(['error'=>'Falta el parametro emisor.'],422);
        }


        //Verificar si existe un chat entre el admin y el cliente
        if ($request->input('chat_id') != null && $request->input('chat_id') != '') {
            $chat = \App\ChatCliente::find($request->input('chat_id'));
            $bandera = false;
        }
        else{
            $chat = \App\ChatCliente::
                where(function ($query) use ($request) {
                    $query->where('admin_id', $request->input('emisor_id'))
                          ->where('usuario_id', $request->input('receptor_id'));
                })
                ->orWhere(function ($query) use ($request) {
                    $query->where('admin_id', $request->input('receptor_id'))
                          ->where('usuario_id', $request->input('emisor_id'));
                })
                ->get();

                $bandera = true;
        }

        if(count($chat)==0){

            //Crear el nuevo chat
            if ($request->input('emisor') == 'admin') {

                $chat=\App\ChatCliente::create([
                        'admin_id' => $request->input('emisor_id'),
                        'usuario_id' => $request->input('receptor_id'),
                    ]);

            }else if ($request->input('emisor') == 'cliente') {

                $chat=\App\ChatCliente::create([
                        'admin_id' => $request->input('receptor_id'),
                        'usuario_id' => $request->input('emisor_id'),
                    ]);
            }

            //Crear el mensaje asociado al nuevo chat
            if ($request->input('emisor') == 'admin') {

               $msg = $chat->mensajes()->create([
                    'msg' => $request->input('msg'),
                    'emisor_id' => $chat->admin_id,
                    'receptor_id' => $chat->usuario_id,
                ]);

               if ($request->input('token_notificacion') != '' && $request->input('token_notificacion') != null && $request->input('token_notificacion') != 'null') {

                    /*$explode1 = explode(" ",$request->input('msg'));
                    $auxMsg = null;
                    for ($i=0; $i < count($explode1); $i++) { 
                        $auxMsg = $auxMsg.$explode1[$i].'%20'; 
                    }*/

                    // Orden del reemplazo
                    //$str     = "Line 1\nLine 2\rLine 3\r\nLine 4\n";
                    $order   = array("\r\n", "\n", "\r", " ", "&");
                    $replace = array('%20', '%20', '%20', '%20', '%26');

                    // Procesa primero \r\n así no es convertido dos veces.
                    $newstr = str_replace($order, $replace, $request->input('msg'));

                    $emisor = \App\User::select('id', 'nombre', 'tipo_usuario', 'token_notificacion', 'imagen')->find($msg->emisor_id);

                    //Tratar los espacios del nombre
                    $nombre = str_replace($order, $replace, $emisor->nombre);
                    $emisor->nombre = $nombre;

                    //Tratar los & de la imagen
                    $imagen = str_replace('&', '%26', $emisor->imagen);
                    $emisor->imagen = $imagen;

                    //Tratar los espacios de la fecha del mensaje
                    $created_at = str_replace($order, $replace, $msg->created_at);
                    $msgAux = array('id'=>$msg->id, 'estado'=>$msg->estado,
                        'chat_id'=>$msg->chat_id, 'emisor_id'=>$msg->emisor_id,
                        'receptor_id'=>$msg->receptor_id, 'created_at'=>$created_at);

                    $obj = array('chat_id'=>$msg->chat_id, 'emisor'=>$emisor, 'msg'=>$msgAux);
                    $obj = json_encode($obj);

                    $this->enviarNotificacionCliente($request->input('token_notificacion'), $newstr, 'null', 2, $obj);

                }

            }else if ($request->input('emisor') == 'cliente') {

               $msg = $chat->mensajes()->create([
                    'msg' => $request->input('msg'),
                    'emisor_id' => $chat->usuario_id,
                    'receptor_id' => $chat->admin_id,
                ]);

               if ($request->input('token_notificacion') != '' && $request->input('token_notificacion') != null && $request->input('token_notificacion') != 'null') {

                    /*$explode1 = explode(" ",$request->input('msg'));
                    $auxMsg = null;
                    for ($i=0; $i < count($explode1); $i++) { 
                        $auxMsg = $auxMsg.$explode1[$i].'%20'; 
                    }*/

                    // Orden del reemplazo
                    //$str     = "Line 1\nLine 2\rLine 3\r\nLine 4\n";
                    $order   = array("\r\n", "\n", "\r", " ", "&");
                    $replace = array('%20', '%20', '%20', '%20', '%26');

                    // Procesa primero \r\n así no es convertido dos veces.
                    $newstr = str_replace($order, $replace, $request->input('msg'));

                    $emisor = \App\User::select('id', 'nombre', 'tipo_usuario', 'token_notificacion', 'imagen')->find($msg->emisor_id);

                    //Tratar los espacios del nombre
                    $nombre = str_replace($order, $replace, $emisor->nombre);
                    $emisor->nombre = $nombre;

                    //Tratar los & de la imagen
                    $imagen = str_replace('&', '%26', $emisor->imagen);
                    $emisor->imagen = $imagen;

                    //Tratar los espacios de la fecha del mensaje
                    $created_at = str_replace($order, $replace, $msg->created_at);
                    $msgAux = array('id'=>$msg->id, 'estado'=>$msg->estado,
                        'chat_id'=>$msg->chat_id, 'emisor_id'=>$msg->emisor_id,
                        'receptor_id'=>$msg->receptor_id, 'created_at'=>$created_at);

                    $obj = array('chat_id'=>$msg->chat_id, 'emisor'=>$emisor, 'msg'=>$msgAux);
                    $obj = json_encode($obj);

                    $this->enviarNotificacion($request->input('token_notificacion'), $newstr, 'null', 2, $obj);

                }
            }

            $receptor = \App\User::select('id', 'token_notificacion')->find($msg->receptor_id);
            $msg->token_notificacion = $receptor->token_notificacion;
            //$msg->emisor = \App\User::select('id', 'nombre', 'imagen')->find($msg->emisor_id);

            return response()->json(['message'=>'Mensaje enviado con éxito.', 'chat'=>$chat, 'msg'=>$msg], 200);
            
        }
        //Crear el mensaje asociado al chat
        else{

            if ($bandera) {
                $chat = $chat[0];
            }

            if ($request->input('emisor') == 'admin') {

               $msg = $chat->mensajes()->create([
                    'msg' => $request->input('msg'),
                    'emisor_id' => $chat->admin_id,
                    'receptor_id' => $chat->usuario_id,
                ]);

               if ($request->input('token_notificacion') != '' && $request->input('token_notificacion') != null && $request->input('token_notificacion') != 'null') {

                    /*$explode1 = explode(" ",$request->input('msg'));
                    $auxMsg = null;
                    for ($i=0; $i < count($explode1); $i++) { 
                        $auxMsg = $auxMsg.$explode1[$i].'%20'; 
                    }*/

                    // Orden del reemplazo
                    //$str     = "Line 1\nLine 2\rLine 3\r\nLine 4\n";
                    $order   = array("\r\n", "\n", "\r", " ", "&");
                    $replace = array('%20', '%20', '%20', '%20', '%26');

                    // Procesa primero \r\n así no es convertido dos veces.
                    $newstr = str_replace($order, $replace, $request->input('msg'));

                    $emisor = \App\User::select('id', 'nombre', 'tipo_usuario', 'token_notificacion', 'imagen')->find($msg->emisor_id);

                    //Tratar los espacios del nombre
                    $nombre = str_replace($order, $replace, $emisor->nombre);
                    $emisor->nombre = $nombre;

                    //Tratar los & de la imagen
                    $imagen = str_replace('&', '%26', $emisor->imagen);
                    $emisor->imagen = $imagen;

                    //Tratar los espacios de la fecha del mensaje
                    $created_at = str_replace($order, $replace, $msg->created_at);
                    $msgAux = array('id'=>$msg->id, 'estado'=>$msg->estado,
                        'chat_id'=>$msg->chat_id, 'emisor_id'=>$msg->emisor_id,
                        'receptor_id'=>$msg->receptor_id, 'created_at'=>$created_at);

                    $obj = array('chat_id'=>$msg->chat_id, 'emisor'=>$emisor, 'msg'=>$msgAux);
                    $obj = json_encode($obj);

                    $this->enviarNotificacionCliente($request->input('token_notificacion'), $newstr, 'null', 2, $obj);

                }

            }else if ($request->input('emisor') == 'cliente') {

               $msg = $chat->mensajes()->create([
                    'msg' => $request->input('msg'),
                    'emisor_id' => $chat->usuario_id,
                    'receptor_id' => $chat->admin_id,
                ]);

               if ($request->input('token_notificacion') != '' && $request->input('token_notificacion') != null && $request->input('token_notificacion') != 'null') {

                    /*$explode1 = explode(" ",$request->input('msg'));
                    $auxMsg = null;
                    for ($i=0; $i < count($explode1); $i++) { 
                        $auxMsg = $auxMsg.$explode1[$i].'%20'; 
                    }*/

                    // Orden del reemplazo
                    //$str     = "Line 1\nLine 2\rLine 3\r\nLine 4\n";
                    $order   = array("\r\n", "\n", "\r", " ", "&");
                    $replace = array('%20', '%20', '%20', '%20', '%26');

                    // Procesa primero \r\n así no es convertido dos veces.
                    $newstr = str_replace($order, $replace, $request->input('msg'));

                    $emisor = \App\User::select('id', 'nombre', 'tipo_usuario', 'token_notificacion', 'imagen')->find($msg->emisor_id);

                    //Tratar los espacios del nombre
                    $nombre = str_replace($order, $replace, $emisor->nombre);
                    $emisor->nombre = $nombre;

                    //Tratar los & de la imagen
                    $imagen = str_replace('&', '%26', $emisor->imagen);
                    $emisor->imagen = $imagen;

                    //Tratar los espacios de la fecha del mensaje
                    $created_at = str_replace($order, $replace, $msg->created_at);
                    $msgAux = array('id'=>$msg->id, 'estado'=>$msg->estado,
                        'chat_id'=>$msg->chat_id, 'emisor_id'=>$msg->emisor_id,
                        'receptor_id'=>$msg->receptor_id, 'created_at'=>$created_at);

                    $obj = array('chat_id'=>$msg->chat_id, 'emisor'=>$emisor, 'msg'=>$msgAux);
                    $obj = json_encode($obj);

                    $this->enviarNotificacion($request->input('token_notificacion'), $newstr, 'null', 2, $obj);

                }
            }

            $receptor = \App\User::select('id', 'token_notificacion')->find($msg->receptor_id);
            $msg->token_notificacion = $receptor->token_notificacion;
            //$msg->emisor = \App\User::select('id', 'nombre', 'imagen')->find($msg->emisor_id);

           return response()->json(['message'=>'Mensaje enviado con éxito.',
             'chat'=>$chat, 'msg'=>$msg], 200);

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
        //cargar un chat
        $chat = \App\ChatCliente::
            with(['admin' => function ($query) {
                $query->select('id', 'nombre', 'imagen', 'tipo_usuario', 'token_notificacion');
            }])
            ->with(['usuario' => function ($query) {
                $query->select('id', 'nombre', 'imagen', 'tipo_usuario', 'token_notificacion');
            }])
            ->with(['mensajes.emisor' => function ($query) {
                $query->select('usuarios.id', 'usuarios.nombre', 'usuarios.imagen')->orderBy('id', 'asc');
            }])
            ->find($id);

        if(count($chat)==0){
            return response()->json(['error'=>'No existe el chat con id '.$id], 404);          
        }else{

            return response()->json(['chat'=>$chat], 200);
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
        // Comprobamos si el chat existe o no.
        $chat=\App\ChatCliente::find($id);

        if (count($chat)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el chat con id '.$id], 404);
        }
       
        $mensajes = $chat->mensajes;

        if (sizeof($mensajes) > 0)
        {
            for ($i=0; $i < count($mensajes) ; $i++) { 
                $mensajes[$i]->delete();
            }
        }

        // Eliminamos el chat.
        $chat->delete();

        return response()->json(['message'=>'Se ha eliminado correctamente el chat.'], 200);
    }

    /*Actualiza los mensajes de un receptor_id en un chat a leidos (estado=2)*/
    public function leerMensajes(Request $request)
    {
        DB::table('msgs_chats_clientes')
                ->where('chat_id', $request->input('chat_id'))
                ->where('receptor_id', $request->input('receptor_id'))
                /*->where('emisor_id', $request->input('emisor_id'))*/
                ->where('estado', 1)
                ->update(['estado' => 2]);

        return response()->json(['message'=>'ok'], 200);
    }

    /*Retorna los ultimos 10 mensajes sin leer (estado=1) de un receptor_id*/
    public function getMsgsSinLeer($receptor_id)
    {
        //cargar los ultimos 10 ids de mensajes sin leer
        $idsSinLeer = \App\MsgChatCliente::
            select(/*'id', 'estado', 'msg', 'created_at',*/ DB::raw('Max(id) AS max_id'))
            ->where('estado', 1)
            ->where('receptor_id', $receptor_id)
            ->groupBy('chat_id')
            ->orderBy('max_id', 'desc')
            ->take(10)
            ->get();

        $idsAux = [];
        for ($i=0; $i < count($idsSinLeer); $i++) { 
            array_push($idsAux, $idsSinLeer[$i]->max_id);
        }

        //cargar toda la info de los mensajes sin leer
        $msgs = \App\MsgChatCliente::select('id', 'msg', 'estado', 'chat_id', 'emisor_id', 'receptor_id', 'created_at')
            ->whereIn('id', $idsAux)
            ->with(['emisor' => function ($query) {
                $query->select('id', 'nombre', 'imagen', 'tipo_usuario', 'token_notificacion');
            }])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([/*'idsSinLeer'=>$idsAux,*/ 'msgs'=>$msgs], 200); 
    }

    /*Retorna el chat de un cliente*/
    public function miChat($usuario_id)
    {
        //Cargar el chat.
        $chat=\App\ChatCliente::where('usuario_id', $usuario_id)->get();

        //Cargar los datos del admin
            $admin=\App\User::where('tipo_usuario', 1)
                ->select('id', 'nombre', 'imagen', 'tipo_usuario', 'token_notificacion')
                ->get();

        if (count($chat)==0)
        {

            if (count($admin)==0) {
                // Devolvemos un código 409 Conflict.
                return response()->json(['Error'=>'No hay admis disponibles para iniciar un chat.'], 409);
            }
            else{
                // Devolvemos error codigo http 404
                return response()->json(['admin'=>$admin], 404); 
            }
            
        }

        return response()->json(['chat'=>$chat[0], 'admin'=>$admin], 200);
    }
}
