<?php
$path = 'app/Http/Controllers/BillingController.php';
$c = file_get_contents($path);

// Cambiar para obtener tenant desde auth() si TenantManager no lo tiene
$old = "        \$tenant = \$this->tenantManager->get();
        \$planes = Plan::where('activo', true)->orderBy('precio_mensual')->get();
        \$suscripcionActual = Suscripcion::where('tenant_id', \$tenant->id)";

$new = "        \$tenant = \$this->tenantManager->get() ?? auth()->user()?->tenant;
        if (!\$tenant) return redirect()->route('login');
        \$planes = Plan::where('activo', true)->orderBy('precio_mensual')->get();
        \$suscripcionActual = Suscripcion::where('tenant_id', \$tenant->id)";

$c = str_replace($old, $new, $c);

// Fix checkout tambien
$c = str_replace(
    "        \$tenant = \$this->tenantManager->get();

        // Si es plan free",
    "        \$tenant = \$this->tenantManager->get() ?? auth()->user()?->tenant;
        if (!\$tenant) return redirect()->route('login');

        // Si es plan free",
    $c
);

// Fix historial
$c = str_replace(
    "        \$tenant = \$this->tenantManager->get();
        \$suscripciones = Suscripcion::where('tenant_id', \$tenant->id)",
    "        \$tenant = \$this->tenantManager->get() ?? auth()->user()?->tenant;
        if (!\$tenant) return redirect()->route('login');
        \$suscripciones = Suscripcion::where('tenant_id', \$tenant->id)",
    $c
);

// Fix pagar
$c = str_replace(
    "        \$tenant = \$this->tenantManager->get();

        try {",
    "        \$tenant = \$this->tenantManager->get() ?? auth()->user()?->tenant;
        if (!\$tenant) return redirect()->route('login');

        try {",
    $c
);

file_put_contents($path, $c);
echo "BillingController: tenant desde auth OK\n";
