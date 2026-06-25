<?php
$path = 'resources/views/partials/sidebar.blade.php';
$c = file_get_contents($path);

$old = '<a href="{{ route(\'facturacion.index\') }}" class="{{ $linkBase }} justify-between {{ request()->routeIs(\'facturacion.*\') ? $linkActive : $linkIdle }}">
            <span class="flex items-center gap-3"><x-icon name="receipt" /> Facturación Electrónica</span>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-700 text-slate-300">OFF</span>
        </a>';

$new = '<a href="{{ route(\'facturacion.index\') }}" class="{{ $linkBase }} justify-between {{ request()->routeIs(\'facturacion.*\') ? $linkActive : $linkIdle }}">
            <span class="flex items-center gap-3"><x-icon name="receipt" /> Facturación Electrónica</span>
            @if(auth()->user()?->tenant?->puedeUsar(\'facturacion_electronica\'))
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-green-600 text-white">PRO</span>
            @else
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-700 text-slate-300">FREE</span>
            @endif
        </a>';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Sidebar: Facturación actualizada OK\n";
