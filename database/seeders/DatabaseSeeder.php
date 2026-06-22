<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Usuarios ----------
        User::updateOrCreate(['email' => 'admin@mibotica.test'], [
            'name' => 'Administrador', 'password' => Hash::make('password'),
            'rol' => 'admin', 'telefono' => '987654321', 'activo' => true,
        ]);
        User::updateOrCreate(['email' => 'farmaceutico@mibotica.test'], [
            'name' => 'Lucía Farfán', 'password' => Hash::make('password'),
            'rol' => 'farmaceutico', 'telefono' => '912345678', 'activo' => true,
        ]);
        User::updateOrCreate(['email' => 'cajero@mibotica.test'], [
            'name' => 'Carlos Ramos', 'password' => Hash::make('password'),
            'rol' => 'cajero', 'telefono' => '900112233', 'activo' => true,
        ]);

        // ---------- Categorías ----------
        $cats = ['Analgésicos', 'Antibióticos', 'Antigripales', 'Vitaminas y Suplementos', 'Dermatológicos', 'Gastrointestinales', 'Cardiovascular', 'Cuidado Personal'];
        $catModels = [];
        foreach ($cats as $c) {
            $catModels[$c] = Categoria::updateOrCreate(['nombre' => $c], ['activo' => true]);
        }

        // ---------- Proveedores ----------
        $provs = [
            ['razon_social' => 'Distribuidora Farma Perú S.A.C.', 'ruc' => '20512345671', 'contacto' => 'Jorge Díaz', 'telefono' => '014567890', 'condicion_pago' => 'Crédito 30 días'],
            ['razon_social' => 'Laboratorios Andinos S.A.', 'ruc' => '20498765432', 'contacto' => 'María Quispe', 'telefono' => '014561122', 'condicion_pago' => 'Contado'],
            ['razon_social' => 'Drogería Salud Total E.I.R.L.', 'ruc' => '20587654329', 'contacto' => 'Pedro Soto', 'telefono' => '013349988', 'condicion_pago' => 'Crédito 15 días'],
        ];
        $provModels = [];
        foreach ($provs as $p) {
            $provModels[] = Proveedor::updateOrCreate(['ruc' => $p['ruc']], $p + ['activo' => true]);
        }

        // ---------- Productos ----------
        $hoy = Carbon::today();
        $productosData = [
            ['Paracetamol 500mg', 'Paracetamol', 'Tableta', '500mg', 'Analgésicos', 0.20, 0.50, 480, 50, 'L-2401', 18],
            ['Ibuprofeno 400mg', 'Ibuprofeno', 'Tableta', '400mg', 'Analgésicos', 0.30, 0.80, 320, 40, 'L-2402', 14],
            ['Aspirina 100mg', 'Ácido acetilsalicílico', 'Tableta', '100mg', 'Cardiovascular', 0.25, 0.60, 210, 30, 'L-2403', 10],
            ['Amoxicilina 500mg', 'Amoxicilina', 'Cápsula', '500mg', 'Antibióticos', 0.40, 1.20, 150, 30, 'L-2404', 8, true],
            ['Azitromicina 500mg', 'Azitromicina', 'Tableta', '500mg', 'Antibióticos', 1.50, 3.50, 60, 20, 'L-2405', 5, true],
            ['Panadol Antigripal', 'Paracetamol + Clorfenamina', 'Tableta', '500mg', 'Antigripales', 0.60, 1.50, 90, 25, 'L-2406', 2],
            ['Sal de Andrews', 'Bicarbonato + Ác. cítrico', 'Sobre', '5g', 'Gastrointestinales', 0.40, 1.00, 200, 30, 'L-2407', 12],
            ['Vitamina C 1g', 'Ácido ascórbico', 'Tableta efervescente', '1g', 'Vitaminas y Suplementos', 0.50, 1.30, 140, 25, 'L-2408', 16],
            ['Complejo B', 'Vitaminas B', 'Tableta', '-', 'Vitaminas y Suplementos', 0.70, 1.80, 75, 20, 'L-2409', 20],
            ['Loratadina 10mg', 'Loratadina', 'Tableta', '10mg', 'Antigripales', 0.30, 0.90, 18, 25, 'L-2410', 9],
            ['Omeprazol 20mg', 'Omeprazol', 'Cápsula', '20mg', 'Gastrointestinales', 0.35, 1.00, 130, 30, 'L-2411', 11],
            ['Crema Hidratante Cerave', 'Cosmético', 'Crema', '236ml', 'Dermatológicos', 18.00, 39.90, 22, 8, 'L-2412', 24],
            ['Protector Solar FPS50', 'Cosmético', 'Loción', '60ml', 'Cuidado Personal', 22.00, 45.00, 14, 6, 'L-2413', 22],
            ['Alcohol en gel 250ml', 'Etanol', 'Gel', '250ml', 'Cuidado Personal', 3.00, 6.50, 5, 15, 'L-2414', -1], // vencido
            ['Enalapril 10mg', 'Enalapril', 'Tableta', '10mg', 'Cardiovascular', 0.30, 0.85, 95, 25, 'L-2415', 1], // por vencer pronto
            ['Metformina 850mg', 'Metformina', 'Tableta', '850mg', 'Gastrointestinales', 0.25, 0.70, 160, 30, 'L-2416', 15],
        ];

        $productos = [];
        foreach ($productosData as $i => $d) {
            $vencMeses = $d[10];
            $venc = $vencMeses < 0 ? (clone $hoy)->subDays(20) : (clone $hoy)->addMonths($vencMeses);
            $productos[] = Producto::updateOrCreate(
                ['nombre' => $d[0]],
                [
                    'codigo_barras' => '775000000' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'principio_activo' => $d[1],
                    'presentacion' => $d[2],
                    'concentracion' => $d[3],
                    'categoria_id' => $catModels[$d[4]]->id,
                    'proveedor_id' => $provModels[$i % count($provModels)]->id,
                    'laboratorio' => ['Genfar', 'Bayer', 'Medifarma', 'Hersil', 'GSK'][$i % 5],
                    'precio_compra' => $d[5],
                    'precio_venta' => $d[6],
                    'stock' => $d[7],
                    'stock_minimo' => $d[8],
                    'lote' => $d[9],
                    'fecha_vencimiento' => $venc,
                    'requiere_receta' => $d[11] ?? false,
                    'activo' => true,
                ]
            );
        }

        // ---------- Clientes ----------
        $clientesData = [
            ['Cliente Varios', 'DNI', '00000000'],
            ['Rosa Huamán Vega', 'DNI', '45678912'],
            ['José Mendoza Ríos', 'DNI', '40123456'],
            ['Comercial San Juan S.A.C.', 'RUC', '20601234567'],
            ['Ana Torres Salas', 'DNI', '70654321'],
            ['Luis Paredes Gómez', 'DNI', '41239876'],
        ];
        $clientes = [];
        foreach ($clientesData as $c) {
            $clientes[] = Cliente::updateOrCreate(
                ['numero_documento' => $c[2]],
                ['nombre' => $c[0], 'tipo_documento' => $c[1], 'activo' => true, 'puntos' => rand(0, 120)]
            );
        }

        // ---------- Ventas (últimos 14 días) ----------
        if (Venta::count() === 0) {
            $usuarios = User::pluck('id')->all();
            $metodos = ['Efectivo', 'Efectivo', 'Tarjeta', 'Yape', 'Plin', 'Transferencia'];

            for ($dia = 13; $dia >= 0; $dia--) {
                $fecha = (clone $hoy)->subDays($dia);
                $numVentas = rand(6, 16); // ventas por día

                for ($v = 0; $v < $numVentas; $v++) {
                    $momento = (clone $fecha)->setTime(rand(8, 20), rand(0, 59));
                    $venta = new Venta([
                        'numero_comprobante' => 'B001-' . str_pad((string) (Venta::count() + 1), 6, '0', STR_PAD_LEFT),
                        'tipo_comprobante' => 'boleta',
                        'cliente_id' => $clientes[array_rand($clientes)]->id,
                        'user_id' => $usuarios[array_rand($usuarios)],
                        'metodo_pago' => $metodos[array_rand($metodos)],
                        'estado' => 'pagada',
                    ]);
                    $venta->created_at = $momento;
                    $venta->updated_at = $momento;
                    $venta->subtotal = 0;
                    $venta->total = 0;
                    $venta->save();

                    $subtotalVenta = 0;
                    $items = rand(1, 4);
                    $usados = [];
                    for ($it = 0; $it < $items; $it++) {
                        $prod = $productos[array_rand($productos)];
                        if (in_array($prod->id, $usados, true)) {
                            continue;
                        }
                        $usados[] = $prod->id;
                        $cant = rand(1, 5);
                        $precio = (float) $prod->precio_venta;
                        $sub = round($cant * $precio, 2);
                        $subtotalVenta += $sub;

                        $detalle = new VentaDetalle([
                            'producto_id' => $prod->id,
                            'descripcion' => $prod->nombre,
                            'cantidad' => $cant,
                            'precio_unitario' => $precio,
                            'subtotal' => $sub,
                        ]);
                        $detalle->venta_id = $venta->id;
                        $detalle->created_at = $momento;
                        $detalle->updated_at = $momento;
                        $detalle->save();
                    }

                    // IGV incluido (18%): subtotal mostrado y total
                    $total = round($subtotalVenta, 2);
                    $base = round($total / 1.18, 2);
                    $igv = round($total - $base, 2);
                    $venta->subtotal = $base;
                    $venta->igv = $igv;
                    $venta->total = $total;
                    $venta->con_receta = (bool) rand(0, 1) && $total > 5;
                    $venta->save();
                }
            }
        }
    }
}
