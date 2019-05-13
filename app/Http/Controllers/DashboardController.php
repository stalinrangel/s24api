<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

//date_default_timezone_set('America/Cancun');

class DashboardController extends Controller
{

    /*Retorna los contadores del dia actual*/
    public function contadores()
    {
        $dia_actual = date("d"); //j  Día del mes sin ceros iniciales 1 a 31
                                //d Día del mes, 2 dígitos con ceros iniciales  01 a 31
        $mes_actual = date("m");
        $anio_actual = date("Y");

        /*return response()->json(['date'=>date("Y-m-d H:i:s"),
            'dia_actual' => date("d"),
        'mes_actual' => date("m"),
        'anio_actual' => date("Y")], 200);*/

        $pedidos_curso = \App\Pedido::where('estado_pago','aprobado')
            ->where(function ($query) {
                $query
                    ->where('estado',1)
                    ->orWhere('estado',2)
                    ->orWhere('estado',3);
            })
            ->where(DB::raw('DAY(created_at)'),$dia_actual)
            ->where(DB::raw('MONTH(created_at)'),$mes_actual)
            ->where(DB::raw('YEAR(created_at)'),$anio_actual)
            ->count();

        $pedidos_finalizados = \App\Pedido::where('estado',4)
            ->where(DB::raw('DAY(created_at)'),$dia_actual)
            ->where(DB::raw('MONTH(created_at)'),$mes_actual)
            ->where(DB::raw('YEAR(created_at)'),$anio_actual)
            ->count();

        $repartidores_activos = \App\Repartidor::where('estado','ON')
            ->where('estado', 'ON')
            ->where('activo', 1)
            ->where('ocupado', 2)
            ->count();

        $dinero_recaudado = \App\Pedido::where('estado_pago','aprobado')
            ->where(DB::raw('DAY(created_at)'),$dia_actual)
            ->where(DB::raw('MONTH(created_at)'),$mes_actual)
            ->where(DB::raw('YEAR(created_at)'),$anio_actual)
            ->sum('costo');

        return response()->json(['pedidos_curso'=>$pedidos_curso,
            'pedidos_finalizados'=>$pedidos_finalizados,
            'repartidores_activos'=>$repartidores_activos,
            'dinero_recaudado'=>$dinero_recaudado], 200);

    }

    /*Retorna las categorias con el
    contador de  productos solicitados
    filtrados por fecha*/
    public function filterCategorias(Request $request, \App\Pedido $pedido)
    {
        set_time_limit(300);

        $pedido = $pedido->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $pedido->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $pedido->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $pedido->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        //$pedidos = $pedido->get();

        $pedidos = $pedido->select('id', 'created_at')
            ->with(['productos' => function ($query) {
                $query->select('productos.id', 'productos.nombre', 'productos.subcategoria_id');
            }])
            ->get();

        if (count($pedidos) == 0) {
            return response()->json(['categorias'=>[]], 200);
        }

        //cargar todas las subcategorias
        $subcategorias = \App\Subcategoria::
            select('id', 'nombre', 'categoria_id')->get();

        if(count($subcategorias) == 0){
            return response()->json(['error'=>'No existen subcategorias.'], 404);          
        }else{

            for ($i=0; $i < count($subcategorias) ; $i++) { 
                $subcategorias[$i]->count_solicitados = 0;
                for ($j=0; $j < count($pedidos) ; $j++) { 
                    for ($k=0; $k < count($pedidos[$j]->productos) ; $k++) { 
                        if ($pedidos[$j]->productos[$k]->subcategoria_id == $subcategorias[$i]->id) {
                            $subcategorias[$i]->count_solicitados = $subcategorias[$i]->count_solicitados + 1; 
                        }
                    }
                }
            }
        }

        //cargar todas las categorias
        $categorias = \App\Categoria::
            select('id', 'nombre')->get();

        if(count($categorias) == 0){
            return response()->json(['error'=>'No existen categorias.'], 404);          
        }else{

            for ($i=0; $i < count($categorias) ; $i++) { 
                $categorias[$i]->count_solicitados = 0;
                for ($j=0; $j < count($subcategorias) ; $j++) {  
                    if ($subcategorias[$j]->categoria_id == $categorias[$i]->id) {
                        $categorias[$i]->count_solicitados = $categorias[$i]->count_solicitados + $subcategorias[$j]->count_solicitados; 
                    }
                }
            }

            $aux = [];
            for ($i=0; $i < count($categorias) ; $i++) { 
                if ($categorias[$i]->count_solicitados > 0) {
                    array_push($aux, $categorias[$i]);
                }
            }

            //return $pedidos;
            return response()->json([/*'pedidos'=>$pedidos,*/ 'categorias'=>$aux], 200);
            
        }
    }

    /*Retorna las subcategorias con el
    contador de  productos solicitados
    filtrados por fecha*/
    public function filterSubcateogrias(Request $request, \App\Pedido $pedido)
    {
        set_time_limit(300);

        $pedido = $pedido->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $pedido->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $pedido->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $pedido->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        //$pedidos = $pedido->get();

        $pedidos = $pedido->select('id', 'created_at')
            ->with(['productos' => function ($query) {
                $query->select('productos.id', 'productos.nombre', 'productos.subcategoria_id');
            }])
            ->get();

        if (count($pedidos) == 0) {
            return response()->json(['subcategorias'=>[]], 200);
        }

        //cargar todas las subcategorias
        $subcategorias = \App\Subcategoria::
            select('id', 'nombre')->get();

        if(count($subcategorias) == 0){
            return response()->json(['error'=>'No existen subcategorias.'], 404);          
        }else{

            for ($i=0; $i < count($subcategorias) ; $i++) { 
                $subcategorias[$i]->count_solicitados = 0;
                for ($j=0; $j < count($pedidos) ; $j++) { 
                    for ($k=0; $k < count($pedidos[$j]->productos) ; $k++) { 
                        if ($pedidos[$j]->productos[$k]->subcategoria_id == $subcategorias[$i]->id) {
                            $subcategorias[$i]->count_solicitados = $subcategorias[$i]->count_solicitados + 1; 
                        }
                    }
                }
            }

            $aux = [];
            for ($i=0; $i < count($subcategorias) ; $i++) { 
                if ($subcategorias[$i]->count_solicitados > 0) {
                    array_push($aux, $subcategorias[$i]);
                }
            }

            //return $pedidos;
            return response()->json([/*'pedidos'=>$pedidos,*/ 'subcategorias'=>$aux], 200);
            
        }
    }

    /*Retorna los establecimientos con el
    contador de productos solicitados
    filtrados por fecha*/
    public function filterEstablecimientos(Request $request, \App\Pedido $pedido)
    {
        set_time_limit(300);

        $pedido = $pedido->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $pedido->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $pedido->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $pedido->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        //$pedidos = $pedido->get();

        $pedidos = $pedido->select('id', 'created_at')
            ->with(['productos' => function ($query) {
                $query->select('productos.id', 'productos.nombre', 'productos.establecimiento_id');
            }])
            ->get();

        if (count($pedidos) == 0) {
            return response()->json(['establecimientos'=>[]], 200);
        }

        //cargar todos los establecimientos
        $establecimientos = \App\Establecimiento::
            select('id', 'nombre', 'direccion')->get();

        if(count($establecimientos) == 0){
            return response()->json(['error'=>'No existen establecimientos.'], 404);          
        }else{

            for ($i=0; $i < count($establecimientos) ; $i++) { 
                $establecimientos[$i]->count_solicitados = 0;
                for ($j=0; $j < count($pedidos) ; $j++) { 
                    for ($k=0; $k < count($pedidos[$j]->productos) ; $k++) { 
                        if ($pedidos[$j]->productos[$k]->establecimiento_id == $establecimientos[$i]->id) {
                            $establecimientos[$i]->count_solicitados = $establecimientos[$i]->count_solicitados + 1; 
                        }
                    }
                }
            }

            $aux = [];
            for ($i=0; $i < count($establecimientos) ; $i++) { 
                if ($establecimientos[$i]->count_solicitados > 0) {
                    array_push($aux, $establecimientos[$i]);
                }
            }

            //return $pedidos;
            return response()->json([/*'pedidos'=>$pedidos,*/ 'establecimientos'=>$aux], 200);
            
        }
    }

    /*Retorna los pedidos solicitados
    agrupados por hora
    filtrados por fecha*/
    public function filterHora(Request $request, \App\Pedido $pedido)
    {
        set_time_limit(300);

        $pedido = $pedido->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $pedido->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $pedido->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $pedido->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        //$pedidos = $pedido->get();

        $pedidos = $pedido->select('id', 'created_at')
            ->get();

        if (count($pedidos) == 0) {
            return response()->json(['pedidos'=>[]], 200);
        }

        $horas_dia = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];

        for ($i=0; $i < count($pedidos) ; $i++) { 
            $fecha=$pedidos[$i]->created_at;
            $hora = date("H", strtotime($fecha));

            if ($hora == 00) {
                $horas_dia[0] = $horas_dia[0] + 1;
            }else if ($hora == 01) {
                $horas_dia[1] = $horas_dia[1] + 1;
            }else if ($hora == 02) {
                $horas_dia[2] = $horas_dia[2] + 1;
            }else if ($hora == 03) {
                $horas_dia[3] = $horas_dia[3] + 1;
            }else if ($hora == 04) {
                $horas_dia[4] = $horas_dia[4] + 1;
            }else if ($hora == 05) {
                $horas_dia[5] = $horas_dia[5] + 1;
            }else if ($hora == 06) {
                $horas_dia[6] = $horas_dia[6] + 1;
            }else if ($hora == 07) {
                $horas_dia[7] = $horas_dia[7] + 1;
            }else if ($hora == 08) {
                $horas_dia[8] = $horas_dia[8] + 1;
            }else if ($hora == 09) {
                $horas_dia[9] = $horas_dia[9] + 1;
            }else if ($hora == 10) {
                $horas_dia[10] = $horas_dia[10] + 1;
            }else if ($hora == 11) {
                $horas_dia[11] = $horas_dia[11] + 1;
            }else if ($hora == 12) {
                $horas_dia[12] = $horas_dia[12] + 1;
            }else if ($hora == 13) {
                $horas_dia[13] = $horas_dia[13] + 1;
            }else if ($hora == 14) {
                $horas_dia[14] = $horas_dia[14] + 1;
            }else if ($hora == 15) {
                $horas_dia[15] = $horas_dia[15] + 1;
            }else if ($hora == 16) {
                $horas_dia[16] = $horas_dia[16] + 1;
            }else if ($hora == 17) {
                $horas_dia[17] = $horas_dia[17] + 1;
            }else if ($hora == 18) {
                $horas_dia[18] = $horas_dia[18] + 1;
            }else if ($hora == 19) {
                $horas_dia[19] = $horas_dia[19] + 1;
            }else if ($hora == 20) {
                $horas_dia[20] = $horas_dia[20] + 1;
            }else if ($hora == 21) {
                $horas_dia[21] = $horas_dia[21] + 1;
            }else if ($hora == 22) {
                $horas_dia[22] = $horas_dia[22] + 1;
            }else if ($hora == 23) {
                $horas_dia[23] = $horas_dia[23] + 1;
            }
        }

        $aux = [];
        for ($i=0; $i < count($horas_dia) ; $i++) { 
            $data = (object) array( 'hora' => $i, 'count_solicitados' => $horas_dia[$i]);
            array_push($aux, $data);
        }

        return response()->json(['pedidos'=>$aux], 200);
    }


    /*Retorna los pedidos solicitados
     de la categoria comida, agrupados por hora
    filtrados por fecha*/
    public function filterHoraComida(Request $request, \App\Pedido $pedido)
    {
        set_time_limit(300);

        $pedido = $pedido->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $pedido->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $pedido->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $pedido->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        //$pedidos = $pedido->get();

        $pedidos = $pedido->select('id', 'created_at')
            ->with(['productos' => function ($query) {
                $query->select('productos.id', 'productos.nombre', 'productos.subcategoria_id');
            }])
            ->get();

        if (count($pedidos) == 0) {
            return response()->json(['pedidos'=>[]], 200);
        }

        $horas_dia = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];

        /*\App\Subcategoria::select('id','categoria_id')
            ->find($pedidos[0]->productos[0]->subcategoria_id);*/


        for ($i=0; $i < count($pedidos) ; $i++) { 
            $fecha=$pedidos[$i]->created_at;
            $hora = date("H", strtotime($fecha));

            for ($j=0; $j < count($pedidos[$i]->productos); $j++) { 
                $subCat = \App\Subcategoria::select('id','categoria_id')
                    ->find($pedidos[$i]->productos[$j]->subcategoria_id);

                //Verificar si el producto es de la categoria comida
                //NOTA: verificar el id real de la categoria comida
                if ($subCat->categoria_id == 1) {
                    if ($hora == 00) {
                        $horas_dia[0] = $horas_dia[0] + 1;
                    }else if ($hora == 01) {
                        $horas_dia[1] = $horas_dia[1] + 1;
                    }else if ($hora == 02) {
                        $horas_dia[2] = $horas_dia[2] + 1;
                    }else if ($hora == 03) {
                        $horas_dia[3] = $horas_dia[3] + 1;
                    }else if ($hora == 04) {
                        $horas_dia[4] = $horas_dia[4] + 1;
                    }else if ($hora == 05) {
                        $horas_dia[5] = $horas_dia[5] + 1;
                    }else if ($hora == 06) {
                        $horas_dia[6] = $horas_dia[6] + 1;
                    }else if ($hora == 07) {
                        $horas_dia[7] = $horas_dia[7] + 1;
                    }else if ($hora == 08) {
                        $horas_dia[8] = $horas_dia[8] + 1;
                    }else if ($hora == 09) {
                        $horas_dia[9] = $horas_dia[9] + 1;
                    }else if ($hora == 10) {
                        $horas_dia[10] = $horas_dia[10] + 1;
                    }else if ($hora == 11) {
                        $horas_dia[11] = $horas_dia[11] + 1;
                    }else if ($hora == 12) {
                        $horas_dia[12] = $horas_dia[12] + 1;
                    }else if ($hora == 13) {
                        $horas_dia[13] = $horas_dia[13] + 1;
                    }else if ($hora == 14) {
                        $horas_dia[14] = $horas_dia[14] + 1;
                    }else if ($hora == 15) {
                        $horas_dia[15] = $horas_dia[15] + 1;
                    }else if ($hora == 16) {
                        $horas_dia[16] = $horas_dia[16] + 1;
                    }else if ($hora == 17) {
                        $horas_dia[17] = $horas_dia[17] + 1;
                    }else if ($hora == 18) {
                        $horas_dia[18] = $horas_dia[18] + 1;
                    }else if ($hora == 19) {
                        $horas_dia[19] = $horas_dia[19] + 1;
                    }else if ($hora == 20) {
                        $horas_dia[20] = $horas_dia[20] + 1;
                    }else if ($hora == 21) {
                        $horas_dia[21] = $horas_dia[21] + 1;
                    }else if ($hora == 22) {
                        $horas_dia[22] = $horas_dia[22] + 1;
                    }else if ($hora == 23) {
                        $horas_dia[23] = $horas_dia[23] + 1;
                    }

                    break;
                }

            }
        }

        $aux = [];
        for ($i=0; $i < count($horas_dia) ; $i++) { 
            $data = (object) array( 'hora' => $i, 'count_solicitados' => $horas_dia[$i]);
            array_push($aux, $data);
        }

        return response()->json(['pedidos'=>$aux], 200);
    }

    /*Retorna los repartidores
    con los ksm recorridos
    filtrados por fecha*/
    public function filterRepartidores(Request $request, \App\Pedido $pedido)
    {
        set_time_limit(300);

        $pedido = $pedido->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $pedido->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $pedido->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $pedido->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        $pedido->where('estado',4);

        //$pedidos = $pedido->get();

        $pedidos = $pedido->select('id', 'estado', 'distancia', 'repartidor_id', 'repartidor_nom', 'created_at')
            ->get();

        if (count($pedidos) == 0) {
            return response()->json(['repartidores'=>[]], 200);
        }

        //cargar todos los repartidores
        $repartidores = \App\Repartidor::
            select('id', 'usuario_id')
            ->with(['usuario' => function ($query) {
                $query->select('id', 'nombre');
            }])
            ->get();

        if(count($repartidores) == 0){
            return response()->json(['error'=>'No existen repartidores.'], 404);          
        }else{

            for ($i=0; $i < count($repartidores) ; $i++) { 
                $repartidores[$i]->count_kms = 0;
                for ($j=0; $j < count($pedidos) ; $j++) { 
                    if ($pedidos[$j]->repartidor_id == $repartidores[$i]->id) {
                        $repartidores[$i]->count_kms = $repartidores[$i]->count_kms + $pedidos[$j]->distancia;
                    }
                }
            }

            //return $pedidos;
            return response()->json([/*'pedidos'=>$pedidos,*/ 'repartidores'=>$repartidores], 200);

        }
            
    }

    /*Retorna las calificaciones
    filtradas por fecha*/
    public function filterCalificaciones(Request $request, \App\Calificacion $calificacion)
    {
        set_time_limit(300);

        $calificacion = $calificacion->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $calificacion->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $calificacion->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $calificacion->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        $calificaciones = $calificacion
            ->with(['pedido' => function ($query) {
                $query->select('id', 'usuario_id', 'repartidor_id', 'repartidor_nom');
            }])
            ->get();

        return response()->json(['calificaciones'=>$calificaciones], 200);
     
    }

    /*Retorna los productos con el
    contador de solicitados
    filtrados por fecha*/
    public function filterProductos(Request $request, \App\Pedido $pedido)
    {
        set_time_limit(300);

        $pedido = $pedido->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $pedido->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $pedido->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $pedido->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        //$pedidos = $pedido->get();

        $pedidos = $pedido->select('id', 'created_at')
            ->with(['productos' => function ($query) {
                $query->select('productos.id', 'productos.nombre', 'productos.subcategoria_id');
            }])
            ->get();

        //cargar todas las subcategorias
        $subcategorias = \App\Subcategoria::
            select('id', 'nombre')->get();

        if(count($subcategorias) == 0){
            return response()->json(['error'=>'No existen subcategorias.'], 404);          
        }else{

            for ($i=0; $i < count($subcategorias) ; $i++) { 
                $subcategorias[$i]->count_solicitados = 0;
                for ($j=0; $j < count($pedidos) ; $j++) { 
                    for ($k=0; $k < count($pedidos[$j]->productos) ; $k++) { 
                        if ($pedidos[$j]->productos[$k]->subcategoria_id == $subcategorias[$i]->id) {
                            $subcategorias[$i]->count_solicitados = $subcategorias[$i]->count_solicitados + 1; 
                        }
                    }
                }
            }

            //return $pedidos;
            return response()->json([/*'pedidos'=>$pedidos,*/ 'subcategorias'=>$subcategorias], 200);
            
        }
    }


    /*Retorna los contadores del dia actual de un establecimiento_id*/
    public function contadoresEst($establecimiento_id)
    {
        //$dia_actual = date("d"); //j  Día del mes sin ceros iniciales 1 a 31
                                //d Día del mes, 2 dígitos con ceros iniciales  01 a 31
        ///$mes_actual = date("m");
        //$anio_actual = date("Y");

        $pedidos_curso = \App\Ruta::where('establecimiento_id',$establecimiento_id)
            ->where('despachado',1)
            ->whereHas('pedido', function ($query) {
                    $query->where('estado_pago','aprobado');
                })
            ->where(DB::raw('DAY(created_at)'),DB::raw('DAY(now())'))
            ->where(DB::raw('MONTH(created_at)'),DB::raw('MONTH(now())'))
            ->where(DB::raw('YEAR(created_at)'),DB::raw('YEAR(now())'))
            ->count();

        $pedidos_finalizados = \App\Ruta::where('establecimiento_id',$establecimiento_id)
            ->where('despachado',2)
            ->whereHas('pedido', function ($query) {
                    $query->where('estado_pago','aprobado');
                })
            ->where(DB::raw('DAY(created_at)'),DB::raw('DAY(now())'))
            ->where(DB::raw('MONTH(created_at)'),DB::raw('MONTH(now())'))
            ->where(DB::raw('YEAR(created_at)'),DB::raw('YEAR(now())'))
            ->count();

        $pagos_registrados = \App\Pago::where('establecimiento_id',$establecimiento_id)
            ->where(DB::raw('DAY(created_at)'),DB::raw('DAY(now())'))
            ->where(DB::raw('MONTH(created_at)'),DB::raw('MONTH(now())'))
            ->where(DB::raw('YEAR(created_at)'),DB::raw('YEAR(now())'))
            ->count();

        $rutas_pedidos = \App\Ruta::select('id', 'pedido_id', 'establecimiento_id')
            ->where('establecimiento_id',$establecimiento_id)
            ->whereHas('pedido', function ($query) {
                    $query->where('estado_pago','aprobado');
                })
            ->where(DB::raw('DAY(created_at)'),DB::raw('DAY(now())'))
            ->where(DB::raw('MONTH(created_at)'),DB::raw('MONTH(now())'))
            ->where(DB::raw('YEAR(created_at)'),DB::raw('YEAR(now())'))
            ->get();

        $aux = [];
        for ($i=0; $i < count($rutas_pedidos); $i++) { 
            array_push($aux, $rutas_pedidos[$i]->pedido_id);
        }

        $pedidos = \App\Pedido::select('id', 'usuario_id', 'repartidor_id')
            //->with('productos')
            ->with(['productos' => function ($query) use ($establecimiento_id){
                $query->where('establecimiento_id', $establecimiento_id);
            }])
            ->whereIn('id',$aux)
            ->get();

        $dinero_recaudado = 0;
        for ($i=0; $i <count($pedidos) ; $i++) { 
            for ($j=0; $j < count($pedidos[$i]->productos) ; $j++) { 
                $dinero_recaudado += $pedidos[$i]->productos[$j]->pivot->precio_unitario * 
                    $pedidos[$i]->productos[$j]->pivot->cantidad;
            }
        }

        return response()->json(['pedidos_curso'=>$pedidos_curso,
            'pedidos_finalizados'=>$pedidos_finalizados,
            'pagos_registrados'=>$pagos_registrados,
            'dinero_recaudado'=>$dinero_recaudado], 200);

    }

    /*Retorna los pedidos solicitados
    a un establecimiento_id
    agrupados por hora
    filtrados por fecha*/
    public function filterHoraEst(Request $request, \App\Ruta $pedido, $establecimiento_id)
    {
        set_time_limit(300);

        $pedido = $pedido->newQuery();

        if ($request->has('dia')) {
            if ($request->input('dia') != 'null' && $request->input('dia') != null && $request->input('dia') != '') {

                $pedido->where(DB::raw('DAY(created_at)'),$request->input('dia'));
            }
        }

        if ($request->has('mes')) {
            if ($request->input('mes') != 'null' && $request->input('mes') != null && $request->input('mes') != '') {

                $pedido->where(DB::raw('MONTH(created_at)'),$request->input('mes'));
            }
        }

        if ($request->has('anio')) {
            if ($request->input('anio') != 'null' && $request->input('anio') != null && $request->input('anio') != '') {

                $pedido->where(DB::raw('YEAR(created_at)'),$request->input('anio'));
            }
        }

        //$pedidos = $pedido->get();

        $pedidos = $pedido
            ->where('establecimiento_id',$establecimiento_id)
            ->select('id', 'created_at')
            ->get();

        if (count($pedidos) == 0) {
            return response()->json(['pedidos'=>[]], 200);
        }

        $horas_dia = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];

        for ($i=0; $i < count($pedidos) ; $i++) { 
            $fecha=$pedidos[$i]->created_at;
            $hora = date("H", strtotime($fecha));

            if ($hora == 00) {
                $horas_dia[0] = $horas_dia[0] + 1;
            }else if ($hora == 01) {
                $horas_dia[1] = $horas_dia[1] + 1;
            }else if ($hora == 02) {
                $horas_dia[2] = $horas_dia[2] + 1;
            }else if ($hora == 03) {
                $horas_dia[3] = $horas_dia[3] + 1;
            }else if ($hora == 04) {
                $horas_dia[4] = $horas_dia[4] + 1;
            }else if ($hora == 05) {
                $horas_dia[5] = $horas_dia[5] + 1;
            }else if ($hora == 06) {
                $horas_dia[6] = $horas_dia[6] + 1;
            }else if ($hora == 07) {
                $horas_dia[7] = $horas_dia[7] + 1;
            }else if ($hora == 08) {
                $horas_dia[8] = $horas_dia[8] + 1;
            }else if ($hora == 09) {
                $horas_dia[9] = $horas_dia[9] + 1;
            }else if ($hora == 10) {
                $horas_dia[10] = $horas_dia[10] + 1;
            }else if ($hora == 11) {
                $horas_dia[11] = $horas_dia[11] + 1;
            }else if ($hora == 12) {
                $horas_dia[12] = $horas_dia[12] + 1;
            }else if ($hora == 13) {
                $horas_dia[13] = $horas_dia[13] + 1;
            }else if ($hora == 14) {
                $horas_dia[14] = $horas_dia[14] + 1;
            }else if ($hora == 15) {
                $horas_dia[15] = $horas_dia[15] + 1;
            }else if ($hora == 16) {
                $horas_dia[16] = $horas_dia[16] + 1;
            }else if ($hora == 17) {
                $horas_dia[17] = $horas_dia[17] + 1;
            }else if ($hora == 18) {
                $horas_dia[18] = $horas_dia[18] + 1;
            }else if ($hora == 19) {
                $horas_dia[19] = $horas_dia[19] + 1;
            }else if ($hora == 20) {
                $horas_dia[20] = $horas_dia[20] + 1;
            }else if ($hora == 21) {
                $horas_dia[21] = $horas_dia[21] + 1;
            }else if ($hora == 22) {
                $horas_dia[22] = $horas_dia[22] + 1;
            }else if ($hora == 23) {
                $horas_dia[23] = $horas_dia[23] + 1;
            }
        }

        $aux = [];
        for ($i=0; $i < count($horas_dia) ; $i++) { 
            $data = (object) array( 'hora' => $i, 'count_solicitados' => $horas_dia[$i]);
            array_push($aux, $data);
        }

        return response()->json(['pedidos'=>$aux], 200);
    }
}
