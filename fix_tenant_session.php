<?php
$path = 'app/Http/Middleware/ResolveTenant.php';
$c = file_get_contents($path);

// Guardar tenant en sesion y leerlo desde ahí si no viene en la URL
$old = '    private function resolverTenant(Request $request): ?Tenant
    {
        // 1. Por subdominio (producción)';

$new = '    private function resolverTenant(Request $request): ?Tenant
    {
        // 1. Por subdominio (producción)';

// Agregar persistencia en sesion despues de set()
$old2 = '$this->manager->set($tenant);
        \Illuminate\Support\Facades\Log::info("ResolveTenant: tenant activo = " . $tenant->slug . " (id=" . $tenant->id . ")");';

$new2 = '$this->manager->set($tenant);
        // Persistir en sesion para requests sin query param
        if ($request->hasSession()) {
            $request->session()->put("tenant_id", $tenant->id);
        }
        \Illuminate\Support\Facades\Log::info("ResolveTenant: tenant activo = " . $tenant->slug . " (id=" . $tenant->id . ")");';

// Leer desde sesion como fallback
$old3 = '        // 4. Por query param (solo local, para pruebas rápidas en el navegador)
        if (config(\'app.env\') === \'local\' && $slug = $request->query(\'_tenant\')) {
            return Tenant::where(\'slug\', $slug)->first();
        }

        return null;';

$new3 = '        // 4. Por query param (solo local, para pruebas rápidas en el navegador)
        if (config(\'app.env\') === \'local\' && $slug = $request->query(\'_tenant\')) {
            return Tenant::where(\'slug\', $slug)->first();
        }

        // 5. Desde sesion (persiste entre requests sin query param)
        if ($request->hasSession() && $tenantId = $request->session()->get("tenant_id")) {
            return Tenant::find($tenantId);
        }

        return null;';

$c = str_replace($old2, $new2, $c);
$c = str_replace($old3, $new3, $c);
file_put_contents($path, $c);
echo "ResolveTenant: persistencia en sesion OK\n";
