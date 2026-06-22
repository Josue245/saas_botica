<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q', ''));

        $categorias = Categoria::withCount('productos')
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('categorias.index', compact('categorias', 'buscar'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        Categoria::create($data);

        return redirect()->route('categorias.index')->with('ok', 'Categoría creada correctamente.');
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $data = $this->validateData($request, $categoria);
        $categoria->update($data);

        return redirect()->route('categorias.index')->with('ok', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->productos()->exists()) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar: la categoría tiene productos asociados.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('ok', 'Categoría eliminada.');
    }

    private function validateData(Request $request, ?Categoria $categoria = null): array
    {
        $id = $categoria?->id ?? 'NULL';

        return $request->validate([
            'nombre' => "required|string|max:120|unique:categorias,nombre,{$id}",
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]) + ['activo' => $request->boolean('activo')];
    }
}
