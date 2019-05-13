<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DateTime;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Mail;
use Session;
use Redirect;


/*Controlador para manejar el olvido de password desde la app*/
class PasswordController extends Controller
{
    /*Genera un codigo aleatorio para el cliente
    con el correo que se pasa como parametro*/
    public function generarCodigo($correo)
    {
        //verificar si existe el cliente que nos estan pasando
        $obj = \App\User::where('email', $correo)->get();

        if(count($obj)==0){
            return response()->json(['error'=>'No existe el cliente con el correo '.$correo], 404);          
        }else{

            //Generamos el codigo aleatorio

            // Solo cuando deseas que devuelva numeros.
            //$salt = '1234567890';
            
            // Solo cuando deseas que devuelva letras en minusculas.
            //$salt = 'abcdefghijklmnopqrstuvwxyz';
           
            // Solo cuando deseas que devuelva letras en mayusculas.
            //$salt = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            // Para cuando deseas que la cadena este compuesta por letras y numeros
            $salt = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

            $rand = '';
            $i = 0;
            $length = 6;

            while ($i < $length) {
                //Loop hasta que el string aleatorio contenga la longitud ingresada.
                $num = rand() % strlen($salt);
                $tmp = substr($salt, $num, 1);
                $rand = $rand . $tmp;
                $i++;
            }

            //Asignamos el codigo aleatorio al cliente
            $cliente = $obj[0];
            $cliente->codigo_verificacion = $rand;
            $cliente->save();

            $data = array( 'codigo_verificacion' => $rand);

            //Enviamos el correo con el codigo aleatorio
            Mail::send('emails.contact', $data, function($msj) use ($correo){
                $msj->subject('Código de verificación');
                $msj->to($correo);
            });

            //Informar al cliente despues de enviar el correo con el codigo
            return response()->json(['status'=>'ok', 'message'=>'Código de verificación enviado a '.$correo,
                     'codigo'=>$rand], 200);
        }
    }

    /*Permite el ingreso si el codigo tiene menos
    de 5 min de haber sido generado.*/
    public function validarCodigo($codigo)
    {
        //verificar si existe el cliente con el codigo que nos estan pasando
        $obj = \App\User::where('codigo_verificacion', $codigo)->get();

        if(count($obj)==0){
            return response()->json(['error'=>'No existe el cliente con el código '.$codigo], 404);          
        }else{

            $cliente = $obj[0];

            //Validacion para diferenciar mayusculas y minusculas
            if($codigo === $cliente->codigo_verificacion){

                $fechaActual = new DateTime("now");

                /*minutos de diferencia entre la hora de generacion
                del codigo y la hora actual*/
                $minDiff = $cliente->updated_at->diff($fechaActual)->i;

                /*Si es menor a 5 min se da acceso*/
                if ($minDiff <= 5) {

                    if (!$token = JWTAuth::fromUser($cliente)) {
                        return response()->json(['error' => 'could_not_create_token'], 401);
                    }

                    $cliente = JWTAuth::toUser($token);

                    return response()->json(['status'=>'ok', 'minDiff'=>$minDiff,
                            'token' => $token, 'cliente_id'=>$cliente->id], 200);
                }
                /*De lo contrario se reseta el codigo y no se da acceso*/
                else{
                    $cliente->codigo_verificacion = null;
                    $cliente->save();

                    return response()->json(['error'=>'El tiempo para el código de verificación ha expirado.',
                                'minDiff'=>$minDiff], 401);
                }

            }else{
                return response()->json(['error'=>'No existe el cliente con el código '.$codigo], 404);
            }
            
        }
    }
}
