<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function index(): View
    {
        return view('configuracion.index', ['config' => Configuracion::todas()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empresa_nombre' => 'required|string|max:120',
            'empresa_ruc' => 'nullable|string|max:11',
            'empresa_direccion' => 'nullable|string|max:200',
            'empresa_telefono' => 'nullable|string|max:40',
            'empresa_email' => 'nullable|email|max:120',
            'igv_porcentaje' => 'required|numeric|min:0|max:100',
            'moneda_simbolo' => 'required|string|max:5',
            'serie_boleta' => 'required|string|max:10',
            'serie_factura' => 'required|string|max:10',
            'mensaje_ticket' => 'nullable|string|max:200',
        ], [
            'empresa_nombre.required' => 'El nombre de la empresa es obligatorio.',
            'igv_porcentaje.required' => 'Indica el porcentaje de IGV.',
        ]);

        Configuracion::guardar($data);

        return redirect()->route('configuracion.index')->with('ok', 'Configuración guardada correctamente.');
    }
}
