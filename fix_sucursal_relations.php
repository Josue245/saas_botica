<?php
$path = 'app/Models/Sucursal.php';
$c = file_get_contents($path);

if (strpos($c, 'ventas()') !== false) {
    echo "Relaciones ya existen\n";
    exit;
}

$c = str_replace(
    "    public function stockProductos(): HasMany
    {
        return \$this->hasMany(StockSucursal::class);
    }",
    "    public function stockProductos(): HasMany
    {
        return \$this->hasMany(StockSucursal::class);
    }

    public function ventas(): HasMany
    {
        return \$this->hasMany(Venta::class);
    }

    public function compras(): HasMany
    {
        return \$this->hasMany(Compra::class);
    }",
    $c
);

// Agregar imports necesarios
if (strpos($c, 'use App\Models\Venta') === false) {
    $c = str_replace(
        "use Illuminate\Database\Eloquent\Model;",
        "use App\Models\Compra;\nuse App\Models\Venta;\nuse Illuminate\Database\Eloquent\Model;",
        $c
    );
}

file_put_contents($path, $c);
echo "Sucursal: relaciones ventas/compras OK\n";
