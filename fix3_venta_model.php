<?php
$path = 'app/Models/Venta.php';
$c = file_get_contents($path);

if (strpos($c, 'comprobanteSunat') !== false) {
    echo "Relacion ya existe\n";
    exit;
}

// Agregar import
if (strpos($c, 'ComprobanteSunat') === false) {
    $c = str_replace(
        "use Illuminate\Database\Eloquent\Model;",
        "use App\Models\ComprobanteSunat;\nuse Illuminate\Database\Eloquent\Model;",
        $c
    );
}

// Agregar relación al final de la clase (antes del cierre })
$c = preg_replace(
    '/^}$/m',
    "    public function comprobanteSunat(): \\Illuminate\\Database\\Eloquent\\Relations\\HasOne\n    {\n        return \$this->hasOne(ComprobanteSunat::class, 'venta_id');\n    }\n}",
    $c,
    1
);

file_put_contents($path, $c);
echo "FIX 3 OK: relacion comprobanteSunat en Venta\n";
