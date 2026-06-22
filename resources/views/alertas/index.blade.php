@extends('layouts.app')
@section('titulo', 'Alertas Sanitarias')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    <div>
        <nav class="text-xs text-slate-400 mb-1">Gerencia & Control</nav>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center"><x-icon name="bell" /></span>
            Alertas Sanitarias
        </h1>
        <p class="text-sm text-slate-500">Productos vencidos, próximos a vencer y con stock por debajo del mínimo.</p>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-500">Vencidos</p>
            <p class="mt-1 text-3xl font-extrabold text-red-700">{{ $vencidos->count() }}</p>
        </div>
        <div class="rounded-2xl border border-orange-200 bg-orange-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">Por vencer (60 días)</p>
            <p class="mt-1 text-3xl font-extrabold text-orange-700">{{ $porVencer->count() }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-500">Stock bajo</p>
            <p class="mt-1 text-3xl font-extrabold text-amber-700">{{ $stockBajo->count() }}</p>
        </div>
    </div>

    @php
        $tabla = function ($items, $tipo) use ($hoy) {
            return [$items, $tipo];
        };
    @endphp

    {{-- Vencidos --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            <h3 class="font-semibold text-slate-700">Productos vencidos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Producto</th>
                        <th class="px-5 py-3 font-semibold">Lote</th>
                        <th class="px-5 py-3 font-semibold text-center">Stock</th>
                        <th class="px-5 py-3 font-semibold">Venció</th>
                        <th class="px-5 py-3 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($vencidos as $p)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $p->nombre }}</td>
                            <td class="px-5 py-3 text-slate-500 font-mono">{{ $p->lote ?: '—' }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-slate-700">{{ $p->stock }}</td>
                            <td class="px-5 py-3 text-red-600">{{ $p->fecha_vencimiento->format('d/m/Y') }} ({{ abs((int) $hoy->diffInDays($p->fecha_vencimiento, false)) }} d)</td>
                            <td class="px-5 py-3 text-right"><a href="{{ route('inventario.ajustes') }}" class="text-xs font-semibold text-brand-600 hover:underline">Retirar stock</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-slate-400">Sin productos vencidos. 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Por vencer --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
            <h3 class="font-semibold text-slate-700">Próximos a vencer (60 días)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Producto</th>
                        <th class="px-5 py-3 font-semibold">Lote</th>
                        <th class="px-5 py-3 font-semibold text-center">Stock</th>
                        <th class="px-5 py-3 font-semibold">Vence</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($porVencer as $p)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $p->nombre }}</td>
                            <td class="px-5 py-3 text-slate-500 font-mono">{{ $p->lote ?: '—' }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-slate-700">{{ $p->stock }}</td>
                            <td class="px-5 py-3 text-orange-600">{{ $p->fecha_vencimiento->format('d/m/Y') }} (en {{ (int) $hoy->diffInDays($p->fecha_vencimiento, false) }} d)</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-slate-400">Ningún producto próximo a vencer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stock bajo --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <h3 class="font-semibold text-slate-700">Stock por debajo del mínimo</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Producto</th>
                        <th class="px-5 py-3 font-semibold">Categoría</th>
                        <th class="px-5 py-3 font-semibold text-center">Stock</th>
                        <th class="px-5 py-3 font-semibold text-center">Mínimo</th>
                        <th class="px-5 py-3 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($stockBajo as $p)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $p->nombre }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $p->categoria?->nombre ?? '—' }}</td>
                            <td class="px-5 py-3 text-center"><span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">{{ $p->stock }}</span></td>
                            <td class="px-5 py-3 text-center text-slate-400">{{ $p->stock_minimo }}</td>
                            <td class="px-5 py-3 text-right"><a href="{{ route('compras.create') }}" class="text-xs font-semibold text-brand-600 hover:underline">Reponer</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-slate-400">Todo el stock está por encima del mínimo. 👍</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
