@extends('layouts.app')
@section('titulo', 'Productos')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Logística & Inventario / Catálogo Maestro</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="box" /></span>
                Productos
            </h1>
        </div>
        <a href="{{ route('productos.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-brand-700">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo producto
        </a>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="relative lg:col-span-2">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar por nombre, código o principio activo…"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
        </div>
        <select name="categoria" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
            <option value="">Todas las categorías</option>
            @foreach ($categorias as $cat)
                <option value="{{ $cat->id }}" @selected($categoriaId == $cat->id)>{{ $cat->nombre }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="filtro" class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
                <option value="">Todos</option>
                <option value="stock_bajo" @selected($filtro==='stock_bajo')>Stock bajo</option>
                <option value="por_vencer" @selected($filtro==='por_vencer')>Por vencer (60 días)</option>
                <option value="vencido" @selected($filtro==='vencido')>Vencidos</option>
            </select>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filtrar</button>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Producto</th>
                        <th class="px-5 py-3 font-semibold">Categoría</th>
                        <th class="px-5 py-3 font-semibold text-right">P. Venta</th>
                        <th class="px-5 py-3 font-semibold text-center">Stock</th>
                        <th class="px-5 py-3 font-semibold text-center">Vencimiento</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productos as $p)
                        @php
                            $dias = $p->fecha_vencimiento ? now()->startOfDay()->diffInDays($p->fecha_vencimiento, false) : null;
                        @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-700 flex items-center gap-2">
                                    {{ $p->nombre }}
                                    @if ($p->requiere_receta)
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200">Rx</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400">{{ $p->principio_activo }} @if($p->concentracion) · {{ $p->concentracion }} @endif</div>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $p->categoria?->nombre ?? '—' }}</td>
                            <td class="px-5 py-3 text-right font-medium text-slate-700">S/ {{ number_format($p->precio_venta, 2) }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($p->stock <= $p->stock_minimo)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold" title="Stock mínimo: {{ $p->stock_minimo }}">{{ $p->stock }} ⚠</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">{{ $p->stock }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if (is_null($dias))
                                    <span class="text-slate-400 text-xs">—</span>
                                @elseif ($dias < 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-semibold">Vencido</span>
                                @elseif ($dias <= 60)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 text-xs font-semibold">{{ $p->fecha_vencimiento->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-slate-500 text-xs">{{ $p->fecha_vencimiento->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('productos.edit', $p) }}" class="p-2 rounded-lg text-slate-400 hover:bg-brand-50 hover:text-brand-600" title="Editar">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('productos.destroy', $p) }}" onsubmit="return confirm('¿Eliminar el producto “{{ $p->nombre }}”?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
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
