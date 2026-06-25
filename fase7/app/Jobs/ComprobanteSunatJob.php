<?php

namespace App\Jobs;

use App\Models\ComprobanteSunat;
use App\Models\Venta;
use App\Services\Sunat\NubefactService;
use App\Services\Sunat\XmlGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ComprobanteSunatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60; // retry cada 60 segundos

    public function __construct(
        public readonly int $comprobanteId
    ) {}

    public function handle(XmlGeneratorService $xmlGenerator, NubefactService $nubefact): void
    {
        $comprobante = ComprobanteSunat::with('venta.detalles.producto', 'venta.cliente', 'venta.user.tenant')
            ->findOrFail($this->comprobanteId);

        // Marcar como enviando
        $comprobante->update([
            'estado'     => 'enviando',
            'enviado_at' => now(),
        ]);

        try {
            // 1. Generar XML UBL 2.1
            $xml = $xmlGenerator->generar($comprobante->venta, $comprobante);

            // Guardar XML para auditoría y re-envío
            $comprobante->update(['xml_contenido' => $xml]);

            // 2. Enviar a Nubefact
            $resultado = $nubefact->enviar($comprobante, $xml);

            // 3. Actualizar estado
            $comprobante->update([
                'estado'       => $resultado['aceptado'] ? 'aceptado' : 'rechazado',
                'hash'         => $resultado['hash'] ?? null,
                'cdr_url'      => $resultado['cdr_url'] ?? null,
                'xml_url'      => $resultado['xml_url'] ?? null,
                'error_mensaje' => $resultado['aceptado'] ? null : ($resultado['mensaje'] ?? 'Error desconocido'),
                'aceptado_at'  => $resultado['aceptado'] ? now() : null,
            ]);

            Log::info('ComprobanteSunatJob: completado', [
                'comprobante_id' => $this->comprobanteId,
                'numero'         => $comprobante->numero,
                'aceptado'       => $resultado['aceptado'],
            ]);

        } catch (\Throwable $e) {
            $comprobante->update([
                'estado'        => 'pendiente', // vuelve a pendiente para retry
                'error_mensaje' => $e->getMessage(),
            ]);

            Log::error('ComprobanteSunatJob: error', [
                'comprobante_id' => $this->comprobanteId,
                'error'          => $e->getMessage(),
            ]);

            throw $e; // re-throw para activar retry de Laravel
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Después de agotar los reintentos, marcar como error permanente
        ComprobanteSunat::find($this->comprobanteId)?->update([
            'estado'        => 'rechazado',
            'error_mensaje' => 'Agotados los reintentos: ' . $exception->getMessage(),
        ]);
    }
}
