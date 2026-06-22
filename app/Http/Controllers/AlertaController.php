<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function index(): View
    {
        $hoy = now()->startOfDay();

        $vencidos = Producto::with('categoria')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento')
            ->get();

        $porVencer = Producto::with('categoria')
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$hoy, (clone $hoy)->addDays(60)])
            ->orderBy('fecha_vencimiento')
            ->get();

        $stockBajo = Producto::with('categoria')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->get();

        return view('alertas.index', compact('vencidos', 'porVencer', 'stockBajo', 'hoy'));
    }
}
