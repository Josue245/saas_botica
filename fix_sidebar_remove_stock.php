<?php
$path = 'resources/views/partials/sidebar.blade.php';
$c = file_get_contents($path);

$c = str_replace(
    "        <a href=\"{{ route('stock.sucursales.index') }}\" class=\"{{ \$linkBase }} {{ request()->routeIs('stock.sucursales.*') ? \$linkActive : \$linkIdle }}\">
            <x-icon name=\"layers\" /> Stock por Sucursal
        </a>
        <a href=\"{{ route('proveedores.index') }}\"",
    "        <a href=\"{{ route('proveedores.index') }}\"",
    $c
);

file_put_contents($path, $c);
echo "Stock por Sucursal removido de Logistica OK\n";
