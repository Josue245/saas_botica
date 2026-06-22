@extends('layouts.app')
@section('titulo', 'Cliente')

@section('contenido')
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <nav class="text-xs text-slate-400 mb-1">
            <a href="{{ route('clientes.index') }}" class="hover:text-brand-600">Clientes</a> / Historial
        </nav>
        <h1 class="text-2xl font-bold text-slate-800">{{ $cliente->nombre }}</h1>
        <p class="text-sm text-slate-500">{{ $cliente->tipo_documento }} {{ $cliente->numero_documento ?: '—' }}</p>
    </div>

    {{-- Datos + métricas --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-2 text-sm">
            <h3 class="font-semibold text-slate-700 mb-2">Datos de contacto</h3>
            <p><span class="text-slate-400">Teléfono:</span> {{ $cliente->telefono ?: '—' }}</p>
            <p><span class="text-slate-400">Email:</span> {{ $cliente->email ?: '—' }}</p>
            <p><span class="text-slate-400">Dirección:</span> {{ $cliente->direccion ?: '—' }}</p>
            <p><span class="text-slate-400">Puntos:</span> {{ $cliente->puntos }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Compras realizadas</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800">{{ number_format($numCompras) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total gastado</p>
            <p class="mt-1 text-3xl font-extrabold text-brand-600">S/ {{ number_format($totalGastado, 2) }}</p>
        </div>
    </div>

    {{-- Historial de compras --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-semibold text-slate-700">Historial de compras</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Comprobante</th>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold">Cajero</th>
                        <th class="px-5 py-3 font-semibold">Pago</th>
                        <th class="px-5 py-3 font-semibold text-right">Total</th>
                        <th class="px-5 py-3 font-semibold text-center">Estado</th>
                        <th class="px-5 py-3 font-semibold text-right">Ver</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ventas as $v)
                        <tr class="hover:bg-slate-50/60 {{ $v->estado === 'anulada' ? 'opacity-60' : '' }}">
                            <td class="px-5 py-3 font-mono text-slate-700">{{ $v->numero_comprobante }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $v->created_at->format('d/m/Y H:i') }}</td>
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
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('pos.ticket', $v) }}" target="_blank" class="text-xs font-semibold text-brand-600 hover:underline">Comprobante</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Este cliente aún no tiene compras.</td></tr>
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
