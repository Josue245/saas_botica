<?php
$path = 'resources/views/ventas/index.blade.php';
$c = file_get_contents($path);

// Agregar columna SUNAT en el thead
$c = str_replace(
    '<th class="px-5 py-3 font-semibold text-right">Acciones</th>',
    '<th class="px-5 py-3 font-semibold text-center">SUNAT</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>',
    $c
);

// Agregar celda SUNAT en cada fila (antes de Acciones)
$old = '                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route(\'pos.ticket\', $v) }}" target="_blank"';

$new = '                            <td class="px-5 py-3 text-center">
                                @php
                                    $compElec = $v->comprobanteSunat ?? null;
                                @endphp
                                @if($compElec)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $compElec->estado === \'aceptado\' ? \'bg-green-100 text-green-700\' :
                                           ($compElec->estado === \'rechazado\' ? \'bg-red-100 text-red-700\' : \'bg-amber-100 text-amber-700\') }}">
                                        {{ ucfirst($compElec->estado) }}
                                    </span>
                                @elseif($v->estado === \'pagada\' && auth()->user()?->tenant?->puedeUsar(\'facturacion_electronica\'))
                                    <form method="POST" action="{{ route(\'facturacion.emitir\', $v) }}">
                                        @csrf
                                        <button class="text-xs text-blue-600 hover:underline">Emitir</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route(\'pos.ticket\', $v) }}" target="_blank"';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 1 OK: boton emitir comprobante agregado\n";
