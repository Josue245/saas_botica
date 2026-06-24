@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">

    {{-- Suscripción actual --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Tu plan actual</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $tenant->plan->nombre }}
                    @if($tenant->plan_expira_at)
                        · Vence el {{ $tenant->plan_expira_at->format('d/m/Y') }}
                    @endif
                    @if($tenant->estado === 'trial')
                        · <span class="text-amber-600 font-medium">Trial activo</span>
                    @endif
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                {{ $tenant->estaActivo() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $tenant->estaActivo() ? 'Activo' : 'Suspendido' }}
            </span>
        </div>

        @if($suscripcionActual)
        <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Último pago</span>
                <p class="font-medium">S/. {{ number_format($suscripcionActual->precio_pagado, 2) }}</p>
            </div>
            <div>
                <span class="text-gray-500">Método</span>
                <p class="font-medium">{{ ucfirst($suscripcionActual->metodo_pago ?? 'N/A') }}</p>
            </div>
            <div>
                <span class="text-gray-500">Próximo cobro</span>
                <p class="font-medium">{{ $suscripcionActual->expira_at?->format('d/m/Y') ?? '—' }}</p>
            </div>
        </div>
        @endif
    </div>

    @if(session('ok'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6 text-sm">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm">
            {!! session('error') !!}
        </div>
    @endif

    {{-- Grid de planes --}}
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Planes disponibles</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($planes as $plan)
        <div class="bg-white rounded-2xl shadow-sm border-2 p-5
            {{ $tenant->plan_id === $plan->id ? 'border-blue-500' : 'border-gray-100' }}">

            @if($tenant->plan_id === $plan->id)
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full mb-3">
                    Plan actual
                </span>
            @endif

            <h3 class="font-bold text-gray-800 text-lg">{{ $plan->nombre }}</h3>
            <div class="mt-1 mb-4">
                @if($plan->precio_mensual == 0)
                    <span class="text-2xl font-bold text-gray-800">Gratis</span>
                @else
                    <span class="text-2xl font-bold text-gray-800">S/. {{ number_format($plan->precio_mensual, 0) }}</span>
                    <span class="text-gray-400 text-sm">/mes</span>
                @endif
            </div>

            <ul class="space-y-2 text-sm text-gray-600 mb-6">
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    {{ $plan->max_usuarios === -1 ? 'Usuarios ilimitados' : $plan->max_usuarios . ' usuario(s)' }}
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    {{ $plan->max_productos === -1 ? 'Productos ilimitados' : number_format($plan->max_productos) . ' productos' }}
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    {{ $plan->max_sucursales === -1 ? 'Sucursales ilimitadas' : $plan->max_sucursales . ' sucursal(es)' }}
                </li>
                @if($plan->features['facturacion_electronica'] ?? false)
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span> Facturación electrónica SUNAT
                </li>
                @endif
                @if($plan->features['reportes_avanzados'] ?? false)
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span> Reportes avanzados
                </li>
                @endif
                @if($plan->features['api_access'] ?? false)
                <li class="flex items-center gap-2">
                    <span class="text-green-500">✓</span> Acceso API
                </li>
                @endif
            </ul>

            @if($tenant->plan_id !== $plan->id)
                <a href="{{ route('billing.checkout', $plan) }}"
                   class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition text-sm">
                    {{ $plan->precio_mensual == 0 ? 'Activar gratis' : 'Suscribirse' }}
                </a>
            @else
                <button disabled
                    class="block w-full text-center bg-gray-100 text-gray-400 font-medium py-2 rounded-lg text-sm cursor-not-allowed">
                    Plan actual
                </button>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Historial --}}
    <div class="mt-8 text-right">
        <a href="{{ route('billing.historial') }}" class="text-sm text-blue-600 hover:underline">
            Ver historial de pagos →
        </a>
    </div>
</div>
@endsection
