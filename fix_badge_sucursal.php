<?php
$path = 'resources/views/sucursales/index.blade.php';
$c = file_get_contents($path);
$c = str_replace(
    '<span class="text-xs {{ $suc->activo ? \'bg-green-100 text-green-700\' : \'bg-red-100 text-red-700\' }} px-2 py-0.5 rounded-full font-medium">
                        {{ $suc->activo ? \'Activa\' : \'Inactiva\' }}
                    </span>',
    '@if(!$suc->activo)
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Inactiva</span>
                    @endif',
    $c
);
file_put_contents($path, $c);
echo "Badge duplicado corregido OK\n";
