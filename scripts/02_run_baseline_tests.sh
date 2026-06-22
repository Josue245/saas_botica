#!/usr/bin/env bash
# Fase 0 — Corre los tests de regresión baseline y guarda el resultado.
# Este reporte es la "foto" del comportamiento ANTES de tocar multi-tenancy.
set -euo pipefail

mkdir -p storage/baseline

echo "==> Corriendo tests de regresión (POS, Compras, Caja)..."
docker compose exec app php artisan test \
  --testsuite=Feature \
  --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest" \
  | tee storage/baseline/baseline_$(date +%Y%m%d_%H%M).txt

echo ""
echo "✅ Si todos los tests pasaron (verde), tienes tu línea base."
echo "   Guarda este archivo en git: storage/baseline/"
echo ""
echo "==> A partir de ahora, en CADA fase del roadmap corre:"
echo "    docker compose exec app php artisan test --filter=RegressionTest"
echo "    Si algo se pone rojo que antes estaba verde -> rompiste algo. Para y revisa."
