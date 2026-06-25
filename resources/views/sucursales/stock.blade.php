@extends('layouts.app')

@section('titulo', 'Stock por Sucursal')

@section('contenido')
<div class="max-w-5xl mx-auto py-6 px-4">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-800">Stock por Sucursal</h1>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('stock.sucursales.poblar') }}">
                @csrf
                <button class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition">
                    Migrar stock actual
                </button>
            </form>
        </div>
    </div>

    @if(session('ok'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Producto</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Sucursal</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500">Stock</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500">Mínimo</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($stock as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->producto }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->sucursal }}</td>
                    <td class="px-4 py-3 text-center font-mono">{{ $item->stock }}</td>
                    <td class="px-4 py-3 text-center font-mono text-gray-400">{{ $item->stock_minimo }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($item->alerta)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Stock bajo</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">OK</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                        No hay registros de stock por sucursal.
                        <form method="POST" action="{{ route('stock.sucursales.poblar') }}" class="inline">
                            @csrf
                            <button class="ml-2 text-blue-600 hover:underline">Migrar stock ahora →</button>
                        </form>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($stock->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $stock->links() }}</div>
        @endif
    </div>

    {{-- Transferir stock --}}
    @if($sucursales->count() > 1)
    <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Transferir stock entre sucursales</h3>
        <form method="POST" action="{{ route('stock.sucursales.transferir') }}" class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Producto ID</label>
                <input type="number" name="producto_id" placeholder="ID del producto"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sucursal origen</label>
                <select name="sucursal_origen" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sucursal destino</label>
                <select name="sucursal_destino" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cantidad</label>
                <div class="flex gap-2">
                    <input type="number" name="cantidad" min="1" placeholder="Qty"
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition">
                        Transferir
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
