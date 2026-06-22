<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\CajaSesion;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CajaController extends Controller
{
    private function sesionAbierta(): ?CajaSesion
    {
        return CajaSesion::with('usuario')->where('estado', 'abierta')->latest('abierta_at')->first();
    }

    /**
     * Calcula los totales de efectivo de la sesión abierta.
     */
    private function resumen(CajaSesion $sesion): array
    {
        $desde = $sesion->abierta_at ?? $sesion->created_at;

        $ventasEfectivo = (float) Venta::where('estado', 'pagada')
            ->where('metodo_pago', 'Efectivo')
            ->where('created_at', '>=', $desde)
            ->sum('total');

        $ingresos = (float) $sesion->movimientos()->where('tipo', 'ingreso')->sum('monto');
        $egresos = (float) $sesion->movimientos()->where('tipo', 'egreso')->sum('monto');

        $esperado = (float) $sesion->monto_inicial + $ventasEfectivo + $ingresos - $egresos;

        return compact('ventasEfectivo', 'ingresos', 'egresos', 'esperado');
    }

    public function index(): View
    {
        $sesion = $this->sesionAbierta();
        $resumen = $sesion ? $this->resumen($sesion) : null;

        $historial = CajaSesion::with(['usuario'])
            ->where('estado', 'cerrada')
            ->latest('cerrada_at')
            ->paginate(8);

        return view('caja.index', compact('sesion', 'resumen', 'historial'));
    }

    public function abrir(Request $request): RedirectResponse
    {
        if ($this->sesionAbierta()) {
            return back()->with('error', 'Ya existe una caja abierta. Ciérrala antes de abrir otra.');
        }

        $data = $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
        ], ['monto_inicial.required' => 'Indica el monto inicial de la caja.']);

        CajaSesion::create([
            'user_id' => $request->user()->id,
            'monto_inicial' => $data['monto_inicial'],
            'estado' => 'abierta',
            'abierta_at' => now(),
        ]);

        return redirect()->route('caja.index')->with('ok', 'Caja abierta correctamente.');
    }

    public function cerrar(Request $request): RedirectResponse
    {
        $sesion = $this->sesionAbierta();

        if (! $sesion) {
            return back()->with('error', 'No hay ninguna caja abierta.');
        }

        $data = $request->validate([
            'monto_final' => 'required|numeric|min:0',
            'observacion' => 'nullable|string|max:200',
        ], ['monto_final.required' => 'Indica el monto contado al cierre.']);

        $resumen = $this->resumen($sesion);

        $sesion->update([
            'monto_esperado' => $resumen['esperado'],
            'monto_final' => $data['monto_final'],
            'diferencia' => round($data['monto_final'] - $resumen['esperado'], 2),
            'estado' => 'cerrada',
            'observacion' => $data['observacion'] ?? null,
            'cerrado_por' => $request->user()->id,
            'cerrada_at' => now(),
        ]);

        return redirect()->route('caja.index')->with('ok', 'Caja cerrada. Diferencia: S/ ' . number_format($sesion->diferencia, 2) . '.');
    }

    public function movimientos(): View
    {
        $sesion = $this->sesionAbierta();
        $movimientos = $sesion
            ? $sesion->movimientos()->with('usuario')->latest()->paginate(15)
            : collect();

        $resumen = $sesion ? $this->resumen($sesion) : null;

        return view('caja.movimientos', compact('sesion', 'movimientos', 'resumen'));
    }

    public function guardarMovimiento(Request $request): RedirectResponse
    {
        $sesion = $this->sesionAbierta();

        if (! $sesion) {
            return back()->with('error', 'Debes abrir la caja antes de registrar movimientos.');
        }

        $data = $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'concepto' => 'required|string|max:160',
            'monto' => 'required|numeric|min:0.01',
        ], [
            'concepto.required' => 'Describe el concepto del movimiento.',
            'monto.min' => 'El monto debe ser mayor a cero.',
        ]);

        CajaMovimiento::create([
            'caja_sesion_id' => $sesion->id,
            'user_id' => $request->user()->id,
            'tipo' => $data['tipo'],
            'concepto' => $data['concepto'],
            'monto' => $data['monto'],
        ]);

        return redirect()->route('caja.movimientos')->with('ok', 'Movimiento registrado.');
    }
}
