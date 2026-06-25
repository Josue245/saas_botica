<?php

namespace App\Services\Sunat;

use App\Models\ComprobanteSunat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NubefactService — envía comprobantes electrónicos al OSE Nubefact.
 *
 * Documentación: https://www.nubefact.com/api-doc/
 * Ambiente de pruebas: https://demo-facturacion.nubefact.com
 * Ambiente producción: https://facturacion.nubefact.com
 *
 * Pasos para obtener credenciales:
 * 1. Registro gratuito en nubefact.com
 * 2. Panel → Empresa → Token API
 * 3. Para homologación SUNAT: Panel → Homologación
 */
class NubefactService
{
    private string $token;
    private string $baseUrl;
    private string $ruc;

    public function __construct()
    {
        $this->token   = config('services.nubefact.token', '');
        $this->ruc     = config('services.nubefact.ruc', '');
        $this->baseUrl = config('services.nubefact.url',
            'https://demo-facturacion.nubefact.com/api/v1'  // demo por defecto
        );
    }

    /**
     * Envía el comprobante a Nubefact y retorna el resultado.
     */
    public function enviar(ComprobanteSunat $comprobante, string $xml): array
    {
        $tipoDoc = $comprobante->tipo === 'factura' ? '01' : '03';

        $payload = [
            'operacion'             => 'generar_comprobante',
            'tipo_de_comprobante'   => (int) $tipoDoc,
            'serie'                 => $comprobante->serie,
            'numero'                => $comprobante->correlativo,
            'sunat_transaction'     => 1,
            'cliente_tipo_de_documento' => 1,
            'cliente_numero_de_documento' => '00000000',
            'cliente_denominacion'  => 'CLIENTE VARIOS',
            'cliente_direccion'     => '-',
            'cliente_email'         => '',
            'cliente_email_1'       => '',
            'cliente_email_2'       => '',
            'fecha_de_emision'      => $comprobante->created_at->format('d-m-Y'),
            'fecha_de_vencimiento'  => '',
            'moneda'                => 1, // 1=PEN
            'porcentaje_de_igv'     => 18.00,
            'descuento_global'      => 0,
            'total_descuento'       => 0,
            'total_anticipo'        => 0,
            'total_gravada'         => (float) $comprobante->venta->subtotal,
            'total_inafecta'        => 0,
            'total_exonerada'       => 0,
            'total_igv'             => (float) $comprobante->venta->igv,
            'total_gratuita'        => 0,
            'total_otros_cargos'    => 0,
            'total'                 => (float) $comprobante->venta->total,
            'percepcion_tipo'       => '',
            'percepcion_base_imponible' => 0,
            'total_percepcion'      => 0,
            'total_incluido_percepcion' => 0,
            'detraccion'            => false,
            'observaciones'         => '',
            'documento_que_se_modifica_tipo' => '',
            'documento_que_se_modifica_serie' => '',
            'documento_que_se_modifica_numero' => '',
            'tipo_de_nota_de_credito' => '',
            'tipo_de_nota_de_debito'  => '',
            'enviar_automaticamente_a_la_sunat' => true,
            'enviar_automaticamente_al_cliente' => false,
            'codigo_unico'          => $comprobante->numero,
            'condiciones_de_pago'   => 'Contado',
            'medio_de_pago'         => $comprobante->venta->metodo_pago ?? 'Efectivo',
            'placa_vehiculo'        => '',
            'orden_compra_servicio' => '',
            'tabla_personalizada_codigo' => '',
            'formato_de_pdf'        => 'A4',
            'items'                 => $this->buildItems($comprobante),
        ];

        Log::info('Nubefact: enviando comprobante', [
            'numero'    => $comprobante->numero,
            'tenant_id' => $comprobante->tenant_id,
        ]);

        $response = Http::withToken($this->token)
            ->timeout(30)
            ->post("{$this->baseUrl}/invoices", $payload);

        $data = $response->json();

        Log::info('Nubefact: respuesta', [
            'numero' => $comprobante->numero,
            'status' => $response->status(),
            'data'   => $data,
        ]);

        if (!$response->successful()) {
            return [
                'aceptado' => false,
                'mensaje'  => $data['errors'] ?? 'Error al conectar con Nubefact',
                'hash'     => null,
                'cdr_url'  => null,
            ];
        }

        return [
            'aceptado' => ($data['aceptada_por_sunat'] ?? false) === true,
            'mensaje'  => $data['sunat_description'] ?? $data['errors'] ?? 'Procesado',
            'hash'     => $data['sunat_ticket'] ?? null,
            'cdr_url'  => $data['enlace_del_pdf'] ?? null,
            'xml_url'  => $data['enlace_del_xml'] ?? null,
        ];
    }

    private function buildItems(ComprobanteSunat $comprobante): array
    {
        $comprobante->load('venta.detalles.producto');
        $items = [];

        foreach ($comprobante->venta->detalles as $detalle) {
            $precioSinIgv = round((float)$detalle->precio_unit / 1.18, 2);

            $items[] = [
                'unidad_de_medida'         => 'NIU',
                'codigo'                   => (string) $detalle->producto_id,
                'descripcion'              => $detalle->producto->nombre ?? 'PRODUCTO',
                'cantidad'                 => (float) $detalle->cantidad,
                'valor_unitario'           => $precioSinIgv,
                'precio_unitario'          => (float) $detalle->precio_unit,
                'descuento'                => '',
                'subtotal'                 => round((float)$detalle->subtotal / 1.18, 2),
                'tipo_de_igv'              => 1,
                'igv'                      => round((float)$detalle->subtotal - (float)$detalle->subtotal / 1.18, 2),
                'total'                    => (float) $detalle->subtotal,
                'anticipo_regularizacion'  => false,
                'anticipo_documento_serie' => '',
                'anticipo_documento_numero'=> '',
            ];
        }

        return $items;
    }
}
