<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexDeuda($establecimiento_id)
    {
        /*cargar los pedidos´por pagar de los ultimos 30 dias
        relacionados con el establecimiento_id*/
        $pedidos = \App\Pedido::select('id', 'usuario_id', 'created_at')
            ->where(DB::raw("PERIOD_DIFF(DATE_FORMAT(now(), '%y%m') ,DATE_FORMAT(created_at, '%y%m'))"), '<=', 1)
            ->where('estado_pago','aprobado')
            ->with(['productos' => function ($query) use ($establecimiento_id) {
                $query->where('productos.establecimiento_id', $establecimiento_id)
                ->where('pedido_productos.estado_deuda', 1)
                ->select('productos.id', 'productos.nombre', 'productos.precio',
                'productos.descripcion', 'productos.subcategoria_id', 'productos.establecimiento_id');
            }])
            //->orderBy('id', 'desc')
            ->get();

        if(count($pedidos) == 0){
            return response()->json(['error'=>'No existen pedidos en los ultimos 30 días.'], 404);
        }else{

            $pedidosAux = [];
            $total_deuda = 0;
            for ($i=0; $i < count($pedidos); $i++) { 
                if (count($pedidos[$i]->productos) > 0) {

                    $total_pedido = 0;

                    for ($j=0; $j < count($pedidos[$i]->productos); $j++) { 
                        $total_pedido = $total_pedido + ($pedidos[$i]->productos[$j]->pivot->cantidad * $pedidos[$i]->productos[$j]->pivot->precio_unitario);
                    }

                    $pedidos[$i]->total_pedido = $total_pedido;
                    $pedidos[$i]->cancelar = 'NO';
                    $total_deuda = $total_deuda + $total_pedido;

                    array_push($pedidosAux, $pedidos[$i]);
                }
            }

            if (count($pedidosAux) == 0) {
                return response()->json(['error'=>'No hay deuda con el establecimiento en los últimos 30 días.'], 404);
            }else{
                return response()->json(['pedidos'=>$pedidosAux, 'total_deuda'=>$total_deuda], 200);
            }  
        }
    }

    public function indexPagos()
    {
        /*cargar los pagos de los ultimos 30 dias*/
        $pagos = \App\Pago::
            where(DB::raw("PERIOD_DIFF(DATE_FORMAT(now(), '%y%m') ,DATE_FORMAT(created_at, '%y%m'))"), '<=', 1)
            ->with(['establecimiento' => function ($query) {
                $query->select('id', 'nombre', 'direccion');
            }])
            ->orderBy('id', 'desc')
            ->get();

        if(count($pagos) == 0){
            return response()->json(['error'=>'No existen pagos en los últimos 30 días.'], 404);
        }else{

            return response()->json(['pagos'=>$pagos], 200);

        }
    }

    public function indexPagosEst($establecimiento_id)
    {
        /*cargar los pagos de los ultimos 30 dias al establecimiento_id*/
        $pagos = \App\Pago::where('establecimiento_id', $establecimiento_id)
            ->where(DB::raw("PERIOD_DIFF(DATE_FORMAT(now(), '%y%m') ,DATE_FORMAT(created_at, '%y%m'))"), '<=', 1)
            ->with(['establecimiento' => function ($query) {
                $query->select('id', 'nombre', 'direccion');
            }])
            ->orderBy('id', 'desc')
            ->get();

        if(count($pagos) == 0){
            return response()->json(['error'=>'No existen pagos en los últimos 30 días.'], 404);
        }else{

            return response()->json(['pagos'=>$pagos], 200);

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
        // Primero comprobaremos si estamos recibiendo todos los campos obligatorios.
        if (!$request->input('establecimiento_id') ||
            !$request->input('monto') ||
            !$request->input('pivots'))
        {
            // Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
            return response()->json(['error'=>'Faltan datos necesarios para el proceso de alta.'],422);
        }

        $establecimiento = \App\Establecimiento::find($request->input('establecimiento_id'));
        if(count($establecimiento) == 0){
           // Devolvemos un código 409 Conflict. 
            return response()->json(['error'=>'No existe el establecimiento al cual se quiere asociar el pago.'], 409);
        }

        //Tratar los pivots
        $pivots = json_decode($request->input('pivots'));

        if($nuevoPago=\App\Pago::create($request->all())){

            //Crear las relaciones en la tabla pivote
            for ($i=0; $i < count($pivots) ; $i++) { 

                $nuevoPago->pedidos()->attach($pivots[$i]->pedido_id);

                //Actualizar los productos del pedido a pagados
                DB::table('pedido_productos')
                    ->where('pedido_id', $pivots[$i]->pedido_id)
                    ->where('producto_id', $pivots[$i]->producto_id)
                    ->update(['estado_deuda' => 2]);
                   
            }

            return response()->json(['pago'=>$nuevoPago, 'message'=>'Pago registrado con éxito.'], 200);
        }else{
            return response()->json(['error'=>'Error al registrar el pago.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $establecimiento_id = $request->input('establecimiento_id'); 

        //cargar un pago
        $pago = \App\Pago::
            with(['pedidos.productos' => function ($query) use ($establecimiento_id) {
                $query->where('productos.establecimiento_id', $establecimiento_id)
                ->select('productos.id', 'productos.nombre', 'productos.precio',
                'productos.descripcion', 'productos.subcategoria_id', 'productos.establecimiento_id');
            }])
            ->find($id);

        if(count($pago)==0){
            return response()->json(['error'=>'No existe el pago con id '.$id], 404);          
        }else{

            for ($i=0; $i < count($pago->pedidos); $i++) { 
                if (count($pago->pedidos[$i]->productos) > 0) {

                    $total_pedido = 0;

                    for ($j=0; $j < count($pago->pedidos[$i]->productos); $j++) { 
                        $total_pedido = $total_pedido + ($pago->pedidos[$i]->productos[$j]->pivot->cantidad * $pago->pedidos[$i]->productos[$j]->pivot->precio_unitario);
                    }

                    $pago->pedidos[$i]->total_pedido = $total_pedido;
                }
            }

            return response()->json(['pago'=>$pago], 200);
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
        //
    }
}
