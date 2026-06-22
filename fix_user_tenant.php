<?php
$path = 'app/Models/User.php';
$c = file_get_contents($path);

if (strpos($c, 'HasTenant') !== false) {
    echo "User: ya tiene HasTenant\n";
    exit;
}

$c = str_replace(
    "use Illuminate\Database\Eloquent\Model;",
    "use App\Models\Concerns\HasTenant;\nuse Illuminate\Database\Eloquent\Model;",
    $c
);

// User extiende Authenticatable, no Model — buscar el use de traits existente
if (preg_match('/use\s+HasFactory/', $c)) {
    $c = preg_replace('/(use\s+HasFactory)/', 'use HasTenant, HasFactory', $c, 1);
} else {
    $c = preg_replace(
        '/(class User extends Authenticatable\s*\{)/',
        "$1\n    use HasTenant;",
        $c,
        1
    );
}

file_put_contents($path, $c);
echo "User: HasTenant agregado OK\n";
