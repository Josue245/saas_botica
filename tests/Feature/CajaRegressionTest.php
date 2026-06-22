<?php

namespace Tests\Feature;

use App\Models\CajaSesion;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BASELINE — Tests de regresión de Caja (Fase 0).
 * Cubre apertura, cierre, movimientos y el cálculo del cuadre
 * (monto esperado vs monto contado), que es la lógica financiera
 * más delicada del sistema.
 */
class CajaRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Configuracion::guardar(Configuracion::defaults());
    }

    /** @test */
    public function un_usuario_puede_abrir_caja(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('caja.abrir'), [
            'monto_inicial' => 100.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('caja_sesiones', [
            'user_id' => $user->id,
            'estado' => 'abierta',
            'monto_inicial' => 100.00,
        ]);
    }

    /** @test */
    public function no_permite_abrir_dos_cajas_simultaneas(): void
    {
        $user = User::factory()->create();
        CajaSesion::create([
            'user_id' => $user->id,
            'monto_inicial' => 50,
            'estado' => 'abierta',
            'abierta_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('caja.abrir'), [
            'monto_inicial' => 200.00,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('caja_sesiones', 1);
    }

    /** @test */
    public function registra_un_movimiento_de_ingreso_en_caja_abierta(): void
    {
        $user = User::factory()->create();
        CajaSesion::create([
            'user_id' => $user->id,
            'monto_inicial' => 100,
            'estado' => 'abierta',
            'abierta_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('caja.movimientos.store'), [
            'tipo' => 'ingreso',
            'concepto' => 'Préstamo de caja chica',
            'monto' => 50.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('caja_movimientos', [
            'tipo' => 'ingreso',
            'monto' => 50.00,
        ]);
    }

    /** @test */
    public function rechaza_movimiento_si_no_hay_caja_abierta(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('caja.movimientos.store'), [
            'tipo' => 'egreso',
            'concepto' => 'Compra de insumos',
            'monto' => 20.00,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('caja_movimientos', 0);
    }

    /** @test */
    public function calcula_el_monto_esperado_incluyendo_ventas_en_efectivo_e_ingresos_menos_egresos(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['precio_venta' => 20, 'stock' => 100]);

        // Abrir caja con 100 de fondo
        $this->actingAs($user)->post(route('caja.abrir'), ['monto_inicial' => 100.00]);

        // Una venta en efectivo de 20
        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'ticket',
        ]);

        // Ingreso manual de 30
        $this->actingAs($user)->post(route('caja.movimientos.store'), [
            'tipo' => 'ingreso',
            'concepto' => 'Vuelto de banco',
            'monto' => 30.00,
        ]);

        // Egreso de 15
        $this->actingAs($user)->post(route('caja.movimientos.store'), [
            'tipo' => 'egreso',
            'concepto' => 'Propina delivery',
            'monto' => 15.00,
        ]);

        // Cerrar caja con 135 contados (100 + 20 ventas + 30 ingreso - 15 egreso = 135 esperado)
        $response = $this->actingAs($user)->patch(route('caja.cerrar'), [
            'monto_final' => 135.00,
        ]);

        $response->assertRedirect();
        $sesion = CajaSesion::first();
        $this->assertEquals(135.00, (float) $sesion->monto_esperado);
        $this->assertEquals(0.00, (float) $sesion->diferencia);
        $this->assertEquals('cerrada', $sesion->estado);
    }

    /** @test */
    public function detecta_diferencia_de_caja_cuando_el_conteo_no_coincide(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('caja.abrir'), ['monto_inicial' => 100.00]);

        // Se cuenta menos de lo esperado (faltante)
        $this->actingAs($user)->patch(route('caja.cerrar'), [
            'monto_final' => 90.00,
        ]);

        $sesion = CajaSesion::first();
        $this->assertEquals(-10.00, (float) $sesion->diferencia);
    }

    /** @test */
    public function no_permite_cerrar_si_no_hay_caja_abierta(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('caja.cerrar'), [
            'monto_final' => 100.00,
        ]);

        $response->assertSessionHas('error');
    }

    /**
     * @test
     *
     * Ventas en efectivo realizadas ANTES de la apertura de caja
     * no deben contar en el cuadre (filtro por abierta_at).
     */
    public function las_ventas_anteriores_a_la_apertura_no_afectan_el_cuadre(): void
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['precio_venta' => 50, 'stock' => 100]);

        // Venta ANTES de abrir caja
        $this->actingAs($user)->post(route('pos.store'), [
            'items_json' => json_encode([['id' => $producto->id, 'cantidad' => 1]]),
            'metodo_pago' => 'Efectivo',
            'tipo_comprobante' => 'ticket',
        ]);

        // Ahora se abre la caja
        $this->actingAs($user)->post(route('caja.abrir'), ['monto_inicial' => 0.00]);

        $response = $this->actingAs($user)->patch(route('caja.cerrar'), [
            'monto_final' => 0.00,
        ]);

        $sesion = CajaSesion::first();
        // El esperado debe ser 0 (la venta de 50 no debe contarse, fue antes de abrir)
        $this->assertEquals(0.00, (float) $sesion->monto_esperado);
    }
}
