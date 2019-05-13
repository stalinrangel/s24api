<?php

namespace App\Listeners;

use App\Events\EventoPrueba;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OyentePrueba implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  EventoPrueba  $event
     * @return void
     */
    public function handle(EventoPrueba $event)
    {

        $coordsEstablecimiento = "8.625395,-71.14731"; //destino
        $coordsRepartidor = '8.628430,-71.14147'; //origen

        /*$response = \GoogleMaps::load('directions')
            ->setParam([
                'origin'          => [$coordsRepartidor], 
                'destination'     => [$coordsEstablecimiento], 
            ])->get();

        //dd( $response );  
        $response = json_decode( $response );

        if ( property_exists($response, 'status')) {
            if ($response->status == 'OK') {

                //Distancia en metros
                $distance_value=$response->routes[0]->legs[0]->distance->value;

                //return response()->json(['distance_value'=>$distance_value], 200);
            }
            
        }*/

        sleep(10);

        return response()->json(['error'=>'Error calculando la ruta.'], 500);
    }
}
