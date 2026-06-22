<?php
$path = 'app/Http/Controllers/Auth/AuthenticatedSessionController.php';
$c = file_get_contents($path);

$old = '$request->session()->regenerate();';
$new = '$request->session()->regenerate();
        // Guardar tenant en sesion para persistir entre requests
        if (app()->bound("tenant") && app("tenant")) {
            $request->session()->put("tenant_id", app("tenant")->id);
        } elseif (Auth::user()->tenant_id) {
            $request->session()->put("tenant_id", Auth::user()->tenant_id);
        }';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Login: tenant guardado en sesion OK\n";
