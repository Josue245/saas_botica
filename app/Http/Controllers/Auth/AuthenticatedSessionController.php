<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $remember = $request->boolean('remember');

        // Filtrar por tenant activo si hay uno en el contexto
        $tenantId = app()->bound('tenant') && app('tenant') ? app('tenant')->id : null;
        $credencialesCompletas = array_merge($credentials, ['activo' => true]);
        if ($tenantId) {
            $credencialesCompletas['tenant_id'] = $tenantId;
        }
        if (! Auth::attempt($credencialesCompletas, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden o el usuario está inactivo.',
            ]);
        }

        $request->session()->regenerate();
        // Guardar tenant en sesion para persistir entre requests
        if (app()->bound("tenant") && app("tenant")) {
            $request->session()->put("tenant_id", app("tenant")->id);
        } elseif (Auth::user()->tenant_id) {
            $request->session()->put("tenant_id", Auth::user()->tenant_id);
        }

        Auditoria::registrar('inició sesión', 'Sesión', Auth::id(), 'Acceso al sistema');

        $tenant = app()->bound('tenant') && app('tenant') ? app('tenant') : Auth::user()->tenant;
        $redirectUrl = $tenant
            ? route('dashboard') . '?_tenant=' . $tenant->slug
            : route('dashboard');
        return redirect()->intended($redirectUrl);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auditoria::registrar('cerró sesión', 'Sesión', Auth::id(), 'Salida del sistema');

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
