#!/usr/bin/env bash
# Fase 0 — Levanta el stack Docker y prepara el entorno de testing
set -euo pipefail

echo "==> Levantando contenedores (app, nginx, mysql, mysql_testing, redis, mailpit)..."
docker compose up -d --build

echo "==> Esperando a que MySQL esté listo..."
until docker compose exec -T mysql mysqladmin ping -h localhost -uroot -proot_local_only --silent; do
  printf '.'
  sleep 2
done
echo " ✅"

echo "==> Esperando a que MySQL de testing esté listo..."
until docker compose exec -T mysql_testing mysqladmin ping -h localhost -uroot -proot_local_only --silent; do
  printf '.'
  sleep 2
done
echo " ✅"

echo "==> Instalando dependencias de Composer..."
docker compose exec app composer install

if [ ! -f .env ]; then
  echo "==> Copiando .env.example a .env..."
  cp .env.example .env
  docker compose exec app php artisan key:generate
fi

echo "==> Verificando .env.testing..."
if grep -q "GENERAR_CON_php_artisan_key_generate" .env.testing; then
  echo "==> Generando APP_KEY para .env.testing..."
  TEST_KEY=$(docker compose exec -T app php artisan key:generate --show)
  sed -i.bak "s|APP_KEY=base64:GENERAR_CON_php_artisan_key_generate|APP_KEY=${TEST_KEY}|" .env.testing
  rm -f .env.testing.bak
fi

echo "==> Corriendo migraciones en BD principal (saas_botica)..."
docker compose exec app php artisan migrate --force

echo "==> Corriendo migraciones en BD de testing (saas_botica_testing)..."
docker compose exec app php artisan migrate --env=testing --force

echo ""
echo "✅ Stack listo."
echo "   App:      http://localhost:8000"
echo "   Mailpit:  http://localhost:8025"
echo "   MySQL:    localhost:3306 (saas_botica)"
echo "   MySQL test: localhost:3307 (saas_botica_testing)"
echo ""
echo "==> Siguiente paso: ./scripts/02_run_baseline_tests.sh"
