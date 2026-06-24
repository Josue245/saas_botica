<?php
$path = 'resources/views/partials/sidebar.blade.php';
$c = file_get_contents($path);

if (strpos($c, 'sucursales.index') !== false) {
    echo "Sidebar ya tiene sucursales\n";
    exit;
}

$old = '        <a href="{{ route(\'billing.index\') }}" class="{{ $linkBase }} {{ request()->routeIs(\'billing.*\') ? $linkActive : $linkIdle }}">
            <x-icon name="credit-card" /> Mi Suscripción
        </a>';

$new = '        <a href="{{ route(\'billing.index\') }}" class="{{ $linkBase }} {{ request()->routeIs(\'billing.*\') ? $linkActive : $linkIdle }}">
            <x-icon name="credit-card" /> Mi Suscripción
        </a>
        <a href="{{ route(\'sucursales.index\') }}" class="{{ $linkBase }} {{ request()->routeIs(\'sucursales.*\') ? $linkActive : $linkIdle }}">
            <x-icon name="building" /> Sucursales
        </a>';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Sidebar: enlace sucursales OK\n";
