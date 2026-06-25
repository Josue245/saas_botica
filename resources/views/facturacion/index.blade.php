@extends('layouts.app')

@section('titulo', 'Facturación Electrónica')

@section('contenido')
<div class="max-w-5xl mx-auto py-6 px-4">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Facturación Electrónica</h1>
            <p class="text-sm text-gray-500 mt-0.5">Comprobantes enviados a SUNAT</p>
        </div>
        @if(!$habilitado)
            <a href="{{ route('billing.index') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                Actualizar a Pro →
            </a>
        @endif
    </div>

    @if(!$habilitado)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6 text-center">
        <div class="text-4xl mb-3">🔒</div>
        <h3 class="font-semibold text-amber-800 mb-1">Facturación electrónica no disponible</h3>
        <p class="text-sm text-amber-700 mb-4">
            Tu plan <strong>{{ $tenant->plan->nombre }}</strong> no incluye facturación electrónica SUNAT.<br>
            Actualiza al plan <strong>Pro</strong> para emitir boletas y facturas electrónicas.
        </p>
        <a href="{{ route('billing.index') }}"
           class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-medium px-6 py-2 rounded-lg transition text-sm">
            Ver planes disponibles
        </a>
    </div>
    @else

    @if(session('ok'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">{!! session('error') !!}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['aceptados'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Aceptados SUNAT</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-amber-500">{{ $stats['pendientes'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendientes</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $stats['rechazados'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Rechazados</p>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="flex gap-3 mb-4">
        <select name="estado" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los estados</option>
            <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="aceptado" {{ request('estado') === 'aceptado' ? 'selected' : '' }}>Aceptado</option>
            <option value="rechazado" {{ request('estado') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
        </select>
        <select name="tipo" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los tipos</option>
            <option value="boleta" {{ request('tipo') === 'boleta' ? 'selected' : '' }}>Boleta</option>
            <option value="factura" {{ request('tipo') === 'factura' ? 'selected' : '' }}>Factura</option>
        </select>
    </form>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Número</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Tipo</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Fecha</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($comprobantes as $comp)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-medium text-gray-800">{{ $comp->numero }}</td>
                    <td class="px-4 py-3 capitalize text-gray-600">{{ $comp->tipo }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $comp->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-right font-medium">S/. {{ number_format($comp->venta->total ?? 0, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $comp->estado === 'aceptado'  ? 'bg-green-100 text-green-700' :
                               ($comp->estado === 'rechazado' ? 'bg-red-100 text-red-700' :
                               ($comp->estado === 'enviando'  ? 'bg-blue-100 text-blue-700' :
                                                                'bg-amber-100 text-amber-700')) }}">
                            {{ ucfirst($comp->estado) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($comp->cdr_url)
                                <a href="{{ $comp->cdr_url }}" target="_blank"
                                   class="text-xs text-blue-600 hover:underline">PDF</a>
                            @endif
                            <a href="{{ route('facturacion.xml', $comp) }}"
                               class="text-xs text-gray-500 hover:text-gray-700">XML</a>
                            @if($comp->puedeReenviar())
                                <form method="POST" action="{{ route('facturacion.reenviar', $comp) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="text-xs text-amber-600 hover:underline">Reenviar</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                        No hay comprobantes electrónicos aún.<br>
                        <span class="text-xs">Ve a Historial de Ventas y emite el comprobante electrónico de cada venta.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($comprobantes->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $comprobantes->links() }}</div>
        @endif
    </div>
    @endif
</div>
@endsection
