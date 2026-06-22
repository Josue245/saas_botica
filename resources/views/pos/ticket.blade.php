<!DOCTYPE html>
<html lang="es">
@php $sym = $config['moneda_simbolo'] ?? 'S/'; @endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante {{ $venta->numero_comprobante }} · {{ $config['empresa_nombre'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif}
        @media print {
            .no-print{display:none !important}
            body{background:#fff}
            .ticket{box-shadow:none !important;margin:0 !important;border:0 !important}
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4">

    {{-- Acciones --}}
    <div class="no-print max-w-sm mx-auto mb-4 flex gap-2">
        <a href="{{ route('pos.index') }}" class="flex-1 text-center rounded-lg bg-white border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">← Nueva venta</a>
        <button onclick="window.print()" class="flex-1 rounded-lg px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90" style="background:#059669">Imprimir</button>
    </div>

    {{-- Ticket --}}
    <div class="ticket max-w-sm mx-auto bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-slate-700">
        <div class="text-center border-b border-dashed border-slate-300 pb-4">
            <div class="flex items-center justify-center gap-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#059669">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                </div>
                <span class="text-xl font-extrabold text-slate-800">{{ $config['empresa_nombre'] }}</span>
            </div>
            @if ($config['empresa_ruc'])
                <p class="text-xs text-slate-500 mt-1">RUC {{ $config['empresa_ruc'] }}</p>
            @endif
            <p class="text-xs text-slate-500">{{ $config['empresa_direccion'] }}@if($config['empresa_telefono']) · Tel. {{ $config['empresa_telefono'] }}@endif</p>
            <p class="mt-3 text-sm font-bold uppercase">{{ $venta->tipo_comprobante }} de venta</p>
            <p class="text-sm font-mono">{{ $venta->numero_comprobante }}</p>
        </div>

        <div class="py-3 text-xs space-y-1 border-b border-dashed border-slate-300">
            <div class="flex justify-between"><span class="text-slate-400">Fecha:</span><span>{{ $venta->created_at->format('d/m/Y H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Cajero:</span><span>{{ $venta->usuario?->name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Cliente:</span><span>{{ $venta->cliente?->nombre ?? 'Cliente Varios' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Pago:</span><span>{{ $venta->metodo_pago }}</span></div>
        </div>

        <table class="w-full text-xs my-3">
            <thead>
                <tr class="text-slate-400 border-b border-slate-200">
                    <th class="text-left font-medium py-1">Producto</th>
                    <th class="text-center font-medium py-1">Cant</th>
                    <th class="text-right font-medium py-1">P.U.</th>
                    <th class="text-right font-medium py-1">Subt.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->detalles as $d)
                    <tr class="border-b border-slate-50">
                        <td class="py-1.5">{{ $d->descripcion }}</td>
                        <td class="py-1.5 text-center">{{ $d->cantidad }}</td>
                        <td class="py-1.5 text-right">{{ number_format($d->precio_unitario, 2) }}</td>
                        <td class="py-1.5 text-right font-medium">{{ number_format($d->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-sm space-y-1 border-t border-dashed border-slate-300 pt-3">
            <div class="flex justify-between text-slate-500"><span>Op. Gravada</span><span>{{ $sym }} {{ number_format($venta->subtotal, 2) }}</span></div>
            <div class="flex justify-between text-slate-500"><span>IGV ({{ $config['igv_porcentaje'] }}%)</span><span>{{ $sym }} {{ number_format($venta->igv, 2) }}</span></div>
            @if ($venta->descuento > 0)
                <div class="flex justify-between text-slate-500"><span>Descuento</span><span>− {{ $sym }} {{ number_format($venta->descuento, 2) }}</span></div>
            @endif
            <div class="flex justify-between text-lg font-extrabold text-slate-800 pt-1"><span>TOTAL</span><span>{{ $sym }} {{ number_format($venta->total, 2) }}</span></div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-5 border-t border-dashed border-slate-300 pt-4">
            {{ $config['mensaje_ticket'] }}
        </p>
    </div>
</body>
</html>
