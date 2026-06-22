@extends('layouts.app')
@section('titulo', 'Nuevo producto')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <nav class="text-xs text-slate-400 mb-1">
            <a href="{{ route('productos.index') }}" class="hover:text-brand-600">Productos</a> / Nuevo
        </nav>
        <h1 class="text-2xl font-bold text-slate-800">Registrar producto</h1>
    </div>

    <form method="POST" action="{{ route('productos.store') }}">
        @csrf
        @include('productos._form')
    </form>
</div>
@endsection
