#!/usr/bin/env bash
# Idempotent local WordPress bootstrap for this repo (shared mysql + webnet).
# Creates .env, the database, starts docker compose, installs WP + WooCommerce, activates
# the child theme. Safe to re-run. Prints a JSON evidence block on success.
# Usage: scripts/wp-env.sh ["Site Title"]
set -euo pipefail
cd "$(dirname "$0")/.."
TITLE="${1:-S.Mart}"
CHILD=ai-zippy-child

for c in mysql; do
  docker ps --format '{{.Names}}' | grep -qx "$c" || { echo "BLOCKED: shared container '$c' is not running (start it; do not create another)"; exit 3; }
done
docker network inspect webnet >/dev/null 2>&1 || { echo "BLOCKED: docker network 'webnet' missing"; exit 3; }

if [ ! -f .env ]; then
  cp .env.sample .env
  ID="$(basename "$PWD" | tr 'A-Z-' 'a-z_' | tr -cd 'a-z0-9_')"
  for _ in $(seq 1 30); do
    PORT=$(shuf -i 13000-19999 -n 1)
    ss -ltn | grep -q ":${PORT}\b" || docker ps --format '{{.Ports}}' | grep -q ":${PORT}->" || break
  done
  sed -i "s|^PROJECT_ID=.*|PROJECT_ID=${ID}|; s|^PORT=.*|PORT=${PORT}|; s|^PROJECT_HOST=.*|PROJECT_HOST=http://localhost:${PORT}|; s|^WORDPRESS_DB_NAME=.*|WORDPRESS_DB_NAME=${ID}|" .env
  printf "\n# WP ADMIN (local only)\nWP_ADMIN_USER=admin\nWP_ADMIN_PASSWORD='%s'\n" "$(openssl rand -base64 18 | tr -d '/+=' )" >> .env
fi
set -a; . ./.env; set +a
: "${PROJECT_ID:?}" "${PORT:?}"

docker exec mysql mysql -uroot -p123456 -e "CREATE DATABASE IF NOT EXISTS \`${WORDPRESS_DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

mkdir -p src/wp-content/uploads src/wp-content/plugins
docker compose up -d --build
for _ in $(seq 1 40); do
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${PORT}/" || true)
  [ "$code" != "000" ] && break; sleep 3
done
# bind-mounted dirs are host-owned; the container runs as www-data
docker compose exec -T -u root wordpress sh -c \
  'chown -R www-data:www-data /var/www/html/wp-content/uploads && chmod -R 777 /var/www/html/wp-content/uploads /var/www/html/wp-content/plugins'

WP=(docker exec -u root "${PROJECT_ID}" wp --allow-root)
if ! "${WP[@]}" core is-installed 2>/dev/null; then
  "${WP[@]}" core install --url="http://localhost:${PORT}" --title="$TITLE" --admin_user="${WP_ADMIN_USER:-admin}" \
    --admin_password="${WP_ADMIN_PASSWORD}" --admin_email="${DEV_EMAIL:-dev@zippy.sg}" --skip-email
fi
"${WP[@]}" option update siteurl "http://localhost:${PORT}" >/dev/null
"${WP[@]}" option update home "http://localhost:${PORT}" >/dev/null
"${WP[@]}" plugin is-installed woocommerce || "${WP[@]}" plugin install woocommerce
"${WP[@]}" plugin is-active woocommerce || "${WP[@]}" plugin activate woocommerce
"${WP[@]}" theme activate "$CHILD"
"${WP[@]}" rewrite structure '/%postname%/' --hard
"${WP[@]}" option update woocommerce_cod_settings '{"enabled":"yes"}' --format=json >/dev/null
chmod -R a+rwX src/wp-content/plugins 2>/dev/null || true

echo "---- evidence ----"
printf '{"url":"http://localhost:%s","container":"%s","db":"%s","http":"%s","theme":"%s","woocommerce":"%s"}\n' \
  "$PORT" "$PROJECT_ID" "$WORDPRESS_DB_NAME" "$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${PORT}/")" \
  "$("${WP[@]}" theme list --status=active --field=name | tr '\n' ',')" "$("${WP[@]}" plugin get woocommerce --field=status)"
