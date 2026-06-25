@extends('layouts.app')
@section('titulo', 'Historial de Ventas')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Comercio & Ventas</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="list" /></span>
                Historial de Ventas
            </h1>
        </div>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-brand-700">
            <x-icon name="cart" /> Nueva venta
        </a>
    </div>

    {{-- Tarjetas resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total vendido</p>
            <p class="mt-1 text-2xl font-extrabold text-brand-600">S/ {{ number_format($totalVendido, 2) }}</p>
            <p class="text-xs text-slate-400">Según filtros aplicados</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">N° de ventas</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($numVentas) }}</p>
            <p class="text-xs text-slate-400">Comprobantes en el periodo</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Ticket promedio</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800">S/ {{ number_format($ticketPromedio, 2) }}</p>
            <p class="text-xs text-slate-400">Por venta pagada</p>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <div class="lg:col-span-2 relative">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Comprobante o cliente…"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
        </div>
        <input type="date" name="desde" value="{{ $desde }}" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
        <input type="date" name="hasta" value="{{ $hasta }}" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
        <select name="metodo" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
            <option value="">Todo método</option>
            @foreach (['Efectivo','Tarjeta','Yape','Plin','Transferencia'] as $m)
                <option value="{{ $m }}" @selected($metodo===$m)>{{ $m }}</option>
            @endforeach
        </select>
        <select name="estado" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
            <option value="">Todo estado</option>
            <option value="pagada" @selected($estado==='pagada')>Pagada</option>
            <option value="anulada" @selected($estado==='anulada')>Anulada</option>
        </select>
        <select name="cajero" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
            <option value="">Todo cajero</option>
            @foreach ($cajeros as $cj)
                <option value="{{ $cj->id }}" @selected($cajeroId == $cj->id)>{{ $cj->name }}</option>
            @endforeach
        </select>
        <div class="lg:col-span-6 flex gap-2">
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Aplicar filtros</button>
            <a href="{{ route('ventas.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">Limpiar</a>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Comprobante</th>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold">Cliente</th>
                        <th class="px-5 py-3 font-semibold">Cajero</th>
                        <th class="px-5 py-3 font-semibold">Pago</th>
                        <th class="px-5 py-3 font-semibold text-right">Total</th>
                        <th class="px-5 py-3 font-semibold text-center">Estado</th>
                        <th class="px-5 py-3 font-semibold text-center">SUNAT</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ventas as $v)
                        <tr class="hover:bg-slate-50/60 {{ $v->estado === 'anulada' ? 'opacity-60' : '' }}">
                            <td class="px-5 py-3 font-mono text-slate-700">{{ $v->numero_comprobante }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $v->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $v->cliente?->nombre ?? 'Cliente Varios' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $v->usuario?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $v->metodo_pago }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($v->total, 2) }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($v->estado === 'pagada')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Pagada</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-xs font-semibold">Anulada</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php
                                    $compElec = $v->comprobanteSunat ?? null;
                                @endphp
                                @if($compElec)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $compElec->estado === 'aceptado' ? 'bg-green-100 text-green-700' :
                                           ($compElec->estado === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst($compElec->estado) }}
                                    </span>
                                @elseif($v->estado === 'pagada' && auth()->user()?->tenant?->puedeUsar('facturacion_electronica'))
                                    <form method="POST" action="{{ route('facturacion.emitir', $v) }}">
                                        @csrf
                                        <button class="text-xs text-blue-600 hover:underline">Emitir</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('pos.ticket', $v) }}" target="_blank" class="p-2 rounded-lg text-slate-400 hover:bg-brand-50 hover:text-brand-600" title="Ver / Imprimir comprobante">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                    @if ($v->estado === 'pagada')
                                        <form method="POST" action="{{ route('ventas.anular', $v) }}" onsubmit="return confirm('¿Anular la venta {{ $v->numero_comprobante }}? Se restaurará el stock de los productos.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="Anular venta">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">No se encontraron ventas con esos criterios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ventas->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $ventas->links() }}</div>
        @endif
    </div>
</div>
@endsection
