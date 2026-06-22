<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q', ''));

        $clientes = Cliente::withCount(['ventas as compras_count' => fn ($q) => $q->where('estado', 'pagada')])
            ->withSum(['ventas as total_gastado' => fn ($q) => $q->where('estado', 'pagada')], 'total')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('numero_documento', 'like', "%{$buscar}%");
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'buscar'));
    }

    public function store(Request $request): RedirectResponse
    {
        Cliente::create($this->validateData($request));

        return redirect()->route('clientes.index')->with('ok', 'Cliente registrado correctamente.');
    }

    public function show(Cliente $cliente): View
    {
        $ventas = $cliente->ventas()
            ->with('usuario')
            ->latest()
            ->paginate(15);

        $totalGastado = (float) $cliente->ventas()->where('estado', 'pagada')->sum('total');
        $numCompras = $cliente->ventas()->where('estado', 'pagada')->count();

        return view('clientes.show', compact('cliente', 'ventas', 'totalGastado', 'numCompras'));
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($this->validateData($request, $cliente));

        return redirect()->route('clientes.index')->with('ok', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        if ($cliente->ventas()->exists()) {
            return redirect()->route('clientes.index')
                ->with('error', 'No se puede eliminar: el cliente tiene ventas registradas.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('ok', 'Cliente eliminado.');
    }

    private function validateData(Request $request, ?Cliente $cliente = null): array
    {
        $id = $cliente?->id ?? 'NULL';

        $data = $request->validate([
            'nombre' => 'required|string|max:160',
            'tipo_documento' => 'required|in:DNI,RUC,CE',
            'numero_documento' => "nullable|string|max:20|unique:clientes,numero_documento,{$id}",
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'numero_documento.unique' => 'Ya existe un cliente con ese documento.',
            'email.email' => 'Ingresa un correo válido.',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        return $data;
    }
}
