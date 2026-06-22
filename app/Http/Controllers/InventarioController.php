<?php

namespace App\Http\Controllers;

use App\Models\AjusteInventario;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q', ''));
        $categoriaId = $request->query('categoria');

        $productos = Producto::with('categoria')
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('codigo_barras', 'like', "%{$buscar}%"))
            ->when($categoriaId, fn ($q) => $q->where('categoria_id', $categoriaId))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        // Valorización total del inventario (todos los productos activos)
        $valorCosto = (float) Producto::sum(DB::raw('stock * precio_compra'));
        $valorVenta = (float) Producto::sum(DB::raw('stock * precio_venta'));
        $unidades = (int) Producto::sum('stock');

        $categorias = Categoria::orderBy('nombre')->get();

        return view('inventario.index', compact('productos', 'categorias', 'buscar', 'categoriaId', 'valorCosto', 'valorVenta', 'unidades'));
    }

    public function lotes(): View
    {
        $hoy = now()->startOfDay();

        $productos = Producto::with('categoria')
            ->whereNotNull('fecha_vencimiento')
            ->orderBy('fecha_vencimiento') // FEFO: primero en vencer
            ->paginate(20);

        return view('inventario.lotes', compact('productos', 'hoy'));
    }

    public function ajustes(Request $request): View
    {
        $productos = Producto::orderBy('nombre')->get(['id', 'nombre', 'stock']);
        $ajustes = AjusteInventario::with(['producto', 'usuario'])
            ->latest()
            ->paginate(15);

        return view('inventario.ajustes', compact('productos', 'ajustes'));
    }

    public function guardarAjuste(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo' => 'required|in:ingreso,salida,conteo',
            'cantidad' => 'required|integer|min:0',
            'motivo' => 'nullable|string|max:200',
        ], [
            'producto_id.required' => 'Selecciona un producto.',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
        ]);

        try {
            DB::transaction(function () use ($data, $request) {
                $producto = Producto::lockForUpdate()->findOrFail($data['producto_id']);
                $anterior = (int) $producto->stock;

                $nuevo = match ($data['tipo']) {
                    'ingreso' => $anterior + (int) $data['cantidad'],
                    'salida' => $anterior - (int) $data['cantidad'],
                    'conteo' => (int) $data['cantidad'],
                };

                if ($nuevo < 0) {
                    throw new \RuntimeException('El ajuste dejaría el stock en negativo.');
                }

                $producto->update(['stock' => $nuevo]);

                AjusteInventario::create([
                    'producto_id' => $producto->id,
                    'user_id' => $request->user()->id,
                    'tipo' => $data['tipo'],
                    'cantidad' => (int) $data['cantidad'],
                    'stock_anterior' => $anterior,
                    'stock_nuevo' => $nuevo,
                    'motivo' => $data['motivo'] ?? null,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('inventario.ajustes')->with('ok', 'Ajuste de stock registrado.');
    }
}
