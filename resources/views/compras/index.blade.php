@extends('layouts.app')
@section('titulo', 'Compras')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Logística & Inventario</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="truck" /></span>
                Compras
            </h1>
        </div>
        <a href="{{ route('compras.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-brand-700">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nueva compra
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Compras del mes</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800">S/ {{ number_format($totalMes, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cuentas por pagar</p>
            <p class="mt-1 text-2xl font-extrabold text-amber-600">S/ {{ number_format($porPagar, 2) }}</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="relative max-w-md">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar por documento o proveedor…"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Documento</th>
                        <th class="px-5 py-3 font-semibold">Proveedor</th>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold text-right">Total</th>
                        <th class="px-5 py-3 font-semibold text-center">Pago</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($compras as $c)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-mono text-slate-700">{{ $c->numero_documento }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $c->proveedor?->razon_social ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ optional($c->fecha)->format('d/m/Y') ?? $c->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($c->total, 2) }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($c->estado_pago === 'pagada')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Pagada</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('compras.show', $c) }}" class="inline-flex p-2 rounded-lg text-slate-400 hover:bg-brand-50 hover:text-brand-600" title="Ver detalle">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Aún no hay compras registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($compras->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $compras->links() }}</div>
        @endif
    </div>
</div>
@endsection
