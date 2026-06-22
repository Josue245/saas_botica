# Mi Botica — Sistema de Gestión Farmacéutica (SaaS)

Aplicación web para la administración integral de una botica/farmacia, desarrollada con **Laravel 11 + MySQL** y diseño profesional con **Tailwind CSS** y **Chart.js**.

Esta primera entrega incluye: **autenticación (login)**, **dashboard con indicadores y gráficos**, y el **menú lateral completo** con todos los módulos del sistema (vistas base listas para desarrollar).

---

## Requisitos previos

- **PHP 8.2 o superior** con las extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`.
- **Composer** (https://getcomposer.org)
- **MySQL 5.7+ / MariaDB 10.3+** corriendo en `localhost:3306`
- (Opcional) Laragon, XAMPP o WAMP en Windows ya traen PHP + MySQL.

> No se requiere Node.js: Tailwind y Chart.js se cargan por CDN, así la app funciona sin compilar nada.

---

## Instalación (paso a paso)

Abre una terminal **dentro de la carpeta del proyecto** (`C:\SAAS\saas_botica`).

### 1. Instalar dependencias de PHP
```bash
composer install
```

### 2. Configurar el entorno
El archivo `.env` ya viene creado. Verifica/ajusta los datos de MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_botica
DB_USERNAME=root
DB_PASSWORD=        # tu contraseña de MySQL (déjalo vacío si no tienes)
```

### 3. Generar la llave de la aplicación
```bash
php artisan key:generate
```

### 4. Crear la base de datos
Opción A — desde MySQL:
```sql
CREATE DATABASE saas_botica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Opción B — un solo comando (si tu usuario tiene permisos):
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS saas_botica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Crear las tablas y cargar datos de demostración
```bash
php artisan migrate --seed
```
Esto crea todas las tablas y carga categorías, productos, proveedores, clientes y **ventas de los últimos 14 días** para que el dashboard muestre datos reales.

### 6. Iniciar el servidor
```bash
php artisan serve
```
Abre en el navegador: **http://localhost:8000**

---

## Credenciales de acceso (datos de demo)

| Rol           | Correo                        | Contraseña |
|---------------|-------------------------------|------------|
| Administrador | `admin@mibotica.test`         | `password` |
| Farmacéutico  | `farmaceutico@mibotica.test`  | `password` |
| Cajero        | `cajero@mibotica.test`        | `password` |

---

## Módulos del sistema

**Visión General**
- Dashboard (indicadores, ingresos de 7 días, top productos y categorías)

**Comercio & Ventas**
- Gestión de Caja (Apertura/Cierre, Movimientos)
- Punto de Venta (POS)
- Historial de Ventas
- Clientes

**Logística & Inventario**
- Catálogo Maestro (Productos, Categorías)
- Compras
- Proveedores
- Inventario (Stock Actual, Lotes y Vencimientos, Ajustes)

**Gerencia & Control**
- Alertas Sanitarias (stock bajo, productos por vencer y vencidos)
- Reportes PDF/Excel

**Ajustes & Sistema**
- Gestión de Personal
- Configuración General
- Logs de Auditoría
- Base de Datos & Respaldos
- Facturación Electrónica (módulo opcional)

> El Dashboard, el login y la navegación están **100% funcionales**. El resto de módulos tienen su ruta, menú y página base listos; la interfaz interna de cada uno se construye en las siguientes iteraciones.

---

## Estructura del proyecto

```
app/
  Http/Controllers/      → Auth, Dashboard, Modulo
  Http/Middleware/        → EnsureUserHasRole (control por rol)
  Models/                 → User, Producto, Categoria, Proveedor, Cliente, Venta, VentaDetalle
database/
  migrations/             → tablas del sistema
  seeders/                → datos de demostración
resources/views/
  auth/login.blade.php    → pantalla de login
  layouts/app.blade.php   → layout maestro
  partials/               → sidebar y topbar
  dashboard/              → dashboard con gráficos
  modulos/                → vista base de los módulos
  components/icon.blade.php → íconos SVG
routes/web.php            → todas las rutas
```

---

## Solución de problemas

- **`SQLSTATE[HY000] [1049] Unknown database`**: no creaste la base `saas_botica` (paso 4).
- **`Access denied for user 'root'`**: revisa `DB_USERNAME` / `DB_PASSWORD` en `.env`.
- **Pantalla sin estilos**: necesitas conexión a internet (Tailwind y Chart.js van por CDN).
- **`No application encryption key`**: ejecuta `php artisan key:generate`.
- Si cambiaste el `.env`, ejecuta `php artisan config:clear`.

---

© Mi Botica · v1.0
