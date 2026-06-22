# Fase 0 — Windows / PowerShell

Tu proyecto está en `C:\saas_botica`, usando PowerShell. Esta versión
reemplaza los scripts `.sh` por equivalentes `.ps1` — no necesitas `chmod`,
Git Bash, ni WSL. Todo corre nativo en PowerShell.

## Diferencia clave vs. la guía anterior

| Antes (Linux/Mac) | Ahora (Windows) |
|---|---|
| `chmod +x scripts/*.sh` | **No se necesita** — PowerShell no requiere permiso de ejecución de archivo, solo política de ejecución de scripts (ver abajo) |
| `./scripts/00_setup_branch.sh` | `.\scripts\00_setup_branch.ps1` |
| `./scripts/01_docker_up.sh` | `.\scripts\01_docker_up.ps1` |
| `./scripts/02_run_baseline_tests.sh` | `.\scripts\02_run_baseline_tests.ps1` |

## Paso 0: habilitar ejecución de scripts en PowerShell (una sola vez)

Por defecto, PowerShell bloquea ejecutar archivos `.ps1` descargados. Corre esto
**una sola vez**, en una PowerShell como Administrador:

```powershell
Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
```

Esto permite correr scripts locales sin firmar, manteniendo la protección
para scripts descargados de internet sin revisar.

## ⚠️ Posible conflicto de puerto 3306 (MySQL)

Si tienes **algún otro MySQL/MariaDB corriendo nativo en Windows** (un servicio
instalado aparte, otro XAMPP, WAMP, Laragon, etc.), el contenedor `mysql` de
Docker no podrá arrancar porque el puerto 3306 ya estaría ocupado.

El script `01_docker_up.ps1` detecta automáticamente si el puerto 3306 está
en uso y te avisa antes de continuar, con la opción de seguir igual o cancelar.
Si no tienes ningún MySQL corriendo fuera de Docker, puedes ignorar esta
sección — el script simplemente no mostrará ninguna advertencia.

Si necesitas verificarlo manualmente:

```powershell
Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
```

Si aparece un resultado, identifica qué servicio es (`Get-Process -Id <PID_que_te_aparezca>`)
y detenlo, o cambia el puerto del contenedor en `docker-compose.yml`
(`'3310:3306'` en vez de `'3306:3306'`).

## Pasos de ejecución (en orden)

### 1. Copiar archivos a tu proyecto

Desde el ZIP descargado, copia estas carpetas/archivos a `C:\saas_botica\`:

```
docker-compose.yml          → C:\saas_botica\
docker\                     → C:\saas_botica\docker\
.env.testing                → C:\saas_botica\
phpunit.xml                 → C:\saas_botica\ (mergear si ya existe uno)
scripts\*.ps1               → C:\saas_botica\scripts\
tests\TestCase.php          → C:\saas_botica\tests\
tests\Feature\*.php         → C:\saas_botica\tests\Feature\
database\factories\*.php    → C:\saas_botica\database\factories\
```

Puedes hacerlo arrastrando carpetas en el Explorador de Windows, o desde
PowerShell (ajusta `$origen` a donde descomprimiste el zip):

```powershell
$origen = "$HOME\Downloads\fase0_windows\fase0_win"
$destino = "C:\saas_botica"

Copy-Item "$origen\docker-compose.yml" $destino
Copy-Item "$origen\docker" $destino -Recurse -Force
Copy-Item "$origen\.env.testing" $destino
Copy-Item "$origen\phpunit.xml" $destino
Copy-Item "$origen\scripts" $destino -Recurse -Force
Copy-Item "$origen\tests\TestCase.php" "$destino\tests\"
Copy-Item "$origen\tests\Feature\*.php" "$destino\tests\Feature\" -Force
Copy-Item "$origen\database\factories\*.php" "$destino\database\factories\" -Force
```

### 2. Ir a la carpeta del proyecto

```powershell
cd C:\saas_botica
```

### 3. Setup de rama Git (snapshot + rama de trabajo)

```powershell
.\scripts\00_setup_branch.ps1
```

### 4. Apagar MySQL de XAMPP, luego levantar Docker

Abre el XAMPP Control Panel → Stop en MySQL. Luego:

```powershell
.\scripts\01_docker_up.ps1
```

### 5. Correr la suite de tests baseline

```powershell
.\scripts\02_run_baseline_tests.ps1
```

## Si algo falla

**"docker: command not found" o similar** → Docker Desktop no está corriendo.
Ábrelo desde el menú de Windows y espera a que el ícono de la barra de tareas
deje de animarse antes de reintentar.

**"port is already allocated" al hacer `docker compose up`** → algo sigue
usando el puerto 3306. Verifica con:

```powershell
Get-NetTCPConnection -LocalPort 3306 -State Listen
```

Si aparece un proceso, es probablemente `mysqld.exe` de XAMPP. Apágalo desde
el Panel de Control de XAMPP.

**El contenedor `app` no encuentra `vendor/` o `.env`** → es normal en el primer
arranque; el script `01_docker_up.ps1` ya corre `composer install` y crea `.env`
automáticamente. Si falló a la mitad, corre el script de nuevo (es seguro
volver a ejecutarlo).

**Los tests fallan con error de conexión a `mysql_testing`** → espera unos
segundos más, a veces el contenedor tarda en aceptar conexiones la primera
vez. Reintenta: `.\scripts\02_run_baseline_tests.ps1`

## Comandos útiles de referencia (PowerShell)

```powershell
# Ver logs de un contenedor
docker compose logs -f app

# Entrar al contenedor de la app
docker compose exec app sh

# Correr un solo archivo de test
docker compose exec app php artisan test tests/Feature/PosRegressionTest.php

# Correr un solo test por nombre
docker compose exec app php artisan test --filter=registra_una_venta_simple

# Resetear la BD de testing manualmente
docker compose exec app php artisan migrate:fresh --env=testing

# Apagar todo
docker compose down

# Apagar y borrar volumenes (¡borra la BD real persistente del contenedor!)
docker compose down -v
```

## Siguiente paso

Con la Fase 0 completa y verde, el siguiente paso del roadmap es la
**Fase 1: Migraciones de tablas nuevas** (`tenants`, `planes`, `sucursales`,
`suscripciones`, `correlativos`, `stock_sucursales`). Avísame cuando tengas
esto corriendo y seguimos con eso.
