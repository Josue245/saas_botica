<?php
$path = 'resources/views/ventas/index.blade.php';
$c = file_get_contents($path);

// Reemplazar el form simple por uno con selección de tipo
$old = '@elseif($v->estado === \'pagada\' && auth()->user()?->tenant?->puedeUsar(\'facturacion_electronica\'))
                                    <form method="POST" action="{{ route(\'facturacion.emitir\', $v) }}">
                                        @csrf
                                        <button class="text-xs text-blue-600 hover:underline">Emitir</button>
                                    </form>';

$new = '@elseif($v->estado === \'pagada\' && auth()->user()?->tenant?->puedeUsar(\'facturacion_electronica\'))
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open=!open" class="text-xs text-blue-600 hover:underline">Emitir ▾</button>
                                        <div x-show="open" x-cloak @click.outside="open=false"
                                             class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-100 z-10">
                                            <form method="POST" action="{{ route(\'facturacion.emitir\', $v) }}">
                                                @csrf
                                                <input type="hidden" name="tipo" value="boleta">
                                                <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50">🧾 Boleta</button>
                                            </form>
                                            <form method="POST" action="{{ route(\'facturacion.emitir\', $v) }}">
                                                @csrf
                                                <input type="hidden" name="tipo" value="factura">
                                                <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50">📄 Factura</button>
                                            </form>
                                        </div>
                                    </div>';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 4 OK: selector tipo comprobante agregado\n";
