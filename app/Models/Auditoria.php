<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Auditoria extends Model
{
    use HasTenant;
    protected $table = 'auditorias';
    protected $fillable = ['user_id', 'accion', 'modelo', 'modelo_id', 'descripcion', 'ip'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function registrar(string $accion, ?string $modelo = null, ?int $modeloId = null, ?string $descripcion = null): void
    {
        static::create([
            'user_id' => Auth::id(),
            'accion' => $accion,
            'modelo' => $modelo,
            'modelo_id' => $modeloId,
            'descripcion' => $descripcion,
            'ip' => request()->ip(),
        ]);
    }
}
