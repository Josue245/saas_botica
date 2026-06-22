@extends('layouts.app')
@section('titulo', 'Punto de Venta')

@section('contenido')
<div x-data="pos()" class="max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== Panel de productos ===== --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="cart" /></span>
                <h1 class="text-2xl font-bold text-slate-800">Punto de Venta</h1>
            </div>

            {{-- Buscador --}}
            <div class="relative">
                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" x-model="search" @keydown.enter.prevent="addFirstMatch()" placeholder="Buscar por nombre, código o principio activo…"
                       class="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-3 text-sm shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
            </div>

            {{-- Filtro de categorías --}}
            <div class="flex flex-wrap gap-2">
                <button @click="categoria=null" :class="categoria===null ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 border border-slate-200'"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold">Todas</button>
                @foreach ($categorias as $cat)
                    <button @click="categoria={{ $cat->id }}" :class="categoria==={{ $cat->id }} ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 border border-slate-200'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold">{{ $cat->nombre }}</button>
                @endforeach
            </div>

            {{-- Grilla --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <template x-for="p in filtered()" :key="p.id">
                    <button type="button" @click="add(p)" :disabled="p.stock<=0"
                            class="text-left rounded-xl border border-slate-200 bg-white p-3 shadow-sm hover:border-brand-400 hover:shadow transition disabled:opacity-40 disabled:cursor-not-allowed">
                        <div class="flex items-start justify-between gap-1">
                            <p class="text-sm font-semibold text-slate-700 leading-tight" x-text="p.nombre"></p>
                            <span x-show="p.requiere_receta" class="text-[9px] font-bold px-1 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200">Rx</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5" x-text="p.categoria || '—'"></p>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-base font-bold text-brand-600" x-text="'S/ ' + p.precio.toFixed(2)"></span>
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full"
                                  :class="p.stock<=0 ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-500'"
                                  x-text="'Stock: ' + p.stock"></span>
                        </div>
                    </button>
                </template>
                <p x-show="filtered().length===0" class="col-span-full text-center text-slate-400 py-10">No hay productos que coincidan.</p>
            </div>
        </div>

        {{-- ===== Carrito ===== --}}
        <div class="lg:sticky lg:top-20 self-start">
            <form method="POST" action="{{ route('pos.store') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col max-h-[calc(100vh-7rem)]">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">Venta actual</h3>
                    <button type="button" @click="cart=[]" x-show="cart.length" class="text-xs text-red-500 hover:underline">Vaciar</button>
                </div>

                {{-- Items --}}
                <div class="flex-1 overflow-y-auto px-5 py-3 space-y-3 min-h-[120px]">
                    <template x-for="(it, i) in cart" :key="it.id">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate" x-text="it.nombre"></p>
                                <p class="text-xs text-slate-400" x-text="'S/ ' + it.precio.toFixed(2) + ' c/u'"></p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="dec(i)" class="w-6 h-6 rounded bg-slate-100 text-slate-600 hover:bg-slate-200">−</button>
                                <span class="w-7 text-center text-sm font-semibold" x-text="it.cantidad"></span>
                                <button type="button" @click="inc(i)" class="w-6 h-6 rounded bg-slate-100 text-slate-600 hover:bg-slate-200">+</button>
                            </div>
                            <span class="w-16 text-right text-sm font-semibold text-slate-700" x-text="'S/ ' + (it.precio*it.cantidad).toFixed(2)"></span>
                            <button type="button" @click="remove(i)" class="text-slate-300 hover:text-red-500">&times;</button>
                        </div>
                    </template>
                    <p x-show="cart.length===0" class="text-center text-slate-400 text-sm py-8">Agrega productos para iniciar la venta.</p>
                </div>

                {{-- Totales y pago --}}
                <div class="px-5 py-4 border-t border-slate-100 space-y-3 bg-slate-50/60 rounded-b-2xl">
                    <div class="grid grid-cols-2 gap-2">
                        <select name="cliente_id" class="rounded-lg border border-slate-300 px-2 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">Cliente Varios</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        <select name="tipo_comprobante" class="rounded-lg border border-slate-300 px-2 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="ticket">Ticket</option>
                            <option value="boleta">Boleta</option>
                            <option value="factura">Factura</option>
                        </select>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">Método de pago</p>
                        <div class="grid grid-cols-5 gap-1">
                            <template x-for="m in metodos" :key="m">
                                <button type="button" @click="metodo=m" :class="metodo===m ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 border border-slate-200'"
                                        class="px-1 py-1.5 rounded-lg text-[11px] font-semibold" x-text="m"></button>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between text-slate-500"><span>Subtotal</span><span x-text="'S/ ' + subtotal().toFixed(2)"></span></div>
                        <div class="flex justify-between text-slate-500 items-center">
                            <span>Descuento</span>
                            <input type="number" name="descuento" x-model.number="descuento" min="0" step="0.10"
                                   class="w-24 text-right rounded border border-slate-200 px-2 py-1 text-sm focus:border-brand-500 focus:outline-none">
                        </div>
                        <div class="flex justify-between text-xs text-slate-400"><span>IGV (18%) incluido</span><span x-text="'S/ ' + igv().toFixed(2)"></span></div>
                        <div class="flex justify-between text-lg font-bold text-slate-800 pt-1 border-t border-slate-200"><span>Total</span><span x-text="'S/ ' + total().toFixed(2)"></span></div>
                    </div>

                    <label class="flex items-center gap-2 text-xs text-slate-500">
                        <input type="checkbox" name="con_receta" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Venta con receta médica
                    </label>

                    <input type="hidden" name="metodo_pago" :value="metodo">
                    <input type="hidden" name="items_json" :value="JSON.stringify(payload())">

                    <button type="submit" :disabled="cart.length===0"
                            @click="return confirmar($event)"
                            class="w-full rounded-xl bg-brand-600 px-4 py-3 text-white font-bold shadow-sm hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed">
                        <span x-text="'Cobrar  ·  S/ ' + total().toFixed(2)"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function pos() {
        return {
            products: @json($productos),
            cart: [],
            search: '',
            categoria: null,
            metodo: 'Efectivo',
            metodos: ['Efectivo', 'Tarjeta', 'Yape', 'Plin', 'Transferencia'],
            descuento: 0,

            filtered() {
                const s = this.search.trim().toLowerCase();
                return this.products.filter(p => {
                    const okCat = this.categoria === null || p.categoria_id === this.categoria;
                    const okTxt = s === '' || (p.nombre + ' ' + (p.codigo_barras||'') + ' ' + (p.principio_activo||'')).toLowerCase().includes(s);
                    return okCat && okTxt;
                });
            },
            add(p) {
                if (p.stock <= 0) return;
                const found = this.cart.find(i => i.id === p.id);
                if (found) {
                    if (found.cantidad < p.stock) found.cantidad++;
                } else {
                    this.cart.push({ id: p.id, nombre: p.nombre, precio: p.precio, cantidad: 1, stock: p.stock });
                }
            },
            addFirstMatch() {
                const f = this.filtered();
                if (f.length) { this.add(f[0]); this.search = ''; }
            },
            inc(i) { const it = this.cart[i]; if (it.cantidad < it.stock) it.cantidad++; },
            dec(i) { const it = this.cart[i]; if (it.cantidad > 1) it.cantidad--; else this.remove(i); },
            remove(i) { this.cart.splice(i, 1); },
            subtotal() { return this.cart.reduce((a, it) => a + it.precio * it.cantidad, 0); },
            total() { return Math.max(0, this.subtotal() - (parseFloat(this.descuento) || 0)); },
            igv() { return this.total() - this.total() / 1.18; },
            payload() { return this.cart.map(it => ({ id: it.id, cantidad: it.cantidad })); },
            confirmar(e) {
                if (this.cart.length === 0) { e.preventDefault(); return false; }
                if (!confirm('¿Confirmar la venta por S/ ' + this.total().toFixed(2) + '?')) { e.preventDefault(); return false; }
                return true;
            }
        };
    }
</script>
@endpush
