<?php
$path = 'app/Http/Controllers/PersonalController.php';
$c = file_get_contents($path);

$old = "User::create([
            'name' => \$data['name'],
            'email' => \$data['email'],
            'rol' => \$data['rol'],
            'telefono' => \$data['telefono'] ?? null,
            'password' => Hash::make(\$data['password']),
            'activo' => \$request->boolean('activo', true),
        ]);";

$new = "User::create([
            'name' => \$data['name'],
            'email' => \$data['email'],
            'rol' => \$data['rol'],
            'telefono' => \$data['telefono'] ?? null,
            'password' => Hash::make(\$data['password']),
            'activo' => \$request->boolean('activo', true),
            'tenant_id' => app()->bound('tenant') ? app('tenant')->id : null,
            'sucursal_id' => auth()->user()->sucursal_id ?? null,
        ]);";

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "PersonalController: tenant_id y sucursal_id OK\n";
