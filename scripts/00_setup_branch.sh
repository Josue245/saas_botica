#!/usr/bin/env bash
# Fase 0 — Setup de rama y snapshot de seguridad
# Ejecutar desde la raíz del proyecto Laravel (saas_botica)

set -euo pipefail

echo "==> Verificando estado del repositorio..."
if [ -n "$(git status --porcelain)" ]; then
  echo "❌ Tienes cambios sin commitear. Haz commit o stash antes de continuar."
  git status --short
  exit 1
fi

echo "==> Rama actual: $(git branch --show-current)"

# 1. Asegurar que main/master está actualizado
BASE_BRANCH=$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@' || echo "main")
echo "==> Rama base detectada: ${BASE_BRANCH}"
git checkout "${BASE_BRANCH}"
git pull origin "${BASE_BRANCH}"

# 2. Tag de snapshot pre-migración (permite rollback instantáneo)
SNAPSHOT_TAG="pre-multitenant-$(date +%Y%m%d-%H%M)"
git tag -a "${SNAPSHOT_TAG}" -m "Snapshot antes de iniciar migración multi-tenant"
git push origin "${SNAPSHOT_TAG}"
echo "✅ Tag de snapshot creado: ${SNAPSHOT_TAG}"
echo "   Rollback de emergencia: git checkout ${SNAPSHOT_TAG}"

# 3. Crear rama de trabajo
BRANCH_NAME="feature/multitenant"
if git show-ref --verify --quiet "refs/heads/${BRANCH_NAME}"; then
  echo "⚠️  La rama ${BRANCH_NAME} ya existe localmente. Cambiando a ella."
  git checkout "${BRANCH_NAME}"
else
  git checkout -b "${BRANCH_NAME}"
  git push -u origin "${BRANCH_NAME}"
  echo "✅ Rama creada: ${BRANCH_NAME}"
fi

# 4. Crear sub-ramas por fase (no se usan todas de inmediato, pero quedan listas)
echo "==> Convención de commits para esta migración:"
cat <<'EOF'

  feat(tenant): ...     -> nueva funcionalidad multi-tenant
  fix(tenant): ...      -> corrección de bug relacionado a tenant
  test(tenant): ...      -> tests nuevos o actualizados
  db(tenant): ...        -> migraciones de base de datos
  refactor(tenant): ...   -> refactor sin cambio de comportamiento

  Ejemplo:
    git commit -m "db(tenant): crear tabla tenants y planes"
    git commit -m "test(tenant): tests de regresión POS antes de multitenancy"

EOF

echo "==> Listo. Estás en la rama ${BRANCH_NAME}, basada en ${SNAPSHOT_TAG}."
echo "==> Siguiente paso: correr ./scripts/01_docker_up.sh"
