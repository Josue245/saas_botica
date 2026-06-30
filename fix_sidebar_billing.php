<?php
$path = 'resources/views/partials/sidebar.blade.php';
$c = file_get_contents($path);

$old = "        <p class=\"{{ \$section }}\">Ajustes &amp; Sistema</p>";
$new = "        <p class=\"{{ \$section }}\">Ajustes &amp; Sistema</p>
        <a href=\"{{ route('billing.index') }}\" class=\"{{ \$linkBase }} {{ request()->routeIs('billing.*') ? \$linkActive : \$linkIdle }}\">
            <x-icon name=\"credit-card\" /> Mi Suscripción
        </a>";

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Sidebar: enlace Billing agregado OK\n";
