<?php
$path = 'app/Http/Controllers/Auth/AuthenticatedSessionController.php';
$c = file_get_contents($path);

// Fix 1: agregar tenant_id al intento de login
$old = "if (! Auth::attempt(array_merge(\$credentials, ['activo' => true]), \$remember)) {";
$new = "// Filtrar por tenant activo si hay uno en el contexto
        \$tenantId = app()->bound('tenant') && app('tenant') ? app('tenant')->id : null;
        \$credencialesCompletas = array_merge(\$credentials, ['activo' => true]);
        if (\$tenantId) {
            \$credencialesCompletas['tenant_id'] = \$tenantId;
        }
        if (! Auth::attempt(\$credencialesCompletas, \$remember)) {";

// Fix 2: redirect preservando el tenant
$old2 = "return redirect()->intended(route('dashboard'));";
$new2 = "\$tenant = app()->bound('tenant') && app('tenant') ? app('tenant') : Auth::user()->tenant;
        \$redirectUrl = \$tenant
            ? route('dashboard') . '?_tenant=' . \$tenant->slug
            : route('dashboard');
        return redirect()->intended(\$redirectUrl);";

$c = str_replace($old, $new, $c);
$c = str_replace($old2, $new2, $c);
file_put_contents($path, $c);
echo "AuthenticatedSessionController: fixes aplicados OK\n";
