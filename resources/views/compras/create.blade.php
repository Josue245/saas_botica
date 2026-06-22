@extends('layouts.app')
@section('titulo', 'Nueva compra')

@section('contenido')
<div x-data="compra()" class="max-w-7xl mx-auto space-y-6">
    <div>
        <nav class="text-xs text-slate-400 mb-1">
            <a href="{{ route('compras.index') }}" class="hover:text-brand-600">Compras</a> / Nueva
        </nav>
        <h1 class="text-2xl font-bold text-slate-800">Registrar compra</h1>
        <p class="text-sm text-slate-500">Ingresa mercadería al inventario. El stock y el costo se actualizan automáticamente.</p>
    </div>

    <form method="POST" action="{{ route('compras.store') }}" @submit="return validar($event)" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        {{-- Datos de la compra --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="font-semibold text-slate-700 mb-4">Datos del documento</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Proveedor <span class="text-red-500">*</span></label>
                        <select name="proveedor_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">— Selecciona proveedor —</option>
                            @foreach ($proveedores as $prov)
                                <option value="{{ $prov->id }}" @selected(old('proveedor_id')==$prov->id)>{{ $prov->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">N° de documento / factura</label>
                        <input type="text" name="numero_documento" value="{{ old('numero_documento') }}" placeholder="Autogenerado si lo dejas vacío"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha</label>
                        <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Estado de pago</label>
                        <select name="estado_pago" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="pendiente">Pendiente (por pagar)</option>
                            <option value="pagada">Pagada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Observación</label>
                        <input type="text" name="observacion" value="{{ old('observacion') }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    </div>
                </div>
            </div>

            {{-- Productos --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="font-semibold text-slate-700 mb-4">Productos a ingresar</h3>
                <div class="flex gap-2 mb-4">
                    <select x-model="picker" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">— Selecciona un producto para agregar —</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" x-text="p.nombre + ' (stock: ' + p.stock + ')'"></option>
                        </template>
                    </select>
                    <button type="button" @click="addByPicker()" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Agregar</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-slate-400 text-left border-b border-slate-200">
                            <tr>
                                <th class="py-2 font-medium">Producto</th>
                                <th class="py-2 font-medium w-20">Cant.</th>
                                <th class="py-2 font-medium w-28">Costo unit.</th>
                                <th class="py-2 font-medium w-28">Lote</th>
                                <th class="py-2 font-medium w-36">Vencimiento</th>
                                <th class="py-2 font-medium text-right w-24">Subtotal</th>
                                <th class="py-2 w-8"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(l, i) in lines" :key="l.id">
                                <tr class="border-b border-slate-50">
                                    <td class="py-2 pr-2 font-medium text-slate-700" x-text="l.nombre"></td>
                                    <td class="py-2 pr-2"><input type="number" min="1" x-model.number="l.cantidad" class="w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-brand-500 focus:outline-none"></td>
                                    <td class="py-2 pr-2"><input type="number" min="0" step="0.01" x-model.number="l.precio_compra" class="w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-brand-500 focus:outline-none"></td>
                                    <td class="py-2 pr-2"><input type="text" x-model="l.lote" class="w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-brand-500 focus:outline-none"></td>
                                    <td class="py-2 pr-2"><input type="date" x-model="l.fecha_vencimiento" class="w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-brand-500 focus:outline-none"></td>
                                    <td class="py-2 text-right font-semibold text-slate-700" x-text="'S/ ' + (l.cantidad*l.precio_compra || 0).toFixed(2)"></td>
                                    <td class="py-2 text-right"><button type="button" @click="lines.splice(i,1)" class="text-slate-300 hover:text-red-500">&times;</button></td>
                                </tr>
                            </template>
                            <tr x-show="lines.length===0"><td colspan="7" class="py-8 text-center text-slate-400">Agrega productos a la compra.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Resumen --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm lg:sticky lg:top-20">
                <h3 class="font-semibold text-slate-700 mb-4">Resumen</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-slate-500"><span>Subtotal</span><span x-text="'S/ ' + subtotal().toFixed(2)"></span></div>
                    <div class="flex justify-between text-slate-500"><span>IGV (18%)</span><span x-text="'S/ ' + igv().toFixed(2)"></span></div>
                    <div class="flex justify-between text-lg font-bold text-slate-800 pt-2 border-t border-slate-200"><span>Total</span><span x-text="'S/ ' + total().toFixed(2)"></span></div>
                </div>
                <input type="hidden" name="items_json" :value="JSON.stringify(payload())">
                <div class="mt-5 flex flex-col gap-2">
                    <button type="submit" :disabled="lines.length===0" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed">Registrar compra</button>
                    <a href="{{ route('compras.index') }}" class="text-center rounded-lg px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function compra() {
        return {
            products: @json($productos),
            lines: [],
            picker: '',
            addByPicker() {
                if (!this.picker) return;
                const p = this.products.find(x => x.id == this.picker);
                if (!p) return;
                if (this.lines.find(l => l.id === p.id)) { this.picker=''; return; }
                this.lines.push({ id: p.id, nombre: p.nombre, cantidad: 1, precio_compra: p.precio_compra, lote: '', fecha_vencimiento: '' });
                this.picker = '';
            },
            subtotal() { return this.lines.reduce((a, l) => a + (l.cantidad * l.precio_compra || 0), 0); },
            igv() { return this.subtotal() * 0.18; },
            total() { return this.subtotal() * 1.18; },
            payload() { return this.lines.map(l => ({ id: l.id, cantidad: l.cantidad, precio_compra: l.precio_compra, lote: l.lote, fecha_vencimiento: l.fecha_vencimiento })); },
            validar(e) {
                if (this.lines.length === 0) { e.preventDefault(); alert('Agrega al menos un producto.'); return false; }
                return true;
            }
        };
    }
</script>
@endpush
