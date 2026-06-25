@extends('layouts.app')

@section('contenido')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

    <h2 class="text-xl font-semibold text-gray-800 mb-1">
        Suscribirse a {{ $plan->nombre }}
    </h2>

    <p class="text-sm text-gray-500 mb-6">
        Se cobrará S/. {{ number_format($plan->precio_mensual, 2) }} al mes
    </p>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Resumen del plan --}}
    <div class="bg-blue-50 rounded-xl p-4 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <p class="font-semibold text-gray-800">
                    Plan {{ $plan->nombre }}
                </p>

                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $plan->max_usuarios === -1 ? 'Usuarios ilimitados' : $plan->max_usuarios . ' usuarios' }}
                    ·
                    {{ $plan->max_productos === -1 ? 'Productos ilimitados' : number_format($plan->max_productos) . ' productos' }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-xl font-bold text-gray-800">
                    S/. {{ number_format($plan->precio_mensual, 2) }}
                </p>
                <p class="text-xs text-gray-400">por mes</p>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <form id="culqi-form" method="POST" action="{{ route('billing.pagar', $plan) }}">
        @csrf

        {{-- Aquí se guardará el token --}}
        <input type="hidden" name="culqi_token" id="culqi_token">

        <button
            type="button"
            id="btn-pagar"
            class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition text-sm flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>

            Pagar S/. {{ number_format($plan->precio_mensual, 2) }}
        </button>

        <p class="text-center text-xs text-gray-400 mt-3">
            🔒 Pago seguro procesado por Culqi · PCI DSS compliant
        </p>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('billing.index') }}"
           class="text-sm text-gray-400 hover:text-gray-600">
            ← Volver a planes
        </a>
    </div>
</div>

{{-- Tarjetas de prueba --}}
@if(config('app.env') === 'local')
<div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-700">
    <p class="font-semibold mb-1">🧪 Tarjetas de prueba:</p>
    <p>Visa: <code class="bg-amber-100 px-1 rounded">4111 1111 1111 1111</code></p>
    <p>Mastercard: <code class="bg-amber-100 px-1 rounded">5111 1111 1111 1118</code></p>
    <p>CVV: cualquier 3 dígitos</p>
    <p>Fecha: cualquier fecha futura</p>
</div>
@endif

{{-- Culqi Checkout --}}
<script src="https://checkout.culqi.com/js/v4"></script>

<script>
    Culqi.publicKey = "{{ $culqiPublicKey }}";

    Culqi.settings({
        title: "Suscripción {{ $plan->nombre }}",
        currency: "PEN",
        amount: {{ (int)($plan->precio_mensual * 100) }}
    });

    document.getElementById("btn-pagar").addEventListener("click", function () {
        Culqi.open();
    });

    function culqi() {
        if (Culqi.token) {
            document.getElementById("culqi_token").value = Culqi.token.id;
            document.getElementById("culqi-form").submit();
        }

        if (Culqi.error) {
            console.error(Culqi.error);
            alert(Culqi.error.user_message);
        }
    }
</script>

@endsection