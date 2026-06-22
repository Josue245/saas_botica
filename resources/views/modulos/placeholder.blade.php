@extends('layouts.app')
@section('titulo', $titulo)

@section('contenido')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Encabezado del módulo --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <nav class="text-xs text-slate-400 mb-1">{{ $seccion ?? 'Módulo' }} <span class="mx-1">/</span> <span class="text-slate-500">{{ $titulo }}</span></nav>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><x-icon name="{{ $icon ?? 'grid' }}" /></span>
                {{ $titulo }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 max-w-2xl">{{ $descripcion }}</p>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-200">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> En desarrollo
        </span>
    </div>

    {{-- Funcionalidades previstas --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <h3 class="font-semibold text-slate-700 mb-4">Funcionalidades de este módulo</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($funciones as $f)
                <div class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3">
                    <span class="mt-0.5 w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                    <p class="text-sm text-slate-600">{{ $f }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white/50 py-12 text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3"><x-icon name="{{ $icon ?? 'grid' }}" /></div>
        <p class="text-slate-500 font-medium">La interfaz de “{{ $titulo }}” se construirá en la siguiente iteración.</p>
        <p class="text-sm text-slate-400 mt-1">La estructura, rutas y navegación ya están listas.</p>
    </div>
</div>
@endsection
