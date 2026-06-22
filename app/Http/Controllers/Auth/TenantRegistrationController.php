<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Correlativo;
use App\Models\Plan;
use App\Models\Sucursal;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantRegistrationController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        $planes = Plan::where('activo', true)->get();
        return view('auth.register', compact('planes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'razon_social'  => 'required|string|max:160',
            'ruc'           => 'nullable|string|size:11|unique:tenants,ruc',
            'email_empresa' => 'required|email|max:120|unique:tenants,email',
            'nombre_admin'  => 'required|string|max:120',
            'email_admin'   => 'required|email|max:120|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
        ], [
            'razon_social.required'  => 'El nombre de tu empresa es requerido.',
            'ruc.size'               => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.unique'             => 'Este RUC ya está registrado.',
            'email_empresa.unique'   => 'Este correo de empresa ya está registrado.',
            'email_admin.unique'     => 'Este correo de administrador ya está en uso.',
            'password.min'           => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'     => 'Las contraseñas no coinciden.',
        ]);

        try {
            $result = DB::transaction(function () use ($data) {
                $planFree = Plan::free();

                // 1. Crear Tenant
                $tenant = Tenant::create([
                    'razon_social'    => $data['razon_social'],
                    'ruc'             => $data['ruc'] ?? null,
                    'email'           => $data['email_empresa'],
                    'slug'            => $this->slugUnico($data['razon_social']),
                    'plan_id'         => $planFree->id,
                    'estado'          => 'trial',
                    'trial_expira_at' => now()->addDays(14),
                ]);

                // 2. Sucursal principal (sin HasTenant aún — insert directo)
                $sucursal = Sucursal::create([
                    'tenant_id'     => $tenant->id,
                    'nombre'        => $data['razon_social'],
                    'activo'        => true,
                    'serie_boleta'  => 'B001',
                    'serie_factura' => 'F001',
                ]);

                // 3. Usuario admin (insert directo sin scope)
                $userId = DB::table('users')->insertGetId([
                    'tenant_id'   => $tenant->id,
                    'sucursal_id' => $sucursal->id,
                    'name'        => $data['nombre_admin'],
                    'email'       => $data['email_admin'],
                    'password'    => Hash::make($data['password']),
                    'rol'         => 'admin',
                    'activo'      => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $user = User::withoutGlobalScopes()->find($userId);

                // 4. Configuraciones por defecto
                foreach (Configuracion::defaults() as $clave => $valor) {
                    DB::table('configuraciones')->insert([
                        'tenant_id'  => $tenant->id,
                        'clave'      => $clave,
                        'valor'      => $valor,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 5. Correlativos iniciales
                foreach ([
                    ['tipo' => 'boleta',       'serie' => 'B001'],
                    ['tipo' => 'factura',       'serie' => 'F001'],
                    ['tipo' => 'orden_compra',  'serie' => 'OC-'],
                ] as $corr) {
                    Correlativo::create([
                        'tenant_id'   => $tenant->id,
                        'sucursal_id' => $sucursal->id,
                        'tipo'        => $corr['tipo'],
                        'serie'       => $corr['serie'],
                        'ultimo'      => 0,
                    ]);
                }

                return ['tenant' => $tenant, 'user' => $user];
            });
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', 'Error al crear la cuenta: ' . $e->getMessage());
        }

        // Login automático
        auth()->login($result['user']);

        // Redirect al dashboard del nuevo tenant
        $baseUrl = config('app.env') === 'local'
            ? url('/dashboard') . '?_tenant=' . $result['tenant']->slug
            : 'https://' . $result['tenant']->slug . '.' . config('app.base_domain') . '/dashboard';

        return redirect($baseUrl)
            ->with('ok', '¡Bienvenido! Tu prueba gratuita de 14 días está activa.');
    }

    private function slugUnico(string $razonSocial): string
    {
        $base = Str::slug($razonSocial);
        $slug = $base;
        $i = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
