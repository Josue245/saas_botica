<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $productos = Producto::with('categoria')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo_barras' => $p->codigo_barras,
                'principio_activo' => $p->principio_activo,
                'precio' => (float) $p->precio_venta,
                'stock' => (int) $p->stock,
                'categoria_id' => $p->categoria_id,
                'categoria' => $p->categoria?->nombre,
                'requiere_receta' => (bool) $p->requiere_receta,
            ]);

        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);
        $clientes = Cliente::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'numero_documento']);

        return view('pos.index', compact('productos', 'categorias', 'clientes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'items_json' => 'required|string',
            'metodo_pago' => 'required|string|in:Efectivo,Tarjeta,Yape,Plin,Transferencia',
            'tipo_comprobante' => 'required|string|in:ticket,boleta,factura',
            'cliente_id' => 'nullable|exists:clientes,id',
            'descuento' => 'nullable|numeric|min:0',
        ]);

        $items = json_decode($request->input('items_json'), true);

        if (! is_array($items) || count($items) === 0) {
            return back()->with('error', 'El carrito está vacío.');
        }

        $igvPct = (float) Configuracion::valor('igv_porcentaje', 18);
        $divisor = 1 + ($igvPct / 100);
        $serieBoleta = Configuracion::valor('serie_boleta', 'B001');
        $serieFactura = Configuracion::valor('serie_factura', 'F001');

        try {
            $venta = DB::transaction(function () use ($request, $items, $divisor, $serieBoleta, $serieFactura) {
                $subtotalBruto = 0;
                $lineas = [];

                foreach ($items as $item) {
                    $producto = Producto::lockForUpdate()->find($item['id'] ?? 0);
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));

                    if (! $producto) {
                        throw new \RuntimeException('Uno de los productos ya no existe.');
                    }
                    if ($producto->stock < $cantidad) {
                        throw new \RuntimeException("Stock insuficiente para “{$producto->nombre}” (disponible: {$producto->stock}).");
                    }

                    $precio = (float) $producto->precio_venta;
                    $sub = round($precio * $cantidad, 2);
                    $subtotalBruto += $sub;

                    $lineas[] = ['producto' => $producto, 'cantidad' => $cantidad, 'precio' => $precio, 'sub' => $sub];
                }

                $descuento = round((float) $request->input('descuento', 0), 2);
                $total = max(0, round($subtotalBruto - $descuento, 2));
                $base = round($total / $divisor, 2);
                $igv = round($total - $base, 2);

                $correlativo = Venta::count() + 1;
                $serie = $request->input('tipo_comprobante') === 'factura' ? $serieFactura : $serieBoleta;

                $venta = Venta::create([
                    'numero_comprobante' => $serie . '-' . str_pad((string) $correlativo, 6, '0', STR_PAD_LEFT),
                    'tipo_comprobante' => $request->input('tipo_comprobante'),
                    'cliente_id' => $request->input('cliente_id'),
                    'user_id' => $request->user()->id,
                    'subtotal' => $base,
                    'igv' => $igv,
                    'descuento' => $descuento,
                    'total' => $total,
                    'metodo_pago' => $request->input('metodo_pago'),
                    'estado' => 'pagada',
                    'con_receta' => $request->boolean('con_receta'),
                ]);

                foreach ($lineas as $l) {
                    VentaDetalle::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $l['producto']->id,
                        'descripcion' => $l['producto']->nombre,
                        'cantidad' => $l['cantidad'],
                        'precio_unitario' => $l['precio'],
                        'subtotal' => $l['sub'],
                    ]);
                    $l['producto']->decrement('stock', $l['cantidad']);
                }

                return $venta;
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pos.ticket', $venta)
            ->with('ok', "Venta {$venta->numero_comprobante} registrada por S/ " . number_format($venta->total, 2) . '.');
    }

    public function ticket(Venta $venta): View
    {
        $venta->load(['detalles.producto', 'cliente', 'usuario']);

        return view('pos.ticket', [
            'venta' => $venta,
            'config' => Configuracion::todas(),
        ]);
    }
}
