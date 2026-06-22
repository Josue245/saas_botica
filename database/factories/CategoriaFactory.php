<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement([
                'Analgésicos', 'Antibióticos', 'Antigripales', 'Vitaminas',
                'Cuidado personal', 'Dermatológicos', 'Material médico',
            ]) . ' ' . fake()->unique()->numerify('##'),
        ];
    }
}
