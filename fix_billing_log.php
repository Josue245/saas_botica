<?php
$path = 'app/Http/Controllers/BillingController.php';
$c = file_get_contents($path);
$old = '$tenant = $this->tenantManager->get() ?? auth()->user()?->tenant;
        if (!$tenant) return redirect()->route(\'login\');
        $planes = Plan::where(\'activo\', true)->orderBy(\'precio_mensual\')->get();';
$new = '$tenant = $this->tenantManager->get() ?? auth()->user()?->tenant;
        \Illuminate\Support\Facades\Log::info("BillingController tenant", [
            "manager" => $this->tenantManager->get()?->id,
            "auth_tenant" => auth()->user()?->tenant_id,
            "tenant" => $tenant?->id,
        ]);
        if (!$tenant) return redirect()->route(\'login\');
        $planes = Plan::where(\'activo\', true)->orderBy(\'precio_mensual\')->get();';
$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Log agregado OK\n";
