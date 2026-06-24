@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Historial de pagos</h2>
        <a href="{{ route('billing.index') }}" class="text-sm text-blue-600 hover:underline">← Volver a planes</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Fecha</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Plan</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Monto</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Método</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Vence</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($suscripciones as $sus)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-600">{{ $sus->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $sus->plan->nombre }}</td>
                    <td class="px-4 py-3 text-gray-600">S/. {{ number_format($sus->precio_pagado, 2) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ ucfirst($sus->metodo_pago ?? '—') }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $sus->estado === 'activa' ? 'bg-green-100 text-green-700' :
                               ($sus->estado === 'vencida' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ ucfirst($sus->estado) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $sus->expira_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No hay pagos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($suscripciones->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $suscripciones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
