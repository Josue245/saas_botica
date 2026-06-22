@extends('layouts.app')
@section('titulo', 'Ajustes de Stock')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Logística & Inventario / Inventario</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="layers" /></span>
                Ajustes de Stock
            </h1>
        </div>
        <a href="{{ route('inventario.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">← Stock Actual</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Formulario --}}
        <div class="lg:col-span-1">
            <form method="POST" action="{{ route('inventario.ajustes.store') }}" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                @csrf
                <h3 class="font-semibold text-slate-700">Nuevo ajuste</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Producto <span class="text-red-500">*</span></label>
                    <select name="producto_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">— Selecciona —</option>
                        @foreach ($productos as $p)
                            <option value="{{ $p->id }}" @selected(old('producto_id')==$p->id)>{{ $p->nombre }} (stock: {{ $p->stock }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tipo de ajuste</label>
                    <select name="tipo" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="ingreso">Ingreso (+) — sumar al stock</option>
                        <option value="salida">Salida (−) — merma, rotura, vencido</option>
                        <option value="conteo">Conteo físico (=) — fijar stock exacto</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Cantidad <span class="text-red-500">*</span></label>
                    <input type="number" name="cantidad" min="0" value="{{ old('cantidad') }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Motivo</label>
                    <input type="text" name="motivo" value="{{ old('motivo') }}" placeholder="Ej: producto vencido, conteo mensual…"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Registrar ajuste</button>
            </form>
        </div>

        {{-- Historial --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-semibold text-slate-700">Historial de ajustes</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-left">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Fecha</th>
                                <th class="px-5 py-3 font-semibold">Producto</th>
                                <th class="px-5 py-3 font-semibold text-center">Tipo</th>
                                <th class="px-5 py-3 font-semibold text-center">Cant.</th>
                                <th class="px-5 py-3 font-semibold text-center">Stock</th>
                                <th class="px-5 py-3 font-semibold">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ajustes as $a)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-5 py-3 text-slate-500">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-3 font-medium text-slate-700">{{ $a->producto?->nombre ?? '—' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($a->tipo === 'ingreso')
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Ingreso</span>
                                        @elseif ($a->tipo === 'salida')
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-xs font-semibold">Salida</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Conteo</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center text-slate-600">{{ $a->cantidad }}</td>
                                    <td class="px-5 py-3 text-center text-slate-500">{{ $a->stock_anterior }} → <span class="font-semibold text-slate-700">{{ $a->stock_nuevo }}</span></td>
                                    <td class="px-5 py-3 text-slate-500">{{ $a->motivo ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Aún no hay ajustes registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($ajustes->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100">{{ $ajustes->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
