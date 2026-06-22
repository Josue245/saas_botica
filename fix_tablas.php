<?php
$fixes = [
    'app/Models/Sucursal.php'     => 'sucursales',
    'app/Models/Suscripcion.php'  => 'suscripciones',
    'app/Models/Correlativo.php'  => 'correlativos',
    'app/Models/StockSucursal.php'=> 'stock_sucursales',
];

foreach ($fixes as $path => $tabla) {
    $c = file_get_contents($path);
    if (strpos($c, 'protected $table') === false) {
        $c = str_replace(
            'protected $fillable',
            "protected \$table = '{$tabla}';\n\n    protected \$fillable",
            $c
        );
        file_put_contents($path, $c);
        echo "{$path}: table={$tabla} OK\n";
    } else {
        echo "{$path}: ya tiene table definida\n";
    }
}
