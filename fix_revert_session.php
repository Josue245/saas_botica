<?php
$path = 'app/Http/Middleware/ResolveTenant.php';
$c = file_get_contents($path);

// Revertir: quitar persistencia en sesion del middleware (causa loop)
$c = str_replace(
    '        // Persistir en sesion para requests sin query param
        if ($request->hasSession()) {
            $request->session()->put("tenant_id", $tenant->id);
        }
        ',
    '        ',
    $c
);

// Revertir: quitar lectura desde sesion
$c = str_replace(
    '        // 5. Desde sesion (persiste entre requests sin query param)
        if ($request->hasSession() && $tenantId = $request->session()->get("tenant_id")) {
            return Tenant::find($tenantId);
        }

        ',
    '        ',
    $c
);

file_put_contents($path, $c);
echo "ResolveTenant: revertido OK\n";
