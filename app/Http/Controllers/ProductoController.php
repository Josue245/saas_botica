<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q', ''));
        $categoriaId = $request->query('categoria');
        $filtro = $request->query('filtro'); // stock_bajo | por_vencer | vencido

        $productos = Producto::with(['categoria', 'proveedor'])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($w) use ($buscar) {
                    $w->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('codigo_barras', 'like', "%{$buscar}%")
                        ->orWhere('principio_activo', 'like', "%{$buscar}%");
                });
            })
            ->when($categoriaId, fn ($q) => $q->where('categoria_id', $categoriaId))
            ->when($filtro === 'stock_bajo', fn ($q) => $q->whereColumn('stock', '<=', 'stock_minimo'))
            ->when($filtro === 'por_vencer', fn ($q) => $q->whereNotNull('fecha_vencimiento')
                ->whereBetween('fecha_vencimiento', [now()->startOfDay(), now()->addDays(60)]))
            ->when($filtro === 'vencido', fn ($q) => $q->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', now()))
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias', 'buscar', 'categoriaId', 'filtro'));
    }

    public function create(): View
    {
        return view('productos.create', [
            'producto' => new Producto(['stock_minimo' => 10, 'activo' => true]),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'proveedores' => Proveedor::orderBy('razon_social')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        Producto::create($data);

        return redirect()->route('productos.index')->with('ok', 'Producto registrado correctamente.');
    }

    public function edit(Producto $producto): View
    {
        return view('productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'proveedores' => Proveedor::orderBy('razon_social')->get(),
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $data = $this->validateData($request, $producto);
        $producto->update($data);

        return redirect()->route('productos.index')->with('ok', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->route('productos.index')->with('ok', 'Producto eliminado.');
    }

    private function validateData(Request $request, ?Producto $producto = null): array
    {
        $id = $producto?->id ?? 'NULL';

        $validated = $request->validate([
            'nombre' => 'required|string|max:160',
            'codigo_barras' => "nullable|string|max:60|unique:productos,codigo_barras,{$id}",
            'principio_activo' => 'nullable|string|max:160',
            'presentacion' => 'nullable|string|max:80',
            'concentracion' => 'nullable|string|max:60',
            'categoria_id' => 'nullable|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'laboratorio' => 'nullable|string|max:120',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0|gte:precio_compra',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'lote' => 'nullable|string|max:60',
            'fecha_vencimiento' => 'nullable|date',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'codigo_barras.unique' => 'Ya existe un producto con ese código de barras.',
            'precio_venta.gte' => 'El precio de venta no puede ser menor al de compra.',
            'precio_compra.required' => 'Indica el precio de compra.',
            'precio_venta.required' => 'Indica el precio de venta.',
            'stock.required' => 'Indica el stock.',
        ]);

        $validated['requiere_receta'] = $request->boolean('requiere_receta');
        $validated['controlado'] = $request->boolean('controlado');
        $validated['activo'] = $request->boolean('activo');

        return $validated;
    }
}
