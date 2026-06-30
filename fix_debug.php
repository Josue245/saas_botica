<?php
$path = 'app/Http/Middleware/ResolveTenant.php';
$c = file_get_contents($path);
$old = '$this->manager->set($tenant);';
$new = '$this->manager->set($tenant);
        \Illuminate\Support\Facades\Log::info("ResolveTenant: tenant activo = " . $tenant->slug . " (id=" . $tenant->id . ")");';
$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Debug log agregado OK\n";
