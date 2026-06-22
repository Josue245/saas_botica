<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalController extends Controller
{
    public const ROLES = ['admin' => 'Administrador', 'farmaceutico' => 'Farmacéutico', 'cajero' => 'Cajero', 'vendedor' => 'Vendedor'];

    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q', ''));

        $usuarios = User::when($buscar !== '', function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")->orWhere('email', 'like', "%{$buscar}%");
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('personal.index', ['usuarios' => $usuarios, 'buscar' => $buscar, 'roles' => self::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120|unique:users,email',
            'rol' => ['required', Rule::in(array_keys(self::ROLES))],
            'telefono' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
        ], $this->mensajes());

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'rol' => $data['rol'],
            'telefono' => $data['telefono'] ?? null,
            'password' => Hash::make($data['password']),
            'activo' => $request->boolean('activo', true),
            'tenant_id' => app()->bound('tenant') ? app('tenant')->id : null,
            'sucursal_id' => auth()->user()->sucursal_id ?? null,
        ]);

        return redirect()->route('personal.index')->with('ok', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($usuario->id)],
            'rol' => ['required', Rule::in(array_keys(self::ROLES))],
            'telefono' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
        ], $this->mensajes());

        // Evita que un admin se quite a sí mismo el rol de admin y se bloquee
        if ($usuario->id === $request->user()->id && $data['rol'] !== 'admin') {
            return back()->with('error', 'No puedes cambiar tu propio rol de administrador.');
        }

        $usuario->name = $data['name'];
        $usuario->email = $data['email'];
        $usuario->rol = $data['rol'];
        $usuario->telefono = $data['telefono'] ?? null;
        $usuario->activo = $usuario->id === $request->user()->id ? true : $request->boolean('activo', true);
        if (! empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }
        $usuario->save();

        return redirect()->route('personal.index')->with('ok', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()->id) {
            return redirect()->route('personal.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('personal.index')->with('ok', 'Usuario eliminado.');
    }

    private function mensajes(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'rol.required' => 'Selecciona un rol.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ];
    }
}
