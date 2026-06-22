<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RespaldoController extends Controller
{
    private function carpeta(): string
    {
        $dir = storage_path('app/backups');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    public function index(): View
    {
        $dir = $this->carpeta();
        $archivos = collect(File::files($dir))
            ->filter(fn ($f) => $f->getExtension() === 'sql')
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->map(fn ($f) => [
                'nombre' => $f->getFilename(),
                'tamano' => $f->getSize(),
                'fecha' => date('d/m/Y H:i', $f->getMTime()),
            ])
            ->values();

        return view('respaldos.index', ['archivos' => $archivos]);
    }

    public function generar(): RedirectResponse
    {
        if (DB::getDriverName() !== 'mysql') {
            return back()->with('error', 'El respaldo automático solo está disponible para MySQL.');
        }

        try {
            $sql = $this->volcarBaseDatos();
            $nombre = 'backup_' . now()->format('Ymd_His') . '.sql';
            File::put($this->carpeta() . DIRECTORY_SEPARATOR . $nombre, $sql);

            Auditoria::registrar('generó respaldo', 'Respaldo', null, $nombre);
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo generar el respaldo: ' . $e->getMessage());
        }

        return redirect()->route('respaldos.index')->with('ok', "Respaldo {$nombre} generado correctamente.");
    }

    public function descargar(string $archivo): BinaryFileResponse|RedirectResponse
    {
        $nombre = basename($archivo);
        $ruta = $this->carpeta() . DIRECTORY_SEPARATOR . $nombre;

        if (! str_ends_with($nombre, '.sql') || ! File::exists($ruta)) {
            return back()->with('error', 'El archivo de respaldo no existe.');
        }

        return response()->download($ruta);
    }

    /**
     * Genera un volcado SQL de toda la base de datos (estructura + datos).
     */
    private function volcarBaseDatos(): string
    {
        $pdo = DB::getPdo();
        $database = DB::getDatabaseName();
        $tablas = array_map(fn ($r) => array_values((array) $r)[0], DB::select('SHOW TABLES'));

        $salida = "-- Respaldo de base de datos: {$database}\n";
        $salida .= '-- Generado: ' . now()->toDateTimeString() . "\n";
        $salida .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tablas as $tabla) {
            $create = DB::select("SHOW CREATE TABLE `{$tabla}`")[0];
            $createSql = ((array) $create)['Create Table'] ?? '';

            $salida .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
            $salida .= $createSql . ";\n\n";

            $filas = DB::table($tabla)->get();
            if ($filas->isEmpty()) {
                continue;
            }

            $columnas = array_keys((array) $filas->first());
            $colList = '`' . implode('`, `', $columnas) . '`';

            foreach ($filas as $fila) {
                $valores = array_map(function ($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote((string) $v);
                }, array_values((array) $fila));

                $salida .= "INSERT INTO `{$tabla}` ({$colList}) VALUES (" . implode(', ', $valores) . ");\n";
            }
            $salida .= "\n";
        }

        $salida .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $salida;
    }
}
