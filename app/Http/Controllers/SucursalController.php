<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SucursalController extends Controller
{
    public function __construct(private TenantManager $manager) {}

    public function index(): View
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;
        $sucursales = Sucursal::where('tenant_id', $tenant->id)
            ->withCount(['usuarios', 'ventas'])
            ->orderBy('nombre')
            ->get();

        return view('sucursales.index', compact('sucursales', 'tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        // Verificar límite del plan
        if (!$tenant->dentroLimite('sucursales')) {
            $max = $tenant->plan->max_sucursales;
            return back()->with('error',
                "Tu plan {$tenant->plan->nombre} permite máximo {$max} sucursal(es). " .
                "<a href='" . route('billing.index') . "' class='underline'>Actualizar plan →</a>"
            );
        }

        $data = $request->validate([
            'nombre'        => 'required|string|max:120',
            'direccion'     => 'nullable|string|max:200',
            'telefono'      => 'nullable|string|max:30',
            'serie_boleta'  => 'required|string|max:10',
            'serie_factura' => 'required|string|max:10',
        ]);

        $sucursal = Sucursal::create([
            ...$data,
            'tenant_id' => $tenant->id,
            'activo'    => true,
        ]);

        // Crear correlativos para la nueva sucursal
        foreach ([
            ['tipo' => 'boleta',       'serie' => $sucursal->serie_boleta],
            ['tipo' => 'factura',      'serie' => $sucursal->serie_factura],
            ['tipo' => 'orden_compra', 'serie' => 'OC-'],
        ] as $corr) {
            \App\Models\Correlativo::create([
                'tenant_id'   => $tenant->id,
                'sucursal_id' => $sucursal->id,
                'tipo'        => $corr['tipo'],
                'serie'       => $corr['serie'],
                'ultimo'      => 0,
            ]);
        }

        return redirect()->route('sucursales.index')
            ->with('ok', "Sucursal \"{$sucursal->nombre}\" creada correctamente.");
    }

    public function update(Request $request, Sucursal $sucursal): RedirectResponse
    {
        // Verificar que pertenece al tenant actual
        $tenant = $this->manager->get() ?? auth()->user()->tenant;
        if ((int) $sucursal->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        $data = $request->validate([
            'nombre'    => 'required|string|max:120',
            'direccion' => 'nullable|string|max:200',
            'telefono'  => 'nullable|string|max:30',
            'activo'    => 'boolean',
        ]);

        $sucursal->update($data);

        return redirect()->route('sucursales.index')
            ->with('ok', "Sucursal actualizada correctamente.");
    }

    public function destroy(Sucursal $sucursal): RedirectResponse
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        if ((int) $sucursal->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        // No permitir eliminar si es la única sucursal
        $total = Sucursal::where('tenant_id', $tenant->id)->count();
        if ($total <= 1) {
            return back()->with('error', 'No puedes eliminar la única sucursal de tu empresa.');
        }

        $sucursal->update(['activo' => false]);

        return redirect()->route('sucursales.index')
            ->with('ok', "Sucursal desactivada correctamente.");
    }

    /**
     * Cambiar la sucursal activa del usuario en sesión.
     */
    public function cambiar(Request $request, Sucursal $sucursal): RedirectResponse
    {
        $tenant = $this->manager->get() ?? auth()->user()->tenant;

        if ((int) $sucursal->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        // Actualizar sucursal del usuario
        auth()->user()->update(['sucursal_id' => $sucursal->id]);
        app()->instance('sucursal', $sucursal);

        return back()->with('ok', "Sucursal activa: {$sucursal->nombre}");
    }
}
