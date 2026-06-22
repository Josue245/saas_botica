<?php
$path = 'app/Http/Middleware/ResolveTenant.php';
$c = file_get_contents($path);

$old = '        // 4. Por query param (solo local, para pruebas rápidas en el navegador)
        if (config(\'app.env\') === \'local\' && $slug = $request->query(\'_tenant\')) {
            return Tenant::where(\'slug\', $slug)->first();
        }

        return null;';

$new = '        // 4. Por query param (solo local, para pruebas rápidas en el navegador)
        if (config(\'app.env\') === \'local\' && $slug = $request->query(\'_tenant\')) {
            return Tenant::where(\'slug\', $slug)->first();
        }

        // 5. Desde usuario autenticado (la forma más confiable)
        if (auth()->check() && auth()->user()->tenant_id) {
            return Tenant::find(auth()->user()->tenant_id);
        }

        return null;';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "ResolveTenant: lectura desde usuario autenticado OK\n";
