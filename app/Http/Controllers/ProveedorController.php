<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q', ''));

        $proveedores = Proveedor::withCount('productos')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('ruc', 'like', "%{$buscar}%");
            })
            ->orderBy('razon_social')
            ->paginate(10)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores', 'buscar'));
    }

    public function store(Request $request): RedirectResponse
    {
        Proveedor::create($this->validateData($request));

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor registrado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update($this->validateData($request, $proveedor));

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        if ($proveedor->productos()->exists()) {
            return redirect()->route('proveedores.index')
                ->with('error', 'No se puede eliminar: el proveedor tiene productos asociados.');
        }

        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor eliminado.');
    }

    private function validateData(Request $request, ?Proveedor $proveedor = null): array
    {
        $id = $proveedor?->id ?? 'NULL';

        $data = $request->validate([
            'razon_social' => 'required|string|max:160',
            'ruc' => "nullable|string|max:11|unique:proveedores,ruc,{$id}",
            'contacto' => 'nullable|string|max:120',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:200',
            'condicion_pago' => 'nullable|string|max:60',
        ], [
            'razon_social.required' => 'La razón social es obligatoria.',
            'ruc.unique' => 'Ya existe un proveedor con ese RUC.',
            'email.email' => 'Ingresa un correo válido.',
        ]);

        $data['activo'] = $request->boolean('activo');

        return $data;
    }
}
