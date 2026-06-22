@extends('layouts.app')
@section('titulo', 'Detalle de compra')

@section('contenido')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">
                <a href="{{ route('compras.index') }}" class="hover:text-brand-600">Compras</a> / Detalle
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">{{ $compra->numero_documento }}</h1>
        </div>
        <a href="{{ route('compras.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-brand-700">
            <x-icon name="truck" /> Nueva compra
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div><p class="text-xs text-slate-400">Proveedor</p><p class="font-medium text-slate-700">{{ $compra->proveedor?->razon_social ?? '—' }}</p></div>
        <div><p class="text-xs text-slate-400">Fecha</p><p class="font-medium text-slate-700">{{ optional($compra->fecha)->format('d/m/Y') ?? $compra->created_at->format('d/m/Y') }}</p></div>
        <div><p class="text-xs text-slate-400">Registrado por</p><p class="font-medium text-slate-700">{{ $compra->usuario?->name ?? '—' }}</p></div>
        <div>
            <p class="text-xs text-slate-400">Estado de pago</p>
            @if ($compra->estado_pago === 'pagada')
                <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Pagada</span>
            @else
                <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">Pendiente</span>
            @endif
        </div>
        @if ($compra->observacion)
            <div class="col-span-2 sm:col-span-4"><p class="text-xs text-slate-400">Observación</p><p class="text-slate-600">{{ $compra->observacion }}</p></div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-5 py-3 font-semibold">Producto</th>
                    <th class="px-5 py-3 font-semibold text-center">Cant.</th>
                    <th class="px-5 py-3 font-semibold text-right">Costo unit.</th>
                    <th class="px-5 py-3 font-semibold">Lote</th>
                    <th class="px-5 py-3 font-semibold">Vencimiento</th>
                    <th class="px-5 py-3 font-semibold text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($compra->detalles as $d)
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-700">{{ $d->descripcion }}</td>
                        <td class="px-5 py-3 text-center">{{ $d->cantidad }}</td>
                        <td class="px-5 py-3 text-right">S/ {{ number_format($d->precio_compra, 2) }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $d->lote ?: '—' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ optional($d->fecha_vencimiento)->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($d->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50/60">
                <tr><td colspan="5" class="px-5 py-2 text-right text-slate-500">Subtotal</td><td class="px-5 py-2 text-right font-medium">S/ {{ number_format($compra->subtotal, 2) }}</td></tr>
                <tr><td colspan="5" class="px-5 py-2 text-right text-slate-500">IGV (18%)</td><td class="px-5 py-2 text-right font-medium">S/ {{ number_format($compra->igv, 2) }}</td></tr>
                <tr><td colspan="5" class="px-5 py-3 text-right text-lg font-bold text-slate-800">Total</td><td class="px-5 py-3 text-right text-lg font-bold text-slate-800">S/ {{ number_format($compra->total, 2) }}</td></tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
