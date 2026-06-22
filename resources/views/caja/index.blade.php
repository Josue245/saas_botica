@extends('layouts.app')
@section('titulo', 'Gestión de Caja')

@section('contenido')
<div class="max-w-6xl mx-auto space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Comercio & Ventas / Gestión de Caja</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="cash" /></span>
                Apertura / Cierre de Caja
            </h1>
        </div>
        @if ($sesion)
            <a href="{{ route('caja.movimientos') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Movimientos de caja</a>
        @endif
    </div>

    @if (! $sesion)
        {{-- Abrir caja --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm max-w-md">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </span>
                <div>
                    <h3 class="font-semibold text-slate-700">No hay caja abierta</h3>
                    <p class="text-sm text-slate-500">Abre la caja para empezar a vender.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('caja.abrir') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">Monto inicial (S/) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="monto_inicial" value="{{ old('monto_inicial', '0.00') }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Abrir caja</button>
            </form>
        </div>
    @else
        {{-- Caja abierta --}}
        <div x-data="{ inicial: {{ $resumen['esperado'] }}, final: 0 }" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-700">Caja abierta</h3>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> En operación
                        </span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div><p class="text-xs text-slate-400">Abierta por</p><p class="font-medium text-slate-700">{{ $sesion->usuario?->name ?? '—' }}</p></div>
                        <div><p class="text-xs text-slate-400">Desde</p><p class="font-medium text-slate-700">{{ $sesion->abierta_at?->format('d/m/Y H:i') }}</p></div>
                        <div><p class="text-xs text-slate-400">Monto inicial</p><p class="font-medium text-slate-700">S/ {{ number_format($sesion->monto_inicial, 2) }}</p></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Ventas efectivo</p><p class="mt-1 text-xl font-extrabold text-slate-800">S/ {{ number_format($resumen['ventasEfectivo'], 2) }}</p></div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Ingresos</p><p class="mt-1 text-xl font-extrabold text-emerald-600">S/ {{ number_format($resumen['ingresos'], 2) }}</p></div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Egresos</p><p class="mt-1 text-xl font-extrabold text-red-500">S/ {{ number_format($resumen['egresos'], 2) }}</p></div>
                    <div class="bg-brand-600 rounded-2xl p-4 shadow-sm text-white"><p class="text-xs uppercase font-semibold text-white/80">Esperado en caja</p><p class="mt-1 text-xl font-extrabold">S/ {{ number_format($resumen['esperado'], 2) }}</p></div>
                </div>
            </div>

            {{-- Cierre / arqueo --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm self-start">
                <h3 class="font-semibold text-slate-700 mb-4">Cierre de caja (arqueo)</h3>
                <form method="POST" action="{{ route('caja.cerrar') }}" class="space-y-4" onsubmit="return confirm('¿Cerrar la caja? Esta acción finaliza la sesión.')">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Efectivo contado (S/) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="monto_final" x-model.number="final" required
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <div class="rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-sm">
                        <div class="flex justify-between text-slate-500"><span>Esperado</span><span>S/ {{ number_format($resumen['esperado'], 2) }}</span></div>
                        <div class="flex justify-between font-semibold pt-1 mt-1 border-t border-slate-200"
                             :class="(final - inicial) < 0 ? 'text-red-600' : 'text-emerald-600'">
                            <span>Diferencia</span>
                            <span x-text="'S/ ' + (final - inicial).toFixed(2)"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Observación</label>
                        <input type="text" name="observacion" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Cerrar caja</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Historial --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-semibold text-slate-700">Historial de cierres</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Apertura</th>
                        <th class="px-5 py-3 font-semibold">Cierre</th>
                        <th class="px-5 py-3 font-semibold">Responsable</th>
                        <th class="px-5 py-3 font-semibold text-right">Inicial</th>
                        <th class="px-5 py-3 font-semibold text-right">Esperado</th>
                        <th class="px-5 py-3 font-semibold text-right">Contado</th>
                        <th class="px-5 py-3 font-semibold text-right">Diferencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($historial as $h)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 text-slate-500">{{ $h->abierta_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $h->cerrada_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $h->usuario?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">S/ {{ number_format($h->monto_inicial, 2) }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">S/ {{ number_format($h->monto_esperado, 2) }}</td>
                            <td class="px-5 py-3 text-right text-slate-700 font-medium">S/ {{ number_format($h->monto_final, 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $h->diferencia < 0 ? 'text-red-600' : ($h->diferencia > 0 ? 'text-amber-600' : 'text-emerald-600') }}">S/ {{ number_format($h->diferencia, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Aún no hay cierres registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($historial->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $historial->links() }}</div>
        @endif
    </div>
</div>
@endsection
