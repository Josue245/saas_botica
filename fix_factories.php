<?php
foreach(['Producto','Proveedor','Cliente','Compra'] as $m) {
    $path = "app/Models/{$m}.php";
    $c = file_get_contents($path);
    if (strpos($c, 'HasFactory') === false) {
        $c = str_replace(
            "use Illuminate\Database\Eloquent\Model;",
            "use Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;",
            $c
        );
        $c = str_replace("extends Model\n{", "extends Model\n{\n    use HasFactory;", $c);
        file_put_contents($path, $c);
        echo $m . " OK\n";
    } else {
        echo $m . " ya tiene HasFactory\n";
    }
}
