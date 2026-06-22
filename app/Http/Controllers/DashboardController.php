<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $hoy = Carbon::today();
        $listo = Schema::hasTable('ventas') && Schema::hasTable('venta_detalles');

        // ----- Tarjetas -----
        $ingresosHoy = $listo
            ? (float) DB::table('ventas')->whereDate('created_at', $hoy)->where('estado', 'pagada')->sum('total')
            : 0;

        $ventasHoy = $listo
            ? DB::table('ventas')->whereDate('created_at', $hoy)->count()
            : 0;

        $clientes = Schema::hasTable('clientes') ? DB::table('clientes')->count() : 0;

        // Alertas: stock bajo + por vencer (<= 60 días) + vencidos
        $alertasCount = 0;
        if (Schema::hasTable('productos')) {
            $stockBajo = DB::table('productos')->whereColumn('stock', '<=', 'stock_minimo')->count();
            $porVencer = DB::table('productos')
                ->whereNotNull('fecha_vencimiento')
                ->whereBetween('fecha_vencimiento', [$hoy, (clone $hoy)->addDays(60)])
                ->count();
            $vencidos = DB::table('productos')
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', $hoy)
                ->count();
            $alertasCount = $stockBajo + $porVencer + $vencidos;
        }

        // ----- Ingresos últimos 7 días (barras) -----
        $labels7 = [];
        $data7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = (clone $hoy)->subDays($i);
            $labels7[] = $d->isoFormat('ddd D');
            $data7[] = $listo
                ? (float) DB::table('ventas')->whereDate('created_at', $d)->where('estado', 'pagada')->sum('total')
                : 0;
        }

        // ----- Top 5 productos más vendidos (unidades) -----
        $topProductos = collect();
        if ($listo && Schema::hasTable('productos')) {
            $topProductos = DB::table('venta_detalles')
                ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
                ->select('productos.nombre', DB::raw('SUM(venta_detalles.cantidad) as unidades'))
                ->groupBy('productos.id', 'productos.nombre')
                ->orderByDesc('unidades')
                ->limit(5)
                ->get();
        }

        // ----- Top 5 categorías por ingresos (pie) -----
        $topCategorias = collect();
        if ($listo && Schema::hasTable('productos') && Schema::hasTable('categorias')) {
            $topCategorias = DB::table('venta_detalles')
                ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
                ->join('categorias', 'categorias.id', '=', 'productos.categoria_id')
                ->select('categorias.nombre', DB::raw('SUM(venta_detalles.subtotal) as ingresos'))
                ->groupBy('categorias.id', 'categorias.nombre')
                ->orderByDesc('ingresos')
                ->limit(5)
                ->get();
        }

        // Distribución de ingresos por método de pago (donut)
        $distribucion = collect();
        if ($listo) {
            $distribucion = DB::table('ventas')
                ->select('metodo_pago', DB::raw('SUM(total) as monto'))
                ->where('estado', 'pagada')
                ->groupBy('metodo_pago')
                ->get();
        }

        return view('dashboard.index', [
            'ingresosHoy'   => $ingresosHoy,
            'ventasHoy'     => $ventasHoy,
            'clientes'      => $clientes,
            'alertasCount'  => $alertasCount,
            'labels7'       => $labels7,
            'data7'         => $data7,
            'topProductos'  => $topProductos,
            'topCategorias' => $topCategorias,
            'distribucion'  => $distribucion,
        ]);
    }
}
