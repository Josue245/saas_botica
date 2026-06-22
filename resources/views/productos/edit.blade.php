@extends('layouts.app')
@section('titulo', 'Editar producto')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <nav class="text-xs text-slate-400 mb-1">
            <a href="{{ route('productos.index') }}" class="hover:text-brand-600">Productos</a> / Editar
        </nav>
        <h1 class="text-2xl font-bold text-slate-800">Editar producto</h1>
        <p class="text-sm text-slate-500">{{ $producto->nombre }}</p>
    </div>

    <form method="POST" action="{{ route('productos.update', $producto) }}">
        @csrf
        @method('PUT')
        @include('productos._form')
    </form>
</div>
@endsection
