@extends('layouts.app')

@section('titulo', 'Sucursales')

@section('contenido')
<div class="max-w-4xl mx-auto py-6 px-4">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Sucursales</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $sucursales->count() }} sucursal(es) ·
                Plan {{ $tenant->plan->nombre }}
                (máx. {{ $tenant->plan->max_sucursales === -1 ? '∞' : $tenant->plan->max_sucursales }})
            </p>
        </div>
        @if($tenant->dentroLimite('sucursales'))
        <button onclick="document.getElementById('modal-nueva').classList.remove('hidden')"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nueva sucursal
        </button>
        @endif
    </div>

    @if(session('ok'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">{!! session('error') !!}</div>
    @endif

    <div class="grid gap-4">
        @foreach($sucursales as $suc)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $suc->nombre }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $suc->direccion ?? 'Sin dirección' }}
                            @if($suc->telefono) · {{ $suc->telefono }} @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if(auth()->user()->sucursal_id === $suc->id)
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Activa</span>
                    @else
                        <form method="POST" action="{{ route('sucursales.cambiar', $suc) }}">
                            @csrf @method('PATCH')
                            <button class="text-xs bg-gray-100 hover:bg-blue-50 hover:text-blue-700 text-gray-600 px-2 py-0.5 rounded-full font-medium transition">
                                Cambiar a esta
                            </button>
                        </form>
                    @endif
                    <span class="text-xs {{ $suc->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} px-2 py-0.5 rounded-full font-medium">
                        {{ $suc->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3 text-center text-sm">
                <div class="bg-gray-50 rounded-lg p-2">
                    <p class="text-lg font-bold text-gray-800">{{ $suc->usuarios_count }}</p>
                    <p class="text-xs text-gray-500">Usuarios</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2">
                    <p class="text-lg font-bold text-gray-800">{{ $suc->ventas_count }}</p>
                    <p class="text-xs text-gray-500">Ventas</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2">
                    <p class="text-sm font-bold text-gray-800">{{ $suc->serie_boleta }}</p>
                    <p class="text-xs text-gray-500">Serie</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal nueva sucursal --}}
<div id="modal-nueva" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Nueva sucursal</h3>
        <form method="POST" action="{{ route('sucursales.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                <input type="text" name="nombre" required placeholder="Ej: Sucursal Norte"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <input type="text" name="direccion" placeholder="Av. Principal 123"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" name="telefono" placeholder="9XXXXXXXX"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serie Boleta *</label>
                    <input type="text" name="serie_boleta" value="B001" required maxlength="10"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serie Factura *</label>
                    <input type="text" name="serie_factura" value="F001" required maxlength="10"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="document.getElementById('modal-nueva').classList.add('hidden')"
                        class="flex-1 border border-gray-300 text-gray-700 text-sm font-medium py-2 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition">
                    Crear sucursal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
