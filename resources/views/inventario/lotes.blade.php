@extends('layouts.app')
@section('titulo', 'Lotes y Vencimientos')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Logística & Inventario / Inventario</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="layers" /></span>
                Lotes y Vencimientos
            </h1>
            <p class="text-sm text-slate-500">Ordenado por fecha de vencimiento más próxima (criterio FEFO).</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">← Stock Actual</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Producto</th>
                        <th class="px-5 py-3 font-semibold">Lote</th>
                        <th class="px-5 py-3 font-semibold text-center">Stock</th>
                        <th class="px-5 py-3 font-semibold">Vencimiento</th>
                        <th class="px-5 py-3 font-semibold text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productos as $p)
                        @php $dias = (int) $hoy->diffInDays($p->fecha_vencimiento, false); @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $p->nombre }}</td>
                            <td class="px-5 py-3 text-slate-500 font-mono">{{ $p->lote ?: '—' }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-slate-700">{{ $p->stock }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $p->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($dias < 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-semibold">Vencido hace {{ abs($dias) }} d</span>
                                @elseif ($dias <= 30)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-xs font-semibold">Vence en {{ $dias }} d</span>
                                @elseif ($dias <= 60)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 text-xs font-semibold">Vence en {{ $dias }} d</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Vigente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No hay productos con fecha de vencimiento registrada.</td></tr>
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
