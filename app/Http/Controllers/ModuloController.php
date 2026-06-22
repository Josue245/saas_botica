<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuloController extends Controller
{
    /**
     * Metadatos de cada módulo (placeholder funcional) indexados por nombre de ruta.
     */
    protected array $modulos = [
        'caja.index' => [
            'seccion' => 'Comercio & Ventas', 'icon' => 'cash', 'titulo' => 'Gestión de Caja — Apertura / Cierre',
            'descripcion' => 'Controla la apertura y el cierre de caja por turno, con arqueo y conciliación de efectivo.',
            'funciones' => ['Apertura de caja con monto inicial', 'Cierre y arqueo de caja por turno', 'Registro de cajeros responsables', 'Detección de diferencias de efectivo', 'Reporte de cierre imprimible'],
        ],
        'caja.movimientos' => [
            'seccion' => 'Comercio & Ventas', 'icon' => 'cash', 'titulo' => 'Movimientos de Caja',
            'descripcion' => 'Ingresos y egresos de efectivo distintos a ventas: pagos a proveedores, gastos y retiros.',
            'funciones' => ['Registro de ingresos y egresos', 'Categorización de gastos', 'Adjuntar comprobantes', 'Saldo de caja en tiempo real', 'Historial filtrable por fecha'],
        ],
        'pos.index' => [
            'seccion' => 'Comercio & Ventas', 'icon' => 'cart', 'titulo' => 'Punto de Venta (POS)',
            'descripcion' => 'Pantalla de venta rápida para atención en mostrador, con búsqueda por código de barras.',
            'funciones' => ['Búsqueda por nombre o código de barras', 'Carrito con cálculo automático de IGV', 'Múltiples métodos de pago', 'Venta con o sin receta médica', 'Impresión de boleta/ticket', 'Descuento de stock automático'],
        ],
        'ventas.index' => [
            'seccion' => 'Comercio & Ventas', 'icon' => 'list', 'titulo' => 'Historial de Ventas',
            'descripcion' => 'Consulta, filtra y gestiona todas las ventas realizadas en la botica.',
            'funciones' => ['Listado de ventas con filtros por fecha y cajero', 'Detalle de cada comprobante', 'Anulación y notas de crédito', 'Reimpresión de comprobantes', 'Exportación a Excel/PDF'],
        ],
        'clientes.index' => [
            'seccion' => 'Comercio & Ventas', 'icon' => 'users', 'titulo' => 'Clientes',
            'descripcion' => 'Base de datos de clientes con historial de compras y datos de facturación.',
            'funciones' => ['Registro de clientes (DNI/RUC)', 'Historial de compras por cliente', 'Datos de contacto y facturación', 'Programa de fidelización / puntos', 'Búsqueda rápida'],
        ],
        'productos.index' => [
            'seccion' => 'Logística & Inventario', 'icon' => 'box', 'titulo' => 'Productos',
            'descripcion' => 'Catálogo maestro de medicamentos y productos con precios, principio activo y presentación.',
            'funciones' => ['Alta de productos con código de barras', 'Principio activo y laboratorio', 'Precio de compra y venta', 'Control de stock mínimo', 'Producto controlado / requiere receta', 'Imagen del producto'],
        ],
        'categorias.index' => [
            'seccion' => 'Logística & Inventario', 'icon' => 'box', 'titulo' => 'Categorías',
            'descripcion' => 'Organiza los productos por categorías terapéuticas o comerciales.',
            'funciones' => ['Crear y editar categorías', 'Asignar productos a categorías', 'Categorías activas/inactivas', 'Reportes por categoría'],
        ],
        'compras.index' => [
            'seccion' => 'Logística & Inventario', 'icon' => 'truck', 'titulo' => 'Compras',
            'descripcion' => 'Registro de compras a proveedores con ingreso de mercadería y actualización de stock.',
            'funciones' => ['Orden de compra a proveedores', 'Recepción de mercadería', 'Registro de lotes y vencimientos', 'Actualización automática de stock', 'Cuentas por pagar'],
        ],
        'proveedores.index' => [
            'seccion' => 'Logística & Inventario', 'icon' => 'building', 'titulo' => 'Proveedores',
            'descripcion' => 'Administra tus laboratorios y distribuidoras con datos de contacto y condiciones.',
            'funciones' => ['Registro de proveedores (RUC)', 'Datos de contacto y vendedor', 'Condiciones de pago', 'Historial de compras', 'Productos por proveedor'],
        ],
        'inventario.index' => [
            'seccion' => 'Logística & Inventario', 'icon' => 'layers', 'titulo' => 'Inventario — Stock Actual',
            'descripcion' => 'Visión en tiempo real del stock disponible por producto y ubicación.',
            'funciones' => ['Stock actual por producto', 'Valorización de inventario', 'Filtro por categoría y stock bajo', 'Kardex por producto', 'Exportación'],
        ],
        'inventario.lotes' => [
            'seccion' => 'Logística & Inventario', 'icon' => 'layers', 'titulo' => 'Lotes y Vencimientos',
            'descripcion' => 'Control de lotes con seguimiento de fechas de vencimiento (FEFO).',
            'funciones' => ['Registro por lote', 'Alertas de productos por vencer', 'Salida FEFO (primero en vencer)', 'Reporte de vencidos', 'Bloqueo de venta de vencidos'],
        ],
        'inventario.ajustes' => [
            'seccion' => 'Logística & Inventario', 'icon' => 'layers', 'titulo' => 'Ajustes de Stock',
            'descripcion' => 'Ajustes de inventario por mermas, roturas, vencimientos o conteo físico.',
            'funciones' => ['Ajuste positivo/negativo', 'Motivo del ajuste', 'Conteo físico / toma de inventario', 'Trazabilidad de ajustes', 'Aprobación por responsable'],
        ],
        'alertas.index' => [
            'seccion' => 'Gerencia & Control', 'icon' => 'bell', 'titulo' => 'Alertas Sanitarias',
            'descripcion' => 'Centro de alertas: productos por vencer, vencidos y con stock por debajo del mínimo.',
            'funciones' => ['Productos por vencer (configurable)', 'Productos vencidos', 'Stock bajo el mínimo', 'Notificaciones', 'Acciones rápidas (reponer/retirar)'],
        ],
        'reportes.index' => [
            'seccion' => 'Gerencia & Control', 'icon' => 'chart', 'titulo' => 'Reportes PDF/Excel',
            'descripcion' => 'Genera reportes de ventas, inventario, compras y rentabilidad exportables.',
            'funciones' => ['Reporte de ventas por período', 'Reporte de inventario valorizado', 'Productos más y menos vendidos', 'Rentabilidad por producto', 'Exportación a PDF y Excel'],
        ],
        'personal.index' => [
            'seccion' => 'Ajustes & Sistema', 'icon' => 'badge', 'titulo' => 'Gestión de Personal',
            'descripcion' => 'Administra usuarios del sistema, roles y permisos de acceso.',
            'funciones' => ['Alta de usuarios y roles', 'Permisos por módulo', 'Activar/desactivar usuarios', 'Restablecer contraseñas', 'Registro de actividad por usuario'],
        ],
        'configuracion.index' => [
            'seccion' => 'Ajustes & Sistema', 'icon' => 'cog', 'titulo' => 'Configuración General',
            'descripcion' => 'Datos de la botica, parámetros del sistema, impuestos y series de comprobantes.',
            'funciones' => ['Datos de la empresa (RUC, dirección)', 'Logo y datos de comprobantes', 'Configuración de IGV', 'Series de boletas/facturas', 'Moneda y formato'],
        ],
        'auditoria.index' => [
            'seccion' => 'Ajustes & Sistema', 'icon' => 'shield', 'titulo' => 'Logs de Auditoría',
            'descripcion' => 'Registro de todas las acciones realizadas en el sistema para trazabilidad.',
            'funciones' => ['Registro de accesos', 'Cambios en productos y precios', 'Anulaciones de venta', 'Filtro por usuario y fecha', 'Exportación de logs'],
        ],
        'respaldos.index' => [
            'seccion' => 'Ajustes & Sistema', 'icon' => 'database', 'titulo' => 'Base de Datos & Respaldos',
            'descripcion' => 'Copias de seguridad de la base de datos y restauración.',
            'funciones' => ['Respaldo manual y programado', 'Descarga de copias', 'Restauración de respaldos', 'Historial de copias', 'Aviso de último respaldo'],
        ],
        'facturacion.index' => [
            'seccion' => 'Ajustes & Sistema', 'icon' => 'receipt', 'titulo' => 'Facturación Electrónica',
            'descripcion' => 'Integración con facturación electrónica (SUNAT) — módulo opcional, actualmente desactivado.',
            'funciones' => ['Emisión de comprobantes electrónicos', 'Envío a SUNAT', 'Estado de comprobantes', 'Notas de crédito/débito', 'Configuración de certificado digital'],
        ],
    ];

    public function __invoke(Request $request): View
    {
        $nombre = $request->route()->getName();
        $meta = $this->modulos[$nombre] ?? [
            'seccion' => 'Módulo', 'icon' => 'grid', 'titulo' => 'Módulo',
            'descripcion' => 'Sección del sistema.', 'funciones' => [],
        ];

        return view('modulos.placeholder', $meta);
    }
}
