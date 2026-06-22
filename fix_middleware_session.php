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

        // 5. Desde sesion (despues del login)
        try {
            if ($request->hasSession() && $request->session()->isStarted()) {
                if ($tenantId = $request->session()->get("tenant_id")) {
                    return Tenant::find($tenantId);
                }
            }
        } catch (\Throwable $e) {
            // sesion no disponible aun, ignorar
        }

        return null;';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "ResolveTenant: lectura desde sesion OK\n";
