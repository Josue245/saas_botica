<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    public function definition(): array
    {
        $precioCompra = fake()->randomFloat(2, 1, 30);

        return [
            'codigo_barras' => fake()->unique()->ean13(),
            'nombre' => fake()->unique()->words(3, true),
            'principio_activo' => fake()->word(),
            'presentacion' => fake()->randomElement(['Tableta', 'Jarabe', 'Cápsula', 'Crema']),
            'concentracion' => fake()->randomElement(['500mg', '250mg', '10mg/ml']),
            'categoria_id' => Categoria::factory(),
            'proveedor_id' => Proveedor::factory(),
            'laboratorio' => fake()->company(),
            'precio_compra' => $precioCompra,
            'precio_venta' => round($precioCompra * 1.4, 2),
            'stock' => fake()->numberBetween(50, 200),
            'stock_minimo' => 10,
            'lote' => fake()->bothify('L-####'),
            'fecha_vencimiento' => fake()->dateTimeBetween('+6 months', '+2 years'),
            'requiere_receta' => false,
            'controlado' => false,
            'activo' => true,
        ];
    }

    /**
     * Producto con stock muy bajo, para probar validación de stock insuficiente.
     */
    public function stockBajo(int $stock = 2): static
    {
        return $this->state(fn (array $attrs) => ['stock' => $stock]);
    }

    /**
     * Producto sin stock, para probar el rechazo de venta.
     */
    public function sinStock(): static
    {
        return $this->state(fn (array $attrs) => ['stock' => 0]);
    }
}
