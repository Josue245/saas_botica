@extends('layouts.app')
@section('titulo', 'Reportes')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Gerencia & Control</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="chart" /></span>
                Reportes
            </h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reportes.export', array_filter(['tipo'=>$tipo,'desde'=>$desde,'hasta'=>$hasta])) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Exportar Excel (CSV)
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                Imprimir / PDF
            </button>
        </div>
    </div>

    {{-- Pestañas --}}
    <div class="flex flex-wrap gap-2 no-print">
        @foreach (['ventas'=>'Ventas','productos'=>'Productos más vendidos','inventario'=>'Inventario valorizado'] as $k=>$label)
            <a href="{{ route('reportes.index', array_filter(['tipo'=>$k,'desde'=>$desde,'hasta'=>$hasta])) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold {{ $tipo===$k ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Filtro de fechas (no aplica a inventario) --}}
    @if ($tipo !== 'inventario')
        <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-wrap items-end gap-3 no-print">
            <input type="hidden" name="tipo" value="{{ $tipo }}">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
            </div>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Generar</button>
        </form>
    @endif

    {{-- Encabezado impreso --}}
    <div class="hidden print:block">
        <h2 class="text-xl font-bold">Mi Botica — Reporte de {{ ucfirst($tipo) }}</h2>
        @if ($tipo !== 'inventario')
            <p class="text-sm text-slate-500">Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
        @endif
    </div>

    {{-- ===================== VENTAS ===================== --}}
    @if ($tipo === 'ventas')
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Total vendido</p><p class="mt-1 text-2xl font-extrabold text-brand-600">S/ {{ number_format($kpis['total'],2) }}</p></div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">N° de ventas</p><p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($kpis['num']) }}</p></div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Ticket promedio</p><p class="mt-1 text-2xl font-extrabold text-slate-800">S/ {{ number_format($kpis['ticket'],2) }}</p></div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">IGV recaudado</p><p class="mt-1 text-2xl font-extrabold text-slate-800">S/ {{ number_format($kpis['igv'],2) }}</p></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="font-semibold text-slate-700">Ventas por día</h3>
                <div class="mt-4 h-72"><canvas id="chartRep"></canvas></div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-semibold text-slate-700">Por método de pago</h3></div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($porMetodo as $m)
                            <tr><td class="px-5 py-3 text-slate-600">{{ $m->metodo_pago }}</td><td class="px-5 py-3 text-right text-slate-400">{{ $m->n }}</td><td class="px-5 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($m->monto,2) }}</td></tr>
                        @empty
                            <tr><td class="px-5 py-6 text-center text-slate-400">Sin datos en el período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ===================== PRODUCTOS ===================== --}}
    @if ($tipo === 'productos')
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h3 class="font-semibold text-slate-700">Top productos (unidades)</h3>
            <div class="mt-4 h-72"><canvas id="chartRep"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-5 py-3 font-semibold">#</th><th class="px-5 py-3 font-semibold">Producto</th><th class="px-5 py-3 font-semibold text-center">Unidades</th><th class="px-5 py-3 font-semibold text-right">Ingresos</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($topProductos as $i=>$p)
                        <tr class="hover:bg-slate-50/60"><td class="px-5 py-3 text-slate-400">{{ $i+1 }}</td><td class="px-5 py-3 font-medium text-slate-700">{{ $p->nombre }}</td><td class="px-5 py-3 text-center font-semibold">{{ number_format($p->unidades) }}</td><td class="px-5 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($p->ingresos,2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">No hubo ventas en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ===================== INVENTARIO ===================== --}}
    @if ($tipo === 'inventario')
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Unidades</p><p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($invKpis['unidades']) }}</p></div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Valor a costo</p><p class="mt-1 text-2xl font-extrabold text-slate-800">S/ {{ number_format($invKpis['costo'],2) }}</p></div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm"><p class="text-xs uppercase font-semibold text-slate-400">Valor a venta</p><p class="mt-1 text-2xl font-extrabold text-brand-600">S/ {{ number_format($invKpis['venta'],2) }}</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-5 py-3 font-semibold">Producto</th><th class="px-5 py-3 font-semibold">Categoría</th><th class="px-5 py-3 font-semibold text-center">Stock</th><th class="px-5 py-3 font-semibold text-right">P. Venta</th><th class="px-5 py-3 font-semibold text-right">Valor</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($invProductos as $p)
                        <tr class="hover:bg-slate-50/60"><td class="px-5 py-3 font-medium text-slate-700">{{ $p->nombre }}</td><td class="px-5 py-3 text-slate-500">{{ $p->categoria?->nombre ?? '—' }}</td><td class="px-5 py-3 text-center">{{ $p->stock }}</td><td class="px-5 py-3 text-right text-slate-500">S/ {{ number_format($p->precio_venta,2) }}</td><td class="px-5 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($p->stock*$p->precio_venta,2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<style>
    @media print {
        aside, header, footer, .no-print { display: none !important; }
        .lg\:pl-64 { padding-left: 0 !important; }
        body { background: #fff; }
    }
</style>
@if (in_array($tipo, ['ventas','productos']))
<script>
    (function(){
        const el = document.getElementById('chartRep');
        if (!el) return;
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        new Chart(el, {
            type: 'bar',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [{
                    label: @json($tipo === 'productos' ? 'Unidades' : 'Ventas (S/)'),
                    data: @json($chartData ?? []),
                    backgroundColor: @json($tipo === 'productos' ? '#8b5cf6' : '#10b981'),
                    borderRadius: 6, maxBarThickness: 40,
                }]
            },
            options: {
                indexAxis: @json($tipo === 'productos' ? 'y' : 'x'),
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }
            }
        });
    })();
</script>
@endif
@endpush
