<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'tipo_documento' => 'DNI',
            'numero_documento' => fake()->unique()->numerify('########'),
            'telefono' => fake()->numerify('9########'),
            'email' => fake()->safeEmail(),
            'direccion' => fake()->address(),
            'puntos' => 0,
            'activo' => true,
        ];
    }
}
