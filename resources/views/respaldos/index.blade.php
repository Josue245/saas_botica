@extends('layouts.app')
@section('titulo', 'Base de Datos & Respaldos')

@section('contenido')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Ajustes & Sistema</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="database" /></span>
                Base de Datos & Respaldos
            </h1>
            <p class="text-sm text-slate-500">Genera y descarga copias de seguridad de toda la base de datos.</p>
        </div>
        <form method="POST" action="{{ route('respaldos.generar') }}" onsubmit="return confirm('¿Generar un nuevo respaldo de la base de datos?')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-brand-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Generar respaldo
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
        El respaldo incluye la estructura y todos los datos en un archivo <span class="font-mono">.sql</span>, que puedes restaurar con
        <span class="font-mono">mysql -u root -p saas_botica &lt; archivo.sql</span> o desde phpMyAdmin.
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-semibold text-slate-700">Respaldos generados</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Archivo</th>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold text-right">Tamaño</th>
                        <th class="px-5 py-3 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($archivos as $a)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-mono text-slate-700">{{ $a['nombre'] }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $a['fecha'] }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">{{ number_format($a['tamano'] / 1024, 1) }} KB</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('respaldos.descargar', $a['nombre']) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:underline">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    Descargar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Aún no hay respaldos. Genera el primero con el botón de arriba.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
