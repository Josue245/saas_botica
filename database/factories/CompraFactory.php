<?php

namespace Database\Factories;

use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compra>
 */
class CompraFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 500);
        $igv = round($subtotal * 0.18, 2);

        return [
            'numero_documento' => 'OC-' . fake()->unique()->numerify('######'),
            'proveedor_id' => Proveedor::factory(),
            'user_id' => User::factory(),
            'fecha' => now()->toDateString(),
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $subtotal + $igv,
            'estado' => 'recibida',
            'estado_pago' => fake()->randomElement(['pendiente', 'pagada']),
            'observacion' => null,
        ];
    }
}
