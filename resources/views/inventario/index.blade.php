@extends('layouts.app')
@section('titulo', 'Inventario · Stock Actual')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Logística & Inventario</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="layers" /></span>
                Stock Actual
            </h1>
        </div>
        <div class="flex gap-2 text-sm">
            <a href="{{ route('inventario.lotes') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 font-medium text-slate-600 hover:bg-slate-50">Lotes y Vencimientos</a>
            <a href="{{ route('inventario.ajustes') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 font-semibold text-white hover:bg-brand-700">Ajustar stock</a>
        </div>
    </div>

    {{-- Valorización --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Unidades en stock</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($unidades) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Valor a costo</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800">S/ {{ number_format($valorCosto, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Valor a precio de venta</p>
            <p class="mt-1 text-2xl font-extrabold text-brand-600">S/ {{ number_format($valorVenta, 2) }}</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="relative sm:col-span-2">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar producto o código…"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
        </div>
        <div class="flex gap-2">
            <select name="categoria" class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}" @selected($categoriaId == $cat->id)>{{ $cat->nombre }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filtrar</button>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Producto</th>
                        <th class="px-5 py-3 font-semibold">Categoría</th>
                        <th class="px-5 py-3 font-semibold text-center">Stock</th>
                        <th class="px-5 py-3 font-semibold text-center">Mínimo</th>
                        <th class="px-5 py-3 font-semibold text-right">Costo</th>
                        <th class="px-5 py-3 font-semibold text-right">Valor (venta)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productos as $p)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $p->nombre }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $p->categoria?->nombre ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($p->stock <= $p->stock_minimo)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">{{ $p->stock }} ⚠</span>
                                @else
                                    <span class="font-semibold text-slate-700">{{ $p->stock }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center text-slate-400">{{ $p->stock_minimo }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">S/ {{ number_format($p->precio_compra, 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($p->stock * $p->precio_venta, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No se encontraron productos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($productos->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $productos->links() }}</div>
        @endif
    </div>
</div>
@endsection
