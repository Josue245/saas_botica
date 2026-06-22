@extends('layouts.app')
@section('titulo', 'Logs de Auditoría')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">

    <div>
        <nav class="text-xs text-slate-400 mb-1">Ajustes & Sistema</nav>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="shield" /></span>
            Logs de Auditoría
        </h1>
        <p class="text-sm text-slate-500">Registro automático de acciones realizadas en el sistema.</p>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="lg:col-span-2 relative">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Acción, módulo o descripción…"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
        </div>
        <select name="usuario" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
            <option value="">Todos los usuarios</option>
            @foreach ($usuarios as $u)
                <option value="{{ $u->id }}" @selected($userId == $u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
        <input type="date" name="desde" value="{{ $desde }}" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
        <div class="flex gap-2">
            <input type="date" name="hasta" value="{{ $hasta }}" class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filtrar</button>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Fecha y hora</th>
                        <th class="px-5 py-3 font-semibold">Usuario</th>
                        <th class="px-5 py-3 font-semibold">Acción</th>
                        <th class="px-5 py-3 font-semibold">Detalle</th>
                        <th class="px-5 py-3 font-semibold">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $log->usuario?->name ?? 'Sistema' }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $color = str_contains($log->accion,'elimin') ? 'bg-red-50 text-red-600'
                                        : (str_contains($log->accion,'cre') ? 'bg-emerald-50 text-emerald-700'
                                        : (str_contains($log->accion,'sesión') ? 'bg-sky-50 text-sky-700' : 'bg-amber-50 text-amber-700'));
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $color }}">{{ ucfirst($log->accion) }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $log->descripcion ?: '—' }}</td>
                            <td class="px-5 py-3 text-slate-400 font-mono text-xs">{{ $log->ip ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No hay registros de auditoría con esos criterios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
