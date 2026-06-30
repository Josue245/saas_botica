<?php
$path = 'app/Http/Controllers/Auth/TenantRegistrationController.php';
$c = file_get_contents($path);

$old = '// 3. Usuario admin (insert directo para evitar scope en creación)
                $user = User::sinTenant()->newModelInstance([
                    \'tenant_id\'   => $tenant->id,
                    \'sucursal_id\' => $sucursal->id,
                    \'name\'        => $data[\'nombre_admin\'],
                    \'email\'       => $data[\'email_admin\'],
                    \'password\'    => Hash::make($data[\'password\']),
                    \'rol\'         => \'admin\',
                    \'activo\'      => true,
                ]);
                $user->save();';

$new = '// 3. Usuario admin (insert directo sin scope)
                $userId = DB::table(\'users\')->insertGetId([
                    \'tenant_id\'   => $tenant->id,
                    \'sucursal_id\' => $sucursal->id,
                    \'name\'        => $data[\'nombre_admin\'],
                    \'email\'       => $data[\'email_admin\'],
                    \'password\'    => Hash::make($data[\'password\']),
                    \'rol\'         => \'admin\',
                    \'activo\'      => true,
                    \'created_at\'  => now(),
                    \'updated_at\'  => now(),
                ]);
                $user = User::withoutGlobalScopes()->find($userId);';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "TenantRegistrationController: fix aplicado OK\n";
