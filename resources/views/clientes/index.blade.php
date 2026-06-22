@extends('layouts.app')
@section('titulo', 'Clientes')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
        open: false,
        mode: 'create',
        form: { id:null, nombre:'', tipo_documento:'DNI', numero_documento:'', telefono:'', email:'', direccion:'', activo:true },
        nuevo() { this.mode='create'; this.form={ id:null, nombre:'', tipo_documento:'DNI', numero_documento:'', telefono:'', email:'', direccion:'', activo:true }; this.open=true; },
        editar(c) { this.mode='edit'; this.form={ id:c.id, nombre:c.nombre, tipo_documento:c.tipo_documento, numero_documento:c.numero_documento??'', telefono:c.telefono??'', email:c.email??'', direccion:c.direccion??'', activo:!!c.activo }; this.open=true; }
     }">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Comercio & Ventas</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="users" /></span>
                Clientes
            </h1>
        </div>
        <button @click="nuevo()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-brand-700">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo cliente
        </button>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="relative max-w-md">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar por nombre o documento…"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-brand-500 focus:outline-none">
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Cliente</th>
                        <th class="px-5 py-3 font-semibold">Documento</th>
                        <th class="px-5 py-3 font-semibold">Teléfono</th>
                        <th class="px-5 py-3 font-semibold text-center">Compras</th>
                        <th class="px-5 py-3 font-semibold text-right">Total gastado</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($clientes as $c)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $c->nombre }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $c->tipo_documento }} {{ $c->numero_documento ?: '—' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $c->telefono ?: '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-7 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">{{ $c->compras_count }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($c->total_gastado ?? 0, 2) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('clientes.show', $c) }}" class="p-2 rounded-lg text-slate-400 hover:bg-brand-50 hover:text-brand-600" title="Ver historial">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                    <button @click='editar(@json($c))' class="p-2 rounded-lg text-slate-400 hover:bg-brand-50 hover:text-brand-600" title="Editar">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('clientes.destroy', $c) }}" onsubmit="return confirm('¿Eliminar al cliente “{{ $c->nombre }}”?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No hay clientes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($clientes->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $clientes->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="open=false"></div>
        <div x-show="open" x-transition class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <form method="POST" :action="mode==='edit' ? '{{ url('clientes') }}/'+form.id : '{{ route('clientes.store') }}'">
                @csrf
                <input type="hidden" name="_method" :value="mode==='edit' ? 'PUT' : 'POST'">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800" x-text="mode==='edit' ? 'Editar cliente' : 'Nuevo cliente'"></h3>
                    <button type="button" @click="open=false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Nombre / Razón social <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" x-model="form.nombre" required
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tipo de documento</label>
                        <select name="tipo_documento" x-model="form.tipo_documento" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="DNI">DNI</option>
                            <option value="RUC">RUC</option>
                            <option value="CE">Carné de extranjería</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">N° de documento</label>
                        <input type="text" name="numero_documento" x-model="form.numero_documento"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Teléfono</label>
                        <input type="text" name="telefono" x-model="form.telefono"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" name="email" x-model="form.email"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Dirección</label>
                        <input type="text" name="direccion" x-model="form.direccion"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600 sm:col-span-2">
                        <input type="checkbox" name="activo" value="1" x-model="form.activo" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Cliente activo
                    </label>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="open=false" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancelar</button>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
