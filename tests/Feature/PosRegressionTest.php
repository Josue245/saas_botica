<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BASELINE — Tests de regresión del POS, escritos ANTES de iniciar
 * la migración multi-tenant (Fase 0 del roadmap).
 *
 * Objetivo: capturar el comportamiento ACTUAL del sistema mono-tenant.
 * Estos tests deben seguir pasando en TODAS las fases de la migración.
 * Si uno de estos falla durante la Fase 2/3 (Global Scopes), es una
 * señal de regresión real, no de "comportamiento esperado nuevo".
 */
class PosRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Configuración base que el POS necesita (IGV, series)
        Configuracion::guardar(Configuracion::defaults());
    }

    /** @test */
    public function un_usuario_autenticado_puede_ver_el_pos(): void
    {
        $user = User::factory()->create(['rol' => 'vendedor']);
        Producto::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('pos.index'));

        $response->assertOk();
        $response->assertViewIs('pos.index');
        $response->assertViewHas('productos');
    }

    /** @test */
    public function registra_una_venta_simple_con_un_producto(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create([
            'precio_venta' => 10.00,
            'stock' => 50,
        ]);

        $items = [['id' => $producto->id, 'cantidad' => 2]];

        $response = $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode($items),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'boleta',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('ok');

        $this->assertDatabaseHas('ventas', [
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'boleta',
            'total' => 20.00,
            'estado' => 'pagada',
        ]);

        $this->assertDatabaseHas('venta_detalles', [
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);
    }

    /** @test */
    public function descuenta_el_stock_del_producto_tras_la_venta(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['stock' => 50, 'precio_venta' => 5]);

        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 7]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'ticket',
        ]);

        $this->assertEquals(43, $producto->fresh()->stock);
    }

    /** @test */
    public function rechaza_la_venta_si_no_hay_stock_suficiente(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->stockBajo(3)->create(['precio_venta' => 5]);

        $response = $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 10]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'ticket',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('ventas', 0);
        // El stock NO debe haberse modificado
        $this->assertEquals(3, $producto->fresh()->stock);
    }

    /** @test */
    public function rechaza_la_venta_con_carrito_vacio(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'ticket',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('ventas', 0);
    }

    /** @test */
    public function calcula_correctamente_el_igv_sobre_el_total(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['precio_venta' => 118.00, 'stock' => 10]);

        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'boleta',
        ]);

        $venta = Venta::first();

        // Total 118 con IGV 18% -> base 100.00, igv 18.00
        $this->assertEquals(100.00, (float) $venta->subtotal);
        $this->assertEquals(18.00, (float) $venta->igv);
        $this->assertEquals(118.00, (float) $venta->total);
    }

    /** @test */
    public function aplica_el_descuento_antes_de_calcular_el_igv(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['precio_venta' => 100.00, 'stock' => 10]);

        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'boleta',
            'descuento' => 10.00,
        ]);

        $venta = Venta::first();
        $this->assertEquals(90.00, (float) $venta->total);
    }

    /** @test */
    public function genera_el_numero_de_comprobante_con_la_serie_correcta(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'factura',
        ]);

        $venta = Venta::first();
        $this->assertStringStartsWith('F001-', $venta->numero_comprobante);
    }

    /**
     * @test
     *
     * Este test documenta el comportamiento ACTUAL (con bug) del correlativo.
     * Cuando se implemente CorrelativoService (Fase de billing/correlativos),
     * este test debe actualizarse para reflejar el nuevo comportamiento
     * basado en tabla `correlativos` con lockForUpdate, no en Venta::count().
     */
    public function el_correlativo_actual_se_basa_en_el_conteo_de_ventas(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['stock' => 100]);

        // Primera venta
        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'boleta',
        ]);

        // Segunda venta
        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'boleta',
        ]);

        $ventas = Venta::orderBy('id')->pluck('numero_comprobante');
        $this->assertEquals('B001-000001', $ventas[0]);
        $this->assertEquals('B001-000002', $ventas[1]);
    }

    /** @test */
    public function una_venta_puede_asociarse_a_un_cliente(): void
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'boleta',
            'cliente_id' => $cliente->id,
        ]);

        $this->assertDatabaseHas('ventas', ['cliente_id' => $cliente->id]);
    }

    /** @test */
    public function rechaza_la_venta_si_un_producto_del_carrito_no_existe(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => 99999, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'ticket',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('ventas', 0);
    }

    /**
     * @test
     *
     * CRÍTICO para la migración: documenta que actualmente NO hay
     * aislamiento de stock concurrente entre "tenants" (porque no existen).
     * Sirve como referencia de comportamiento single-tenant.
     */
    public function el_stock_se_descuenta_de_forma_atomica_dentro_de_la_transaccion(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['stock' => 5, 'precio_venta' => 10]);

        // Venta que excede el stock disponible debe fallar SIN modificar nada
        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 6]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'ticket',
        ]);

        $this->assertEquals(5, $producto->fresh()->stock);
        $this->assertDatabaseCount('venta_detalles', 0);
    }
}
