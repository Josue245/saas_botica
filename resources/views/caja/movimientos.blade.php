@extends('layouts.app')
@section('titulo', 'Movimientos de Caja')

@section('contenido')
<div class="max-w-6xl mx-auto space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Comercio & Ventas / Gestión de Caja</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="cash" /></span>
                Movimientos de Caja
            </h1>
        </div>
        <a href="{{ route('caja.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">← Apertura / Cierre</a>
    </div>

    @if (! $sesion)
        <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm text-center">
            <p class="text-slate-500">No hay una caja abierta.</p>
            <a href="{{ route('caja.index') }}" class="inline-block mt-3 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Abrir caja</a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Registrar movimiento --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm self-start">
                <h3 class="font-semibold text-slate-700 mb-4">Registrar movimiento</h3>
                <form method="POST" action="{{ route('caja.movimientos.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tipo</label>
                        <select name="tipo" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="ingreso">Ingreso (+)</option>
                            <option value="egreso">Egreso (−)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Concepto <span class="text-red-500">*</span></label>
                        <input type="text" name="concepto" value="{{ old('concepto') }}" required placeholder="Pago a proveedor, gasto, retiro…"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Monto (S/) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="monto" value="{{ old('monto') }}" required
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Registrar</button>
                </form>
            </div>

            {{-- Resumen + tabla --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Ingresos</p><p class="mt-1 text-lg font-extrabold text-emerald-600">S/ {{ number_format($resumen['ingresos'], 2) }}</p></div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Egresos</p><p class="mt-1 text-lg font-extrabold text-red-500">S/ {{ number_format($resumen['egresos'], 2) }}</p></div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Esperado en caja</p><p class="mt-1 text-lg font-extrabold text-slate-800">S/ {{ number_format($resumen['esperado'], 2) }}</p></div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 text-left">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Fecha</th>
                                    <th class="px-5 py-3 font-semibold">Concepto</th>
                                    <th class="px-5 py-3 font-semibold">Usuario</th>
                                    <th class="px-5 py-3 font-semibold text-center">Tipo</th>
                                    <th class="px-5 py-3 font-semibold text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($movimientos as $m)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-5 py-3 text-slate-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-5 py-3 text-slate-700">{{ $m->concepto }}</td>
                                        <td class="px-5 py-3 text-slate-500">{{ $m->usuario?->name ?? '—' }}</td>
                                        <td class="px-5 py-3 text-center">
                                            @if ($m->tipo === 'ingreso')
                                                <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Ingreso</span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-xs font-semibold">Egreso</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-right font-semibold {{ $m->tipo === 'ingreso' ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ $m->tipo === 'ingreso' ? '+' : '−' }} S/ {{ number_format($m->monto, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Sin movimientos en esta sesión.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($movimientos instanceof \Illuminate\Pagination\LengthAwarePaginator && $movimientos->hasPages())
                        <div class="px-5 py-3 border-t border-slate-100">{{ $movimientos->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
