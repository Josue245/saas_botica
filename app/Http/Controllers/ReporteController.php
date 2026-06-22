<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    private array $tiposValidos = ['ventas', 'productos', 'inventario'];

    private function rango(Request $request): array
    {
        $desde = $request->query('desde') ?: now()->startOfMonth()->toDateString();
        $hasta = $request->query('hasta') ?: now()->toDateString();

        return [$desde, $hasta];
    }

    public function index(Request $request): View
    {
        $tipo = in_array($request->query('tipo'), $this->tiposValidos, true) ? $request->query('tipo') : 'ventas';
        [$desde, $hasta] = $this->rango($request);

        $data = match ($tipo) {
            'productos' => $this->dataProductos($desde, $hasta),
            'inventario' => $this->dataInventario(),
            default => $this->dataVentas($desde, $hasta),
        };

        return view('reportes.index', array_merge($data, [
            'tipo' => $tipo,
            'desde' => $desde,
            'hasta' => $hasta,
        ]));
    }

    public function exportar(Request $request): StreamedResponse
    {
        $tipo = in_array($request->query('tipo'), $this->tiposValidos, true) ? $request->query('tipo') : 'ventas';
        [$desde, $hasta] = $this->rango($request);

        [$headers, $filas] = match ($tipo) {
            'productos' => $this->csvProductos($desde, $hasta),
            'inventario' => $this->csvInventario(),
            default => $this->csvVentas($desde, $hasta),
        };

        $nombre = "reporte_{$tipo}_{$desde}_a_{$hasta}.csv";

        return response()->streamDownload(function () use ($headers, $filas) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF"); // BOM para acentos en Excel
            fputcsv($out, $headers);
            foreach ($filas as $fila) {
                fputcsv($out, $fila);
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---------- VENTAS ----------
    private function ventasQuery(string $desde, string $hasta)
    {
        return Venta::where('estado', 'pagada')
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta);
    }

    private function dataVentas(string $desde, string $hasta): array
    {
        $total = (float) (clone $this->ventasQuery($desde, $hasta))->sum('total');
        $igv = (float) (clone $this->ventasQuery($desde, $hasta))->sum('igv');
        $num = (clone $this->ventasQuery($desde, $hasta))->count();
        $ticket = $num > 0 ? $total / $num : 0;

        $porDia = (clone $this->ventasQuery($desde, $hasta))
            ->select(DB::raw('DATE(created_at) as dia'), DB::raw('SUM(total) as monto'))
            ->groupBy('dia')->orderBy('dia')->get();

        $porMetodo = (clone $this->ventasQuery($desde, $hasta))
            ->select('metodo_pago', DB::raw('SUM(total) as monto'), DB::raw('COUNT(*) as n'))
            ->groupBy('metodo_pago')->orderByDesc('monto')->get();

        return [
            'kpis' => ['total' => $total, 'igv' => $igv, 'num' => $num, 'ticket' => $ticket],
            'porDia' => $porDia,
            'porMetodo' => $porMetodo,
            'chartLabels' => $porDia->map(fn ($r) => Carbon::parse($r->dia)->format('d/m'))->values(),
            'chartData' => $porDia->map(fn ($r) => (float) $r->monto)->values(),
        ];
    }

    private function csvVentas(string $desde, string $hasta): array
    {
        $ventas = (clone $this->ventasQuery($desde, $hasta))
            ->with(['cliente', 'usuario'])->orderBy('created_at')->get();

        $filas = $ventas->map(fn ($v) => [
            $v->numero_comprobante,
            $v->created_at->format('d/m/Y H:i'),
            $v->cliente?->nombre ?? 'Cliente Varios',
            $v->usuario?->name ?? '',
            $v->metodo_pago,
            number_format($v->subtotal, 2, '.', ''),
            number_format($v->igv, 2, '.', ''),
            number_format($v->total, 2, '.', ''),
        ])->all();

        return [['Comprobante', 'Fecha', 'Cliente', 'Cajero', 'Método', 'Subtotal', 'IGV', 'Total'], $filas];
    }

    // ---------- PRODUCTOS ----------
    private function productosQuery(string $desde, string $hasta)
    {
        return DB::table('venta_detalles')
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->where('ventas.estado', 'pagada')
            ->whereDate('ventas.created_at', '>=', $desde)
            ->whereDate('ventas.created_at', '<=', $hasta)
            ->select(
                'productos.nombre',
                DB::raw('SUM(venta_detalles.cantidad) as unidades'),
                DB::raw('SUM(venta_detalles.subtotal) as ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('unidades');
    }

    private function dataProductos(string $desde, string $hasta): array
    {
        $top = (clone $this->productosQuery($desde, $hasta))->limit(20)->get();

        return [
            'topProductos' => $top,
            'chartLabels' => $top->take(8)->map(fn ($r) => $r->nombre)->values(),
            'chartData' => $top->take(8)->map(fn ($r) => (float) $r->unidades)->values(),
        ];
    }

    private function csvProductos(string $desde, string $hasta): array
    {
        $rows = (clone $this->productosQuery($desde, $hasta))->get();
        $filas = $rows->map(fn ($r) => [$r->nombre, (int) $r->unidades, number_format($r->ingresos, 2, '.', '')])->all();

        return [['Producto', 'Unidades vendidas', 'Ingresos'], $filas];
    }

    // ---------- INVENTARIO ----------
    private function dataInventario(): array
    {
        $productos = Producto::with('categoria')->orderBy('nombre')->get();
        $valorCosto = (float) Producto::sum(DB::raw('stock * precio_compra'));
        $valorVenta = (float) Producto::sum(DB::raw('stock * precio_venta'));
        $unidades = (int) Producto::sum('stock');

        return [
            'invProductos' => $productos,
            'invKpis' => ['costo' => $valorCosto, 'venta' => $valorVenta, 'unidades' => $unidades],
        ];
    }

    private function csvInventario(): array
    {
        $rows = Producto::with('categoria')->orderBy('nombre')->get();
        $filas = $rows->map(fn ($p) => [
            $p->nombre,
            $p->categoria?->nombre ?? '',
            (int) $p->stock,
            number_format($p->precio_compra, 2, '.', ''),
            number_format($p->precio_venta, 2, '.', ''),
            number_format($p->stock * $p->precio_venta, 2, '.', ''),
        ])->all();

        return [['Producto', 'Categoría', 'Stock', 'P. Compra', 'P. Venta', 'Valor (venta)'], $filas];
    }
}
