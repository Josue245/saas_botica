<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q', ''));

        $compras = Compra::with(['proveedor', 'usuario'])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('numero_documento', 'like', "%{$buscar}%")
                    ->orWhereHas('proveedor', fn ($p) => $p->where('razon_social', 'like', "%{$buscar}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalMes = (float) Compra::where('estado', 'recibida')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $porPagar = (float) Compra::where('estado', 'recibida')
            ->where('estado_pago', 'pendiente')
            ->sum('total');

        return view('compras.index', compact('compras', 'buscar', 'totalMes', 'porPagar'));
    }

    public function create(): View
    {
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get(['id', 'razon_social']);
        $productos = Producto::orderBy('nombre')->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'precio_compra' => (float) $p->precio_compra,
                'stock' => (int) $p->stock,
            ]);

        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'numero_documento' => 'nullable|string|max:60',
            'fecha' => 'nullable|date',
            'estado_pago' => 'required|in:pendiente,pagada',
            'observacion' => 'nullable|string|max:200',
            'items_json' => 'required|string',
        ], [
            'proveedor_id.required' => 'Selecciona un proveedor.',
        ]);

        $items = json_decode($request->input('items_json'), true);

        if (! is_array($items) || count($items) === 0) {
            return back()->withInput()->with('error', 'Agrega al menos un producto a la compra.');
        }

        try {
            $compra = DB::transaction(function () use ($request, $items) {
                $subtotal = 0;
                $lineas = [];

                foreach ($items as $item) {
                    $producto = Producto::lockForUpdate()->find($item['id'] ?? 0);
                    if (! $producto) {
                        throw new \RuntimeException('Uno de los productos seleccionados ya no existe.');
                    }
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
                    $precio = max(0, round((float) ($item['precio_compra'] ?? 0), 2));
                    $sub = round($cantidad * $precio, 2);
                    $subtotal += $sub;

                    $lineas[] = [
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'sub' => $sub,
                        'lote' => $item['lote'] ?? null,
                        'venc' => ! empty($item['fecha_vencimiento']) ? $item['fecha_vencimiento'] : null,
                    ];
                }

                $igv = round($subtotal * 0.18, 2);
                $total = round($subtotal + $igv, 2);
                $correlativo = Compra::count() + 1;

                $compra = Compra::create([
                    'numero_documento' => $request->input('numero_documento') ?: ('OC-' . str_pad((string) $correlativo, 6, '0', STR_PAD_LEFT)),
                    'proveedor_id' => $request->input('proveedor_id'),
                    'user_id' => $request->user()->id,
                    'fecha' => $request->input('fecha') ?: now()->toDateString(),
                    'subtotal' => $subtotal,
                    'igv' => $igv,
                    'total' => $total,
                    'estado' => 'recibida',
                    'estado_pago' => $request->input('estado_pago'),
                    'observacion' => $request->input('observacion'),
                ]);

                foreach ($lineas as $l) {
                    CompraDetalle::create([
                        'compra_id' => $compra->id,
                        'producto_id' => $l['producto']->id,
                        'descripcion' => $l['producto']->nombre,
                        'cantidad' => $l['cantidad'],
                        'precio_compra' => $l['precio'],
                        'subtotal' => $l['sub'],
                        'lote' => $l['lote'],
                        'fecha_vencimiento' => $l['venc'],
                    ]);

                    // Ingreso a inventario + actualización de costo/lote/vencimiento
                    $cambios = ['precio_compra' => $l['precio']];
                    if ($l['lote']) {
                        $cambios['lote'] = $l['lote'];
                    }
                    if ($l['venc']) {
                        $cambios['fecha_vencimiento'] = $l['venc'];
                    }
                    $l['producto']->update($cambios);
                    $l['producto']->increment('stock', $l['cantidad']);
                }

                return $compra;
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('compras.show', $compra)
            ->with('ok', "Compra {$compra->numero_documento} registrada e ingresada al inventario.");
    }

    public function show(Compra $compra): View
    {
        $compra->load(['detalles.producto', 'proveedor', 'usuario']);

        return view('compras.show', compact('compra'));
    }
}
