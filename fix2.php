<?php
// Fix 1: HasFactory en Categoria
$path = "app/Models/Categoria.php";
$c = file_get_contents($path);
if (strpos($c, 'HasFactory') === false) {
    $c = str_replace(
        "use Illuminate\Database\Eloquent\Model;",
        "use Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;",
        $c
    );
    $c = str_replace("extends Model\n{", "extends Model\n{\n    use HasFactory;", $c);
    file_put_contents($path, $c);
    echo "Categoria: HasFactory agregado\n";
}

// Fix 2: RUC fijo de 11 dígitos en ProveedorFactory
$path = "database/factories/ProveedorFactory.php";
$c = file_get_contents($path);
$c = str_replace(
    "'ruc' => fake()->unique()->numerify('20###########'),",
    "'ruc' => fake()->unique()->numerify('20#########'),",
    $c
);
file_put_contents($path, $c);
echo "ProveedorFactory: RUC corregido a 11 digitos\n";
