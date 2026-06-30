<?php

namespace App\Http\Controllers;

use App\Jobs\ComprobanteSunatJob;
use App\Models\ComprobanteSunat;
use App\Models\Correlativo;
use App\Models\Venta;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacturacionController extends Controller
{
    public function __construct(private TenantManager $manager) {}

    /**
     * Listado de comprobantes electrónicos del tenant.
     */
    public function index(Request $request): View
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        // Verificar que el plan permite facturación electrónica
        $habilitado = $tenant->puedeUsar('facturacion_electronica');

        $comprobantes = ComprobanteSunat::where('tenant_id', $tenant->id)
            ->with(['venta', 'sucursal'])
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'aceptados'  => ComprobanteSunat::where('tenant_id', $tenant->id)->where('estado', 'aceptado')->count(),
            'pendientes' => ComprobanteSunat::where('tenant_id', $tenant->id)->where('estado', 'pendiente')->count(),
            'rechazados' => ComprobanteSunat::where('tenant_id', $tenant->id)->where('estado', 'rechazado')->count(),
        ];

        return view('facturacion.index', compact('comprobantes', 'stats', 'habilitado', 'tenant'));
    }

    /**
     * Emitir comprobante electrónico para una venta existente.
     */
    public function emitir(Request $request, Venta $venta): RedirectResponse
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        if (!$tenant->puedeUsar('facturacion_electronica')) {
            return back()->with('error',
                'Tu plan no incluye facturación electrónica. ' .
                '<a href="' . route('billing.index') . '" class="underline">Actualizar a Pro →</a>'
            );
        }

        // Verificar que la venta pertenece al tenant
        if ((int) $venta->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        // Validar: factura solo para clientes con RUC
        if ($tipo === "factura") {
                return back()->with("error", "Las facturas solo se pueden emitir a clientes con RUC. Use boleta para clientes con DNI.");
            }
        }

        // Verificar que no tiene comprobante ya emitido
        if (ComprobanteSunat::where('venta_id', $venta->id)->exists()) {
            return back()->with('error', 'Esta venta ya tiene un comprobante electrónico emitido.');
        }

        // Usar tipo del request si viene, sino usar el de la venta
        $tipoRequest = request()->input('tipo');
        $tipo = in_array($tipoRequest, ['boleta', 'factura']) ? $tipoRequest : ($venta->tipo_comprobante === 'factura' ? 'factura' : 'boleta');
        $serie = $tipo === 'factura'
            ? ($venta->sucursal?->serie_factura ?? 'F001')
            : ($venta->sucursal?->serie_boleta ?? 'B001');

        // Obtener siguiente correlativo atómico
        $sucursalId = $venta->sucursal_id ?? auth()->user()->sucursal_id;
        $numero = Correlativo::siguiente($tenant->id, $sucursalId, $tipo, $serie);
        $correlativo = (int) explode('-', $numero)[1];

        // Crear registro de comprobante
        $comprobante = ComprobanteSunat::create([
            'tenant_id'   => $tenant->id,
            'venta_id'    => $venta->id,
            'sucursal_id' => $sucursalId,
            'tipo'        => $tipo,
            'serie'       => $serie,
            'correlativo' => $correlativo,
            'numero'      => $numero,
            'estado'      => 'pendiente',
        ]);

        // Despachar job de envío a SUNAT (asíncrono)
        ComprobanteSunatJob::dispatch($comprobante->id);

        return back()->with('ok',
            "Comprobante {$numero} generado y enviado a SUNAT. El estado se actualizará en breve."
        );
    }

    /**
     * Reenviar comprobante rechazado o pendiente.
     */
    public function reenviar(ComprobanteSunat $comprobante): RedirectResponse
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        if ((int) $comprobante->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        if (!$comprobante->puedeReenviar()) {
            return back()->with('error', 'Este comprobante no puede reenviarse (ya fue aceptado).');
        }

        $comprobante->update(['estado' => 'pendiente', 'error_mensaje' => null]);
        ComprobanteSunatJob::dispatch($comprobante->id);

        return back()->with('ok', "Comprobante {$comprobante->numero} reenviado a SUNAT.");
    }

    /**
     * Ver XML del comprobante.
     */
    public function verXml(ComprobanteSunat $comprobante): \Illuminate\Http\Response
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        if ((int) $comprobante->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        return response($comprobante->xml_contenido ?? '<!-- Sin XML generado -->', 200)
            ->header('Content-Type', 'application/xml');
    }
}
