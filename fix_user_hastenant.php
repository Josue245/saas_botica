<?php
$path = 'app/Models/User.php';
$c = file_get_contents($path);

// Agregar import del trait si no existe
if (strpos($c, "use App\Models\Concerns\HasTenant;") === false) {
    $c = str_replace(
        "use Illuminate\Database\Eloquent\Factories\HasFactory;",
        "use App\Models\Concerns\HasTenant;\nuse Illuminate\Database\Eloquent\Factories\HasFactory;",
        $c
    );
}

// Reemplazar el docblock por uso real del trait
$c = str_replace(
    '/** @use HasTenant, HasFactory<\Database\Factories\UserFactory> */',
    'use HasTenant, HasFactory;',
    $c
);

file_put_contents($path, $c);
echo "User: HasTenant como trait real OK\n";
