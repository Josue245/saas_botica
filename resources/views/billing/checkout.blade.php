@extends('layouts.app')

@section('contenido')
<div class="max-w-lg mx-auto py-8 px-4">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

        <h2 class="text-xl font-semibold text-gray-800 mb-1">Suscribirse a {{ $plan->nombre }}</h2>
        <p class="text-sm text-gray-500 mb-6">Se cobrará S/. {{ number_format($plan->precio_mensual, 2) }} al mes</p>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Resumen del plan --}}
        <div class="bg-blue-50 rounded-xl p-4 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-semibold text-gray-800">Plan {{ $plan->nombre }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $plan->max_usuarios === -1 ? 'Usuarios ilimitados' : $plan->max_usuarios . ' usuarios' }} ·
                        {{ $plan->max_productos === -1 ? 'Productos ilimitados' : number_format($plan->max_productos) . ' productos' }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xl font-bold text-gray-800">S/. {{ number_format($plan->precio_mensual, 2) }}</p>
                    <p class="text-xs text-gray-400">por mes</p>
                </div>
            </div>
        </div>

        {{-- Formulario de pago --}}
        <form id="culqi-form" method="POST" action="{{ route('billing.pagar', $plan) }}">
            @csrf

            {{-- Token de Culqi (se llena con JS) --}}
            <input type="hidden" name="culqi_token" id="culqi_token">

            {{-- Datos de la tarjeta (Culqi.js los captura y genera el token) --}}
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de tarjeta</label>
                    <input type="text" id="card-number" placeholder="4111 1111 1111 1111"
                           maxlength="19"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vencimiento</label>
                        <input type="text" id="card-expiry" placeholder="MM/AA"
                               maxlength="5"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                        <input type="text" id="card-cvv" placeholder="123"
                               maxlength="4"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre en la tarjeta</label>
                    <input type="text" id="card-name" placeholder="JUAN PEREZ"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <button type="button" id="btn-pagar"
                    class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Pagar S/. {{ number_format($plan->precio_mensual, 2) }}
            </button>

            <p class="text-center text-xs text-gray-400 mt-3">
                🔒 Pago seguro procesado por Culqi · PCI DSS compliant
            </p>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('billing.index') }}" class="text-sm text-gray-400 hover:text-gray-600">
                ← Volver a planes
            </a>
        </div>
    </div>

    {{-- Tarjetas de prueba --}}
    @if(config('app.env') === 'local')
    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-700">
        <p class="font-semibold mb-1">🧪 Modo desarrollo — tarjetas de prueba Culqi:</p>
        <p>Visa exitosa: <code class="bg-amber-100 px-1 rounded">4111 1111 1111 1111</code></p>
        <p>Mastercard exitosa: <code class="bg-amber-100 px-1 rounded">5111 1111 1111 1118</code></p>
        <p>Rechazada: <code class="bg-amber-100 px-1 rounded">4000 0000 0000 0002</code></p>
        <p class="mt-1">CVV: cualquier 3 dígitos · Vencimiento: cualquier fecha futura</p>
    </div>
    @endif
</div>

{{-- Culqi.js --}}
<script src="https://checkout.culqi.com/js/v4"></script>
<script>
    // Configurar llave pública de Culqi
    Culqi.publicKey = '{{ $culqiPublicKey }}';
    Culqi.settings({
        title: 'Mi Botica',
        currency: 'PEN',
        amount: {{ (int)($plan->precio_mensual * 100) }},
        order: null,
    });

    // Formatear número de tarjeta
    document.getElementById('card-number').addEventListener('input', function(e) {
        let val = e.target.value.replace(/\D/g, '').substring(0, 16);
        e.target.value = val.match(/.{1,4}/g)?.join(' ') ?? val;
    });

    // Formatear vencimiento
    document.getElementById('card-expiry').addEventListener('input', function(e) {
        let val = e.target.value.replace(/\D/g, '').substring(0, 4);
        if (val.length >= 2) val = val.substring(0,2) + '/' + val.substring(2);
        e.target.value = val;
    });

    document.getElementById('btn-pagar').addEventListener('click', function() {
        const number  = document.getElementById('card-number').value.replace(/\s/g, '');
        const expiry  = document.getElementById('card-expiry').value.split('/');
        const cvv     = document.getElementById('card-cvv').value;
        const name    = document.getElementById('card-name').value;

        if (!number || !expiry[0] || !expiry[1] || !cvv || !name) {
            alert('Por favor completa todos los datos de la tarjeta.');
            return;
        }

        const btn = document.getElementById('btn-pagar');
        btn.disabled = true;
        btn.textContent = 'Procesando...';

        Culqi.createToken({
            card_number:      number,
            cvv:              cvv,
            expiration_month: expiry[0].padStart(2, '0'),
            expiration_year:  '20' + expiry[1],
            email:            '{{ $tenant->email }}',
        }).then(function(token) {
            document.getElementById('culqi_token').value = token.id;
            document.getElementById('culqi-form').submit();
        }).catch(function(error) {
            btn.disabled = false;
            btn.textContent = 'Pagar S/. {{ number_format($plan->precio_mensual, 2) }}';
            alert('Error: ' + (error.merchant_message || error.user_message || 'Error al procesar tarjeta'));
        });
    });
</script>
@endsection
