@extends('layouts.app')
@section('titulo', 'Configuración General')

@section('contenido')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <nav class="text-xs text-slate-400 mb-1">Ajustes & Sistema</nav>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="cog" /></span>
            Configuración General
        </h1>
        <p class="text-sm text-slate-500">Estos datos se usan en los comprobantes del punto de venta.</p>
    </div>

    <form method="POST" action="{{ route('configuracion.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Datos de la empresa --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-4">Datos de la botica</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Nombre / Razón social <span class="text-red-500">*</span></label>
                    <input type="text" name="empresa_nombre" value="{{ old('empresa_nombre', $config['empresa_nombre']) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">RUC</label>
                    <input type="text" name="empresa_ruc" value="{{ old('empresa_ruc', $config['empresa_ruc']) }}" maxlength="11"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Teléfono</label>
                    <input type="text" name="empresa_telefono" value="{{ old('empresa_telefono', $config['empresa_telefono']) }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Dirección</label>
                    <input type="text" name="empresa_direccion" value="{{ old('empresa_direccion', $config['empresa_direccion']) }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="empresa_email" value="{{ old('empresa_email', $config['empresa_email']) }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
            </div>
        </div>

        {{-- Parámetros de venta --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-semibold text-slate-700 mb-4">Parámetros de venta y comprobantes</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">IGV (%) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" name="igv_porcentaje" value="{{ old('igv_porcentaje', $config['igv_porcentaje']) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Símbolo de moneda <span class="text-red-500">*</span></label>
                    <input type="text" name="moneda_simbolo" value="{{ old('moneda_simbolo', $config['moneda_simbolo']) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Serie de boleta <span class="text-red-500">*</span></label>
                    <input type="text" name="serie_boleta" value="{{ old('serie_boleta', $config['serie_boleta']) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Serie de factura <span class="text-red-500">*</span></label>
                    <input type="text" name="serie_factura" value="{{ old('serie_factura', $config['serie_factura']) }}" required
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Mensaje al pie del ticket</label>
                    <input type="text" name="mensaje_ticket" value="{{ old('mensaje_ticket', $config['mensaje_ticket']) }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <button type="submit" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Guardar configuración</button>
        </div>
    </form>
</div>
@endsection
