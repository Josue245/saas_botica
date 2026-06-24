<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockSucursalController extends Controller
{
    public function __construct(private TenantManager $manager) {}

    /**
     * Ver stock consolidado de todas las sucursales.
     */
    public function index(): View
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        $stock = DB::table('stock_sucursales')
            ->join('productos', 'productos.id', '=', 'stock_sucursales.producto_id')
            ->join('sucursales', 'sucursales.id', '=', 'stock_sucursales.sucursal_id')
            ->where('stock_sucursales.tenant_id', $tenant->id)
            ->select(
                'productos.nombre as producto',
                'sucursales.nombre as sucursal',
                'stock_sucursales.stock',
                'stock_sucursales.stock_minimo',
                DB::raw('IF(stock_sucursales.stock <= stock_sucursales.stock_minimo, 1, 0) as alerta')
            )
            ->orderBy('productos.nombre')
            ->orderBy('sucursales.nombre')
            ->paginate(20);

        $sucursales = Sucursal::where('tenant_id', $tenant->id)->where('activo', true)->get();

        return view('sucursales.stock', compact('stock', 'sucursales'));
    }

    /**
     * Transferir stock de una sucursal a otra.
     */
    public function transferir(Request $request): RedirectResponse
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        $data = $request->validate([
            'producto_id'    => 'required|integer|exists:productos,id',
            'sucursal_origen' => 'required|integer|exists:sucursales,id',
            'sucursal_destino'=> 'required|integer|exists:sucursales,id|different:sucursal_origen',
            'cantidad'        => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($data, $tenant) {
                // Verificar stock en origen
                $stockOrigen = StockSucursal::lockForUpdate()
                    ->where('tenant_id', $tenant->id)
                    ->where('sucursal_id', $data['sucursal_origen'])
                    ->where('producto_id', $data['producto_id'])
                    ->first();

                if (!$stockOrigen || $stockOrigen->stock < $data['cantidad']) {
                    throw new \RuntimeException('Stock insuficiente en la sucursal origen.');
                }

                // Descontar en origen
                $stockOrigen->decrement('stock', $data['cantidad']);

                // Incrementar en destino (crear si no existe)
                StockSucursal::updateOrCreate(
                    [
                        'tenant_id'   => $tenant->id,
                        'sucursal_id' => $data['sucursal_destino'],
                        'producto_id' => $data['producto_id'],
                    ],
                    ['stock' => DB::raw("stock + {$data['cantidad']}")]
                );
            });

            return back()->with('ok', "Transferencia de {$data['cantidad']} unidades realizada correctamente.");

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Poblar stock_sucursales desde productos.stock (migración inicial).
     * Solo para el primer tenant que ya tenía datos.
     */
    public function poblarDesdeProductos(): RedirectResponse
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;
        $sucursal = Sucursal::where('tenant_id', $tenant->id)->orderBy('id')->first();

        if (!$sucursal) {
            return back()->with('error', 'No hay sucursales configuradas.');
        }

        $productos = Producto::where('tenant_id', $tenant->id)->get();
        $creados = 0;

        foreach ($productos as $producto) {
            $existe = StockSucursal::where('tenant_id', $tenant->id)
                ->where('sucursal_id', $sucursal->id)
                ->where('producto_id', $producto->id)
                ->exists();

            if (!$existe) {
                StockSucursal::create([
                    'tenant_id'   => $tenant->id,
                    'sucursal_id' => $sucursal->id,
                    'producto_id' => $producto->id,
                    'stock'       => $producto->stock,
                    'stock_minimo'=> $producto->stock_minimo,
                ]);
                $creados++;
            }
        }

        return back()->with('ok', "{$creados} productos migrados a stock por sucursal.");
    }
}
