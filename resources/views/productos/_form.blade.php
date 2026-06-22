@php
    $val = fn ($campo, $def = '') => old($campo, $producto->{$campo} ?? $def);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Columna principal --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-4">Información general</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Nombre del producto <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ $val('nombre') }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Principio activo</label>
                    <input type="text" name="principio_activo" value="{{ $val('principio_activo') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Laboratorio</label>
                    <input type="text" name="laboratorio" value="{{ $val('laboratorio') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Presentación</label>
                    <input type="text" name="presentacion" value="{{ $val('presentacion') }}" placeholder="Tableta, Jarabe, Cápsula…"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Concentración</label>
                    <input type="text" name="concentracion" value="{{ $val('concentracion') }}" placeholder="500mg, 10ml…"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Código de barras</label>
                    <input type="text" name="codigo_barras" value="{{ $val('codigo_barras') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Categoría</label>
                    <select name="categoria_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">— Sin categoría —</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected($val('categoria_id') == $cat->id)>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Proveedor</label>
                    <select name="proveedor_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">— Sin proveedor —</option>
                        @foreach ($proveedores as $prov)
                            <option value="{{ $prov->id }}" @selected($val('proveedor_id') == $prov->id)>{{ $prov->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-4">Precios e inventario</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Precio de compra (S/) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="precio_compra" value="{{ $val('precio_compra', 0) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Precio de venta (S/) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="precio_venta" value="{{ $val('precio_venta', 0) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Stock actual <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="stock" value="{{ $val('stock', 0) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Stock mínimo <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="stock_minimo" value="{{ $val('stock_minimo', 10) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Lote</label>
                    <input type="text" name="lote" value="{{ $val('lote') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Fecha de vencimiento</label>
                    <input type="date" name="fecha_vencimiento"
                           value="{{ old('fecha_vencimiento', optional($producto->fecha_vencimiento)->format('Y-m-d')) }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- Columna lateral --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-4">Opciones</h3>
            <div class="space-y-3">
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="activo" value="1" @checked($val('activo', true)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Producto activo
                </label>
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="requiere_receta" value="1" @checked($val('requiere_receta', false)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Requiere receta médica
                </label>
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="controlado" value="1" @checked($val('controlado', false)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Medicamento controlado
                </label>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex flex-col gap-2">
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Guardar producto</button>
                <a href="{{ route('productos.index') }}" class="text-center rounded-lg px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancelar</a>
            </div>
        </div>
    </div>
</div>
