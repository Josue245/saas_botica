<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BASELINE — Tests de regresión de Compras (Fase 0).
 * Cubre el flujo de ingreso de inventario, que es la otra mitad
 * crítica del sistema de stock (junto al POS).
 */
class CompraRegressionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_usuario_puede_ver_el_listado_de_compras(): void
    {
        $user = User::factory()->create();
        Compra::factory()->count(2)->create();

        // No existe CompraFactory en el código fuente; se crea vía store() en otros tests.
        // Este test solo valida que la ruta responde correctamente sin datos.
        $response = $this->actingAs($user)->get(route('compras.index'));
        $response->assertOk();
    }

    /** @test */
    public function registra_una_compra_y_aumenta_el_stock_del_producto(): void
    {
        $user = User::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $producto = Producto::factory()->create(['stock' => 10, 'precio_compra' => 5]);

        $items = [[
            'id' => $producto->id,
            'cantidad' => 20,
            'precio_compra' => 6.50,
        ]];

        $response = $this->actingAs($user)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id,
            'estado_pago' => 'pendiente',
            'items_json' => json_encode($items),
        ]);

        $response->assertRedirect();
        $this->assertEquals(30, $producto->fresh()->stock);
    }

    /** @test */
    public function actualiza_el_precio_de_compra_del_producto_al_recibir(): void
    {
        $user = User::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $producto = Producto::factory()->create(['precio_compra' => 5.00, 'stock' => 0]);

        $this->actingAs($user)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id,
            'estado_pago' => 'pagada',
            'items_json' => json_encode([[
                'id' => $producto->id,
                'cantidad' => 5,
                'precio_compra' => 8.75,
            ]]),
        ]);

        $this->assertEquals(8.75, (float) $producto->fresh()->precio_compra);
    }

    /** @test */
    public function actualiza_lote_y_fecha_de_vencimiento_si_se_proporcionan(): void
    {
        $user = User::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $producto = Producto::factory()->create(['lote' => 'VIEJO-001']);

        $this->actingAs($user)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id,
            'estado_pago' => 'pendiente',
            'items_json' => json_encode([[
                'id' => $producto->id,
                'cantidad' => 1,
                'precio_compra' => 3.00,
                'lote' => 'NUEVO-2026-05',
                'fecha_vencimiento' => '2027-12-31',
            ]]),
        ]);

        $producto->refresh();
        $this->assertEquals('NUEVO-2026-05', $producto->lote);
        $this->assertEquals('2027-12-31', $producto->fecha_vencimiento->format('Y-m-d'));
    }

    /** @test */
    public function rechaza_la_compra_sin_items(): void
    {
        $user = User::factory()->create();
        $proveedor = Proveedor::factory()->create();

        $response = $this->actingAs($user)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id,
            'estado_pago' => 'pendiente',
            'items_json' => json_encode([]),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('compras', 0);
    }

    /** @test */
    public function calcula_el_igv_18_por_ciento_sobre_el_subtotal(): void
    {
        $user = User::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $producto = Producto::factory()->create();

        $this->actingAs($user)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id,
            'estado_pago' => 'pendiente',
            'items_json' => json_encode([[
                'id' => $producto->id,
                'cantidad' => 10,
                'precio_compra' => 10.00,
            ]]),
        ]);

        $compra = Compra::first();
        $this->assertEquals(100.00, (float) $compra->subtotal);
        $this->assertEquals(18.00, (float) $compra->igv);
        $this->assertEquals(118.00, (float) $compra->total);
    }

    /** @test */
    public function genera_numero_de_documento_automatico_si_no_se_proporciona(): void
    {
        $user = User::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $producto = Producto::factory()->create();

        $this->actingAs($user)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id,
            'estado_pago' => 'pendiente',
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1, 'precio_compra' => 1]]),
        ]);

        $compra = Compra::first();
        $this->assertStringStartsWith('OC-', $compra->numero_documento);
    }
}
