<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proveedor>
 */
class ProveedorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'razon_social' => fake()->company() . ' S.A.C.',
            'ruc' => fake()->unique()->numerify('20#########'),
            'contacto' => fake()->name(),
            'telefono' => fake()->numerify('9########'),
            'email' => fake()->companyEmail(),
            'direccion' => fake()->address(),
            'condicion_pago' => fake()->randomElement(['contado', '15 días', '30 días']),
            'activo' => true,
        ];
    }
}
