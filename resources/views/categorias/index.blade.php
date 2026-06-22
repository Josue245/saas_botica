@extends('layouts.app')
@section('titulo', 'Categorías')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
        open: false,
        mode: 'create',
        form: { id: null, nombre: '', descripcion: '', activo: true },
        nuevo() { this.mode='create'; this.form={ id:null, nombre:'', descripcion:'', activo:true }; this.open=true; },
        editar(c) { this.mode='edit'; this.form={ id:c.id, nombre:c.nombre, descripcion:c.descripcion ?? '', activo:!!c.activo }; this.open=true; }
     }">

    {{-- Encabezado --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-slate-400 mb-1">Logística & Inventario / Catálogo Maestro</nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="box" /></span>
                Categorías
            </h1>
        </div>
        <button @click="nuevo()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-brand-700">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nueva categoría
        </button>
    </div>

    {{-- Buscador --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="relative max-w-md">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar categoría…"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Nombre</th>
                        <th class="px-5 py-3 font-semibold">Descripción</th>
                        <th class="px-5 py-3 font-semibold text-center">Productos</th>
                        <th class="px-5 py-3 font-semibold text-center">Estado</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categorias as $cat)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $cat->nombre }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $cat->descripcion ?: '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-7 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">{{ $cat->productos_count }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if ($cat->activo)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Activa</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click='editar(@json($cat))' class="p-2 rounded-lg text-slate-400 hover:bg-brand-50 hover:text-brand-600" title="Editar">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('categorias.destroy', $cat) }}" onsubmit="return confirm('¿Eliminar la categoría “{{ $cat->nombre }}”?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No hay categorías registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categorias->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $categorias->links() }}</div>
        @endif
    </div>

    {{-- Modal crear/editar --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="open=false"></div>
        <div x-show="open" x-transition class="relative w-full max-w-md bg-white rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <form method="POST" :action="mode==='edit' ? '{{ url('categorias') }}/'+form.id : '{{ route('categorias.store') }}'">
                @csrf
                <input type="hidden" name="_method" :value="mode==='edit' ? 'PUT' : 'POST'">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800" x-text="mode==='edit' ? 'Editar categoría' : 'Nueva categoría'"></h3>
                    <button type="button" @click="open=false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" x-model="form.nombre" required
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Descripción</label>
                        <textarea name="descripcion" x-model="form.descripcion" rows="2"
                                  class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none"></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="activo" value="1" x-model="form.activo" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Categoría activa
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
