<?php
$path = 'resources/views/partials/sidebar.blade.php';
$c = file_get_contents($path);

// Quitar el enlace que agregamos antes fuera del submenu
$c = str_replace(
    "        <a href=\"{{ route('stock.sucursales.index') }}\" class=\"{{ \$linkBase }} {{ request()->routeIs('stock.sucursales.*') ? \$linkActive : \$linkIdle }}\">
            <x-icon name=\"layers\" /> Stock por Sucursal
        </a>
        <a href=\"{{ route('compras.index') }}\"",
    "        <a href=\"{{ route('compras.index') }}\"",
    $c
);

// Agregar dentro del submenu de Inventario
$c = str_replace(
    "<a href=\"{{ route('inventario.ajustes') }}\" class=\"{{ \$linkBase }} py-2 {{ request()->routeIs('inventario.ajustes') ? \$linkActive : \$linkIdle }}\">Ajustes de Stock</a>",
    "<a href=\"{{ route('inventario.ajustes') }}\" class=\"{{ \$linkBase }} py-2 {{ request()->routeIs('inventario.ajustes') ? \$linkActive : \$linkIdle }}\">Ajustes de Stock</a>
                <a href=\"{{ route('stock.sucursales.index') }}\" class=\"{{ \$linkBase }} py-2 {{ request()->routeIs('stock.sucursales.*') ? \$linkActive : \$linkIdle }}\">Stock por Sucursal</a>",
    $c
);

file_put_contents($path, $c);
echo "Sidebar: Stock por Sucursal en submenu Inventario OK\n";
