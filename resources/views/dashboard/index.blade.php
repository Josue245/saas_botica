@extends('layouts.app')
@section('titulo', 'Dashboard')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Encabezado --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Hola, {{ auth()->user()?->name ?? 'Admin' }}!</h1>
        <p class="text-sm text-slate-500">Este es el resumen general de tu botica · {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
    </div>

    {{-- Tarjetas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @php
            $cards = [
                ['t'=>'Ingresos de Hoy','v'=>'S/ '.number_format($ingresosHoy,2),'s'=>'Ventas pagadas del día','from'=>'from-brand-500','to'=>'to-brand-600','icon'=>'cash'],
                ['t'=>'Ventas de Hoy','v'=>number_format($ventasHoy),'s'=>'Comprobantes emitidos','from'=>'from-sky-500','to'=>'to-blue-600','icon'=>'cart'],
                ['t'=>'Clientes','v'=>number_format($clientes),'s'=>'Clientes registrados','from'=>'from-cyan-500','to'=>'to-teal-600','icon'=>'users'],
                ['t'=>'Alertas Sanitarias','v'=>number_format($alertasCount),'s'=>'Stock bajo y vencimientos','from'=>'from-amber-500','to'=>'to-orange-600','icon'=>'bell'],
            ];
        @endphp
        @foreach($cards as $c)
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $c['from'] }} {{ $c['to'] }} p-5 text-white shadow-sm">
                <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ $c['t'] }}</p>
                        <p class="mt-2 text-3xl font-extrabold">{{ $c['v'] }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center"><x-icon name="{{ $c['icon'] }}" /></span>
                </div>
                <p class="relative mt-3 text-xs text-white/80">{{ $c['s'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Fila 1 de gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h3 class="font-semibold text-slate-700">Ingresos de los últimos 7 días</h3>
            <div class="mt-4 h-72"><canvas id="chartIngresos"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h3 class="font-semibold text-slate-700">Distribución de Ingresos</h3>
            <div class="mt-4 h-72"><canvas id="chartDistribucion"></canvas></div>
        </div>
    </div>

    {{-- Fila 2 de gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h3 class="font-semibold text-slate-700">Top 5 Productos Más Vendidos (Unid.)</h3>
            <div class="mt-4 h-72"><canvas id="chartTopProductos"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h3 class="font-semibold text-slate-700">Top 5 Categorías con Mayores Ingresos</h3>
            <div class="mt-4 h-72"><canvas id="chartTopCategorias"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const fmtSoles = v => 'S/ ' + Number(v).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const PALETTE = ['#10b981','#3b82f6','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#ef4444'];
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    // Ingresos 7 días
    new Chart(document.getElementById('chartIngresos'), {
        type: 'bar',
        data: {
            labels: @json($labels7),
            datasets: [{
                label: 'Ingresos',
                data: @json($data7),
                backgroundColor: '#10b981',
                borderRadius: 6,
                maxBarThickness: 42,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => fmtSoles(c.raw) } } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v } }, x: { grid: { display: false } } }
        }
    });

    // Distribución (donut)
    (function(){
        const d = @json($distribucion);
        const labels = d.length ? d.map(x => (x.metodo_pago || 'Otro')) : ['Sin datos'];
        const data = d.length ? d.map(x => Number(x.monto)) : [1];
        new Chart(document.getElementById('chartDistribucion'), {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: PALETTE, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } },
                tooltip: { callbacks: { label: c => c.label + ': ' + fmtSoles(c.raw) } } } }
        });
    })();

    // Top productos (barra horizontal)
    (function(){
        const t = @json($topProductos);
        new Chart(document.getElementById('chartTopProductos'), {
            type: 'bar',
            data: {
                labels: t.length ? t.map(x => x.nombre) : ['Sin datos'],
                datasets: [{ label: 'Unidades', data: t.length ? t.map(x => Number(x.unidades)) : [0],
                    backgroundColor: '#8b5cf6', borderRadius: 6, maxBarThickness: 26 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid: { display: false } } } }
        });
    })();

    // Top categorías (pie)
    (function(){
        const c = @json($topCategorias);
        new Chart(document.getElementById('chartTopCategorias'), {
            type: 'pie',
            data: {
                labels: c.length ? c.map(x => x.nombre) : ['Sin datos'],
                datasets: [{ data: c.length ? c.map(x => Number(x.ingresos)) : [1], backgroundColor: PALETTE, borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 12 } },
                tooltip: { callbacks: { label: c => c.label + ': ' + fmtSoles(c.raw) } } } }
        });
    })();
</script>
@endpush
