<?php
// Revertir fix_middleware_session
$path = 'app/Http/Middleware/ResolveTenant.php';
$c = file_get_contents($path);
$c = str_replace(
    '        // 5. Desde sesion (despues del login)
        try {
            if ($request->hasSession() && $request->session()->isStarted()) {
                if ($tenantId = $request->session()->get("tenant_id")) {
                    return Tenant::find($tenantId);
                }
            }
        } catch (\Throwable $e) {
            // sesion no disponible aun, ignorar
        }

        ',
    '        ',
    $c
);
file_put_contents($path, $c);
echo "ResolveTenant: revertido OK\n";
