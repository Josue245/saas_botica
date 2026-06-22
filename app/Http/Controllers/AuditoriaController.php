<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q', ''));
        $userId = $request->query('usuario');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $logs = Auditoria::with('usuario')
            ->when($buscar !== '', fn ($q) => $q->where('descripcion', 'like', "%{$buscar}%")
                ->orWhere('accion', 'like', "%{$buscar}%")
                ->orWhere('modelo', 'like', "%{$buscar}%"))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $usuarios = User::orderBy('name')->get(['id', 'name']);

        return view('auditoria.index', compact('logs', 'usuarios', 'buscar', 'userId', 'desde', 'hasta'));
    }
}
