<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Exception;

class NotificacionController extends Controller
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
        ///curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
    }


    /*Localiza los repartidores disponibles para notificarles
    que hay un nuevo pedido*/
    public function localizarRepartidores(Request $request, $id, $intento=1)
    {
        //cargar un pedido y el punto en la ruta del establecimineto mas lejano
        $pedido = \App\Pedido::with(['ruta' => function ($query){
                    $query->where('posicion', 1);
                }])->find($id);

        if(count($pedido)==0){

            return response()->json(['error'=>'No existe el pedido AI00'.$id], 404);   

        }else{

            if ($pedido->estado_pago == null || $pedido->estado_pago == 'pendiente' ||
                $pedido->estado_pago == 'declinado') {
                return response()->json(['error'=>'Para poder asignar un repartidor el pedido AI00'.$pedido->id.' debe tener un pago registrado.'],409);
            }

            if ($pedido->repartidor_id) {
                return response()->json(['error'=>'El pedido AI00'.$pedido->id.' ya tiene un repartidor asignado.'],200);
            }   

            $usuario = \App\User::select('token_notificacion', 'nombre', 'tipo_usuario')->find($pedido->usuario_id);

            $admins = \App\User::select('token_notificacion')
                   ->where('tipo_usuario', 1)
                   ->get();

            //Enviar notificacion al panel solo una vez
            if ($intento == 1) {
                // Orden del reemplazo
                //$str     = "Line 1\nLine 2\rLine 3\r\nLine 4\n";
                $order   = array("\r\n", "\n", "\r", " ", "&");
                $replace = array('%20', '%20', '%20', '%20', '%26');

                //tratar el nombre del cliente.
                $clienteNom = str_replace($order, $replace, $usuario->nombre);

                //Tratar los espacios de la fecha del pedido
                $fecha = str_replace($order, $replace, $pedido->created_at);

                $obj = array('created_at'=>$fecha);
                $obj = json_encode($obj);

                for ($j=0; $j < count($admins) ; $j++) { 
                    if ($admins[$j]->token_notificacion) {
                        
                        $this->enviarNotificacion($admins[$j]->token_notificacion, $clienteNom.'%20ha%20realizado%20un%20pedido%20AI00'.$pedido->id, $pedido->id, 5, $obj);

                    }
                }
            }

            set_time_limit(500);

            //cargar todos los repartidores en ON, Trabajando y Disponibles
            $repartidores = \App\Repartidor::with('usuario')
                    ->where('estado', 'ON')
                    ->where('activo', 1)
                    ->where('ocupado', 2)
                    ->get();

            //return response()->json(['repartidores'=>$repartidores], 200);

            if(count($repartidores) == 0){

                //Repetir todo el proceso
                $intento = $intento + 1;
                if ($intento <= 10) {
                    //esperar
                    sleep(30);
                    $this->localizarRepartidores($request, $id, $intento);
                }

                if ($intento > 10) {

                    //En caso de que el pedido lo haga un cliente
                    if ($usuario->tipo_usuario == 2) {
                        //Enviar notificacion al cliente (pedido no asignado)
                        if ($usuario->token_notificacion) {
                            $this->enviarNotificacionCliente($usuario->token_notificacion, 'No%20hay%20repartidores%20disponibles%20para%20su%20pedido%20AI00'.$pedido->id, $pedido->id);
                        }
                    }
                    //En caso de que el pedido lo haga un establecimiento
                    else if ($usuario->tipo_usuario == 4) {
                        //Enviar notificacion al establecimiento (pedido no asignado)
                        if ($usuario->token_notificacion) {
                            $this->enviarNotificacion($usuario->token_notificacion, 'No%20hay%20repartidores%20disponibles%20para%20su%20pedido%20AI00'.$pedido->id, $pedido->id, 16);
                        }
                    }
                    

                    // Orden del reemplazo
                    //$str     = "Line 1\nLine 2\rLine 3\r\nLine 4\n";
                    $order   = array("\r\n", "\n", "\r", " ", "&");
                    $replace = array('%20', '%20', '%20', '%20', '%26');

                    //Tratar los espacios de la fecha del pedido
                    $fecha = str_replace($order, $replace, $pedido->created_at);

                    $obj = array('created_at'=>$fecha);
                    $obj = json_encode($obj);

                    //Enviar notificacion al panel (asignar pedido manualmente)
                    for ($j=0; $j < count($admins) ; $j++) { 
                        if ($admins[$j]->token_notificacion) {
                            
                            $this->enviarNotificacion($admins[$j]->token_notificacion, 'El%20pedido%20AI00'.$pedido->id.'%20necesita%20ser%20asignado%20desde%20el%20panel.', $pedido->id, 6, $obj);

                        }
                    }

                    return response()->json(['error'=>'No hay repartidores disponibles para su pedido AI00'.$pedido->id], 404);
                }
                          
            }

            //Calcular distancia(km) aproximada de los repartidores al establecimiento
            for ($i=0; $i < count($repartidores) ; $i++) { 
                $repartidores[$i]->distancia_calculada = $this->haversine($repartidores[$i]->lat, $repartidores[$i]->lng, $pedido->ruta[0]->lat, $pedido->ruta[0]->lng);
            }

            if (count($repartidores) > 1) {
                //Ordenar los repartidores de menor a mayor por distancia aproximada
                for ($i=0; $i < count($repartidores)-1 ; $i++) { 
                    for ($j=$i+1; $j < count($repartidores); $j++) { 
                        if ($repartidores[$i]->distancia_calculada > $repartidores[$j]->distancia_calculada) {
                            $aux = $repartidores[$i];
                            $repartidores[$i] = $repartidores[$j];
                            $repartidores[$j] = $aux; 
                        }
                    }
                }

                //Calcular distancia(m) real de los repartidores al establecimiento
                //destino
                $coordsEstablecimiento = $pedido->ruta[0]->lat.','.$pedido->ruta[0]->lng;

                $repSeleccionados = [];

                for ($i=0; $i < count($repartidores) ; $i++) { 
                    
                    //origen
                    $coordsRepartidor = $repartidores[$i]->lat.','.$repartidores[$i]->lng;

                    $distancia = $this->googleMaps($coordsRepartidor, $coordsEstablecimiento);
                    if ($distancia) {
                        $repartidores[$i]->distancia_real = $distancia;

                        array_push($repSeleccionados, $repartidores[$i]);
                    }else{
                        /*Si google maps no pudo calcular la distancia real,
                        se asume como distancia real la distancia calculada en metros*/
                        $repartidores[$i]->distancia_real = $repartidores[$i]->distancia_calculada * 1000;

                        array_push($repSeleccionados, $repartidores[$i]);
                    }

                    //Seleccionar solo 5 repartidores
                    if ($i == 4) {
                        break;
                    }   
                }

                //Ordenar los repartidores seleccionados de menor a mayor por distancia real
                for ($i=0; $i < count($repSeleccionados)-1 ; $i++) { 
                    for ($j=$i+1; $j < count($repSeleccionados); $j++) { 
                        if ($repSeleccionados[$i]->distancia_real > $repSeleccionados[$j]->distancia_real) {
                            $aux = $repSeleccionados[$i];
                            $repSeleccionados[$i] = $repSeleccionados[$j];
                            $repSeleccionados[$j] = $aux; 
                        }
                    }
                }

                $bandera = false; 

                //Enviar notificacion a los repartidores seleccionados
                for ($i=0; $i < count($repSeleccionados); $i++) { 
                    //Enviar notificacion a repartidor de pedido pendiente
                    if ($repSeleccionados[$i]->usuario->token_notificacion) {
                        $this->enviarNotificacion($repSeleccionados[$i]->usuario->token_notificacion, 'Tienes%20un%20nuevo%20pedido%20AI00'.$pedido->id, $pedido->id, 1);
                    }

                    //esperar
                    sleep(30);

                    //verificar
                    $pedidoAux = \App\Pedido::select('estado', 'repartidor_id')->find($id);
                    if ($pedidoAux->repartidor_id) {

                        //Nota esta notificacion se envia desde el la funcion aceptar pedido
                        /*//Enviar notificacion al cliente (pedido asignado)
                        if ($usuario->token_notificacion) {
                            $this->enviarNotificacionCliente($usuario->token_notificacion, 'Tu%20pedido%20va%20en%20camino.', $pedido->id, 7 );
                        }*/

                        $bandera = true;

                        //break;

                        return response()->json(['message'=>'Tu pedido AI00'.$pedido->id.' va en camino.'], 200);

                    }
                }

                //Repetir todo el proceso
                $intento = $intento + 1;
                if ($intento <= 10) {
                    $this->localizarRepartidores($request, $id, $intento);
                }

                if (!$bandera && $intento > 10) {
                    //verificar
                    $pedidoAux = \App\Pedido::select('estado', 'repartidor_id')->find($id);
                    if (!$pedidoAux->repartidor_id) {

                        // Orden del reemplazo
                        //$str     = "Line 1\nLine 2\rLine 3\r\nLine 4\n";
                        $order   = array("\r\n", "\n", "\r", " ", "&");
                        $replace = array('%20', '%20', '%20', '%20', '%26');

                        //Tratar los espacios de la fecha del pedido
                        $fecha = str_replace($order, $replace, $pedido->created_at);

                        $obj = array('created_at'=>$fecha);
                        $obj = json_encode($obj);

                        //Enviar notificacion al panel (asignar pedido manualmente)
                        for ($j=0; $j < count($admins) ; $j++) { 
                            if ($admins[$j]->token_notificacion) {
                                
                                $this->enviarNotificacion($admins[$j]->token_notificacion, 'El%20pedido%20AI00'.$pedido->id.'%20necesita%20ser%20asignado%20desde%20el%20panel.', $pedido->id, 6, $obj);

                            }
                        }

                        //En caso de que el pedido lo haga un cliente
                        if ($usuario->tipo_usuario == 2) {
                            //Enviar notificacion al cliente (pedido no asignado)
                            if ($usuario->token_notificacion) {
                                $this->enviarNotificacionCliente($usuario->token_notificacion, 'No%20hay%20repartidores%20disponibles%20para%20su%20pedido%20AI00'.$pedido->id, $pedido->id);
                            }
                        }
                        //En caso de que el pedido lo haga un establecimiento
                        else if ($usuario->tipo_usuario == 4) {
                            //Enviar notificacion al establecimiento (pedido no asignado)
                            if ($usuario->token_notificacion) {
                                $this->enviarNotificacion($usuario->token_notificacion, 'No%20hay%20repartidores%20disponibles%20para%20su%20pedido%20AI00'.$pedido->id, $pedido->id, 16);
                            }
                        }

                        return response()->json(['error'=>'No hay repartidores disponibles para su pedido AI00'.$pedido->id], 404);

                    }else{

                        //Nota esta notificacion se envia desde el la funcion aceptar pedido
                        /*//Enviar notificacion al cliente (pedido asignado)
                        if ($usuario->token_notificacion) {
                            $this->enviarNotificacionCliente($usuario->token_notificacion, 'Tu%20pedido%20va%20en%20camino.', $pedido->id, 7);
                        }*/

                        return response()->json(['message'=>'Tu pedido AI00'.$pedido->id.' va en camino.'], 200);
                    }
                }

            }else{

                $bandera = false; 

                //Enviar notificacion a unico repartidor disponible
                if ($repartidores[0]->usuario->token_notificacion) {
                    $this->enviarNotificacion($repartidores[0]->usuario->token_notificacion, 'Tienes%20un%20nuevo%20pedido%20AI00'.$pedido->id, $pedido->id, 1);
                }

                //esperar
                sleep(30);

                //verificar
                $pedidoAux = \App\Pedido::select('estado', 'repartidor_id')->find($id);
                if ($pedidoAux->repartidor_id) {

                    //Nota esta notificacion se envia desde el la funcion aceptar pedido
                    /*//Enviar notificacion al cliente (pedido asignado)
                    if ($usuario->token_notificacion) {
                        $this->enviarNotificacionCliente($usuario->token_notificacion, 'Tu%20pedido%20va%20en%20camino.', $pedido->id, 7);
                    }*/

                    $bandera = true;

                    return response()->json(['message'=>'Tu pedido AI00'.$pedido->id.' va en camino.'], 200);
                }

                //Repetir todo el proceso
                $intento = $intento + 1;
                if ($intento <= 10) {
                    $this->localizarRepartidores($request, $id, $intento);
                }

                if (!$bandera && $intento > 10) {

                    //verificar
                    $pedidoAux = \App\Pedido::select('estado', 'repartidor_id')->find($id);
                    if (!$pedidoAux->repartidor_id) {

                        // Orden del reemplazo
                        //$str     = "Line 1\nLine 2\rLine 3\r\nLine 4\n";
                        $order   = array("\r\n", "\n", "\r", " ", "&");
                        $replace = array('%20', '%20', '%20', '%20', '%26');

                        //Tratar los espacios de la fecha del pedido
                        $fecha = str_replace($order, $replace, $pedido->created_at);

                        $obj = array('created_at'=>$fecha);
                        $obj = json_encode($obj);

                        //Enviar notificacion al panel (asignar pedido manualmente)
                        for ($j=0; $j < count($admins) ; $j++) { 
                            if ($admins[$j]->token_notificacion) {
                                
                                $this->enviarNotificacion($admins[$j]->token_notificacion, 'El%20pedido%20AI00'.$pedido->id.'%20necesita%20ser%20asignado%20desde%20el%20panel.', $pedido->id, 6, $obj);

                            }
                        }

                        //En caso de que el pedido lo haga un cliente
                        if ($usuario->tipo_usuario == 2) {
                            //Enviar notificacion al cliente (pedido no asignado)
                            if ($usuario->token_notificacion) {
                                $this->enviarNotificacionCliente($usuario->token_notificacion, 'No%20hay%20repartidores%20disponibles%20para%20su%20pedido%20AI00'.$pedido->id, $pedido->id);
                            }
                        }
                        //En caso de que el pedido lo haga un establecimiento
                        else if ($usuario->tipo_usuario == 4) {
                            //Enviar notificacion al establecimiento (pedido no asignado)
                            if ($usuario->token_notificacion) {
                                $this->enviarNotificacion($usuario->token_notificacion, 'No%20hay%20repartidores%20disponibles%20para%20su%20pedido%20AI00'.$pedido->id, $pedido->id, 16);
                            }
                        }

                        return response()->json(['error'=>'No hay repartidores disponibles para su pedido AI00'.$pedido->id], 404);

                    }else{

                        //Nota esta notificacion se envia desde el la funcion aceptar pedido
                        /*//Enviar notificacion al cliente (pedido asignado)
                        if ($usuario->token_notificacion) {
                            $this->enviarNotificacionCliente($usuario->token_notificacion, 'Tu%20pedido%20va%20en%20camino.', $pedido->id, 7);
                        }*/

                        return response()->json(['message'=>'Tu pedido AI00'.$pedido->id.' va en camino.'], 200);
                    }
                    
                }
            }
            

            //return response()->json(['repSeleccionados'=>$repSeleccionados, 'repartidores'=>$repartidores], 200);
            return response()->json(['error'=>'Pedido AI00'.$pedido->id.' no asignado!'], 500);
        }

    }

    //Calculo de distancia real entre dos coordenadas con google maps
    public function googleMaps($origen, $destino)
    {
        try{ 
            $response = null;
            $response = \GoogleMaps::load('directions')
                ->setParam([
                    'origin'          => [$origen], 
                    'destination'     => [$destino], 
                ])->get();

            //dd( $response );  
            $response = json_decode( $response );

            if ( property_exists($response, 'status')) {
                if ($response->status == 'OK') {

                    //Distancia en metros
                    $distance_value=$response->routes[0]->legs[0]->distance->value;

                    return $distance_value;

                } 
            }

        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    //Peticion a google maps con coordenadas por defecto para pruebas
    public function googleMaps2()
    {
        try {

            $destino = "8.625395,-71.14731"; //destino
            $origen = '8.628430,-71.14147'; //origen

            $response = null;
            $response = \GoogleMaps::load('directions')
                ->setParam([
                    'origin'          => [$origen], 
                    'destination'     => [$destino], 
                ])->get();

            //dd( $response );  
            $response = json_decode( $response );

            if ( property_exists($response, 'status')) {
                if ($response->status == 'OK') {

                    //Distancia en metros
                    $distance_value=$response->routes[0]->legs[0]->distance->value;

                } 
            }

            return response()->json(['response'=>$response], 200);
            
        } catch (Exception $e) {

            return response()->json(['Error'=>'Exception capturada', 'response'=>$response], 500);
            
        }
        
    }

    //Calculo de distancia entre dos puntos geograficos
    public function haversine($point1_lat, $point1_lng, $point2_lat, $point2_lng, $decimals = 4  )
    {
        //calculo de la distancia en grados
        $degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_lng-$point2_lng)))));

        //conversion de la distancia a kilometros
        $distance = $degrees * 111.13384; // 1 grado = 111.13384, basandose en el diametro promedio de la tierra (12.735 km)

        return round($distance, $decimals);
    }


    /*Asignar un pedido a un repartidor $repartidor_id*/
    public function asignarPedido(Request $request, $repartidor_id)
    {
        // Comprobamos si el repartidor que nos están pasando existe o no.
        $repartidor = \App\Repartidor::
            //with('usuario')->
            with(['usuario' => function ($query) {
                $query->select('id', 'nombre', 'telefono', 'token_notificacion');
            }])->
            find($repartidor_id);

        if (count($repartidor)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el repartidor con id '.$repartidor_id], 404);
        }

        if ($repartidor->estado != 'ON' || $repartidor->activo != 1 || $repartidor->ocupado != 2) {
            return response()->json(['error'=>'Este repartidor ya no está disponible.'],409);
        }      

        // Listado de campos recibidos teóricamente.
        $pedido_id=$request->input('pedido_id');

        // Creamos una bandera para controlar si se ha modificado algún dato.
        $bandera = false;
        $notificarCliente = false;
        $notificarRepAntiguo = false;


        // Actualización parcial de campos.
        if ($pedido_id != null && $pedido_id!='')
        {
            // Comprobamos si el pedido que nos están pasando existe o no.
            $pedido = \App\Pedido::with('usuario')->find($pedido_id);

            if (count($pedido)==0)
            {
                // Devolvemos error codigo http 404
                return response()->json(['error'=>'No existe el pedido AI00'.$pedido_id], 404);
            }

            if ($pedido->estado_pago == null || $pedido->estado_pago == 'declinado' || $pedido->estado_pago == 'pendiente') {
                return response()->json(['error'=>'Para poder asignar un repartidor el pedido AI00'.$pedido_id.' debe tener un pago registrado.'],409);
            }

            if ($pedido->estado == 4) {
                return response()->json(['error'=>'El pedido AI00'.$pedido_id.' ya está marcado como finalizado.'],409);
            }

            if ($pedido->repartidor_id != null) {

                $rep = \App\Repartidor::with('usuario')->find($pedido->repartidor_id);

                if ($rep)
                {
                    //Se cambia a desocupado
                    $rep->ocupado = 2;
                    $rep->save();

                    $notificarRepAntiguo = true;
                }
            }else{
                $notificarCliente = true;
            }

            $pedido->repartidor_id = $repartidor->id;
            $pedido->repartidor_nom = $repartidor->usuario->nombre;
            $pedido->estado = 2;
            $bandera=true;
        }

        $repartidor->ocupado = 1;

        if ($bandera)
        {
            // Almacenamos en la base de datos el registro.
            if ($pedido->save() && $repartidor->save()) {

                //Enviar notificacion al repartidor (nuevo pedido asignado)
                if ($repartidor->usuario->token_notificacion) {
                    $this->enviarNotificacion($repartidor->usuario->token_notificacion, 'Se%20te%20ha%20asignado%20un%20pedido%20AI00'.$pedido_id, $pedido->id, 8);
                }

                if ($notificarCliente) {

                    //En caso de que el pedido lo haga un cliente
                    if ($pedido->usuario->tipo_usuario == 2) {
                        //Enviar notificacion al cliente (pedido no asignado)
                        if ($pedido->usuario->token_notificacion) {
                            $this->enviarNotificacionCliente($pedido->usuario->token_notificacion, 'Tu%20pedido%20AI00'.$pedido_id.'%20va%20en%20camino.', $pedido->id, 7);
                        }
                    }
                    //En caso de que el pedido lo haga un establecimiento
                    else if ($pedido->usuario->tipo_usuario == 4) {
                        //Enviar notificacion al establecimiento (pedido no asignado)
                        if ($pedido->usuario->token_notificacion) {
                            $this->enviarNotificacion($pedido->usuario->token_notificacion, 'Un%20repartidor%20ha%20tomado%20tu%20pedido%20AI00'.$pedido_id, $pedido->id, 16);
                        }
                    }
                }

                if ($notificarRepAntiguo) {
                    //Enviar notificacion al repartidor que se le quita el pedido
                    if ($rep->usuario->token_notificacion) {
                        $this->enviarNotificacion($rep->usuario->token_notificacion, 'Se%20te%20ha%20eliminado%20un%20pedido%20AI00'.$pedido_id, $pedido->id);
                    }
                }

                return response()->json(['message'=>'Pedido AI00'.$pedido_id.' asignado.', 'pedido'=>$pedido, 'repartidor'=>$repartidor], 200);

            }else{
                return response()->json(['error'=>'Error al asignar el pedido AI00'.$pedido_id], 500);
            }
        }
        else
        {
            // Se devuelve un array error con los error encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
            // Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
            return response()->json(['error'=>'No se ha modificado ningún dato.'],409);
        }
    }

    /*Notificar a un cliente la visita de un establecimiento*/
    public function notificarVisita(Request $request)
    {
        DB::table('rutas')
            ->where('id', $request->input('id'))
            ->update(['estado' => 2]);

        if ($request->input('token_notificacion') != '' && $request->input('token_notificacion') != null) {
            
            $explode1 = explode(" ",$request->input('nombre_establecimiento'));
            $nomEst = null;
            for ($i=0; $i < count($explode1); $i++) { 
                $nomEst = $nomEst.$explode1[$i].'%20'; 
            }

            $this->enviarNotificacionCliente($request->input('token_notificacion'), 'El%20repartidor%20ha%20visitado%20el%20establecimiento%20'.$nomEst, 'null', 7);

        }

        return response()->json(['message'=>'Cliente notificado.'], 200);
    }

    /*Un repartidor_id acepta un pedido y se notifica al cliente*/
    public function aceptarPedido(Request $request, $repartidor_id)
    {
        // Comprobamos si el repartidor que nos están pasando existe o no.
        $repartidor = \App\Repartidor::with('usuario')->find($repartidor_id);

        if (count($repartidor)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el repartidor con id '.$repartidor_id], 404);
        }      

        // Listado de campos recibidos teóricamente.
        $pedido_id=$request->input('pedido_id');

        // Creamos una bandera para controlar si se ha modificado algún dato.
        $bandera = false;

        // Actualización parcial de campos.
        if ($pedido_id != null && $pedido_id!='')
        {
            // Comprobamos si el pedido que nos están pasando existe o no.
            $pedido = \App\Pedido::with('usuario')->find($pedido_id);

            if (count($pedido)==0)
            {
                // Devolvemos error codigo http 404
                return response()->json(['error'=>'No existe el pedido AI00'.$pedido_id], 404);
            }

            if ($pedido->estado == 2 || $pedido->repartidor_id != null) {
                return response()->json(['error'=>'El pedido AI00'.$pedido_id.' ya tiene un repartidor asignado.'],409);
            }

            $pedido->repartidor_id = $repartidor->id;
            $pedido->repartidor_nom = $repartidor->usuario->nombre;
            $pedido->estado = 2;
            $bandera=true;
        }

        $repartidor->ocupado = 1;

        if ($bandera)
        {
            // Almacenamos en la base de datos el registro.
            if ($pedido->save() && $repartidor->save()) {

                //En caso de que el pedido lo haga un cliente
                if ($pedido->usuario->tipo_usuario == 2) {
                    //Enviar notificacion al cliente (pedido no asignado)
                    if ($pedido->usuario->token_notificacion) {
                        $this->enviarNotificacionCliente($pedido->usuario->token_notificacion, 'Tu%20pedido%20AI00'.$pedido_id.'%20va%20en%20camino.', $pedido->id, 7);
                    }
                }
                //En caso de que el pedido lo haga un establecimiento
                else if ($pedido->usuario->tipo_usuario == 4) {
                    //Enviar notificacion al establecimiento (pedido no asignado)
                    if ($pedido->usuario->token_notificacion) {
                        $this->enviarNotificacion($pedido->usuario->token_notificacion, 'Un%20repartidor%20ha%20tomado%20tu%20pedido%20AI00'.$pedido_id, $pedido->id, 16);
                    }
                }

                return response()->json(['message'=>'Pedido AI00'.$pedido_id.' aceptado.'], 200);
            }else{
                return response()->json(['error'=>'Error al aceptar el pedido AI00'.$pedido_id], 500);
            }
        }
        else
        {
            // Se devuelve un array error con los error encontrados y cabecera HTTP 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
            // Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
            return response()->json(['error'=>'No se ha modificado ningún dato.'],409);
        }
    }

    /*Un repartidor_id finaliza un pedido y se notifica al cliente*/
    public function finalizarPedido(Request $request, $repartidor_id)
    {

        // Comprobamos si el pedido que nos están pasando existe o no.
        $pedido = \App\Pedido::with('usuario')->find($request->input('pedido_id'));

        if (count($pedido)==0)
        {
            // Devolvemos error codigo http 404
            return response()->json(['error'=>'No existe el pedido AI00'.$request->input('pedido_id')], 404);
        }

        //Finalizar pedido
        DB::table('pedidos')
                    ->where('id', $request->input('pedido_id'))
                    ->update(['estado' => 4]);

        //Liberar repartidor
        DB::table('repartidores')
                    ->where('id', $repartidor_id)
                    ->update(['ocupado' => 2]);

        //Notificar al cliente
        /*if ($request->input('token_notificacion') != '' && $request->input('token_notificacion') != null && $request->input('token_notificacion') != 'null') {
            $this->enviarNotificacionCliente($request->input('token_notificacion'), 'El%20repartidor%20ha%20llegado%20a%20tu%20ubicación.', $request->input('pedido_id'), 3);
        }*/

        //En caso de que el pedido lo haga un cliente
        if ($pedido->usuario->tipo_usuario == 2) {
            //Notificar al cliente
            if ($request->input('token_notificacion') != '' && $request->input('token_notificacion') != null && $request->input('token_notificacion') != 'null') {
                $this->enviarNotificacionCliente($request->input('token_notificacion'), 'El%20repartidor%20ha%20llegado%20a%20tu%20ubicación.', $request->input('pedido_id'), 3);
            }
        }
        //En caso de que el pedido lo haga un establecimiento
        else if ($pedido->usuario->tipo_usuario == 4) {
            //Enviar notificacion al establecimiento (pedido no asignado)
            if ($pedido->usuario->token_notificacion) {
                $this->enviarNotificacion($pedido->usuario->token_notificacion, 'El%20pedido%20AI00'.$request->input('pedido_id').'%20ha%20llegado%20a%20su%20destino.', $pedido->id, 16);

            }
        }

        return response()->json(['message'=>'Pedido AI00'.$request->input('pedido_id').' finalizado.'], 200);
 
    }



}
