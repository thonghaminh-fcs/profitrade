#!/usr/bin/env bash
# Idempotent: create/refresh the live WordPress site in ~/.wpa-sites/<job> (shared mysql + webnet),
# install WP + WooCommerce, activate the child theme. Prints a JSON evidence block.
# Usage: scripts/wp-env.sh ["Site Title"]
set -euo pipefail
cd "$(dirname "$0")/.."
source scripts/_site.sh
TITLE="${1:-S.Mart}"

docker ps --format '{{.Names}}' | grep -qx mysql || { echo "BLOCKED: shared container 'mysql' is not running (do not create another)"; exit 3; }
docker network inspect webnet >/dev/null 2>&1 || { echo "BLOCKED: docker network 'webnet' missing"; exit 3; }

FRESH=0
if [ ! -d "$SITE/src/wp-content/plugins" ]; then
  FRESH=1
  mkdir -p "$SITE/src/wp-content/uploads"
  cp docker-compose.yml Dockerfile.dev "$SITE/"; [ -f Dockerfile ] && cp Dockerfile "$SITE/"
  mkdir -p "$SITE/src"; cp src/.htaccess "$SITE/src/" 2>/dev/null || true
  rsync -a --exclude node_modules src/wp-content/plugins "$SITE/src/wp-content/"
  rsync -a --exclude node_modules src/wp-content/themes/index.php "$SITE/src/wp-content/themes/"
  mkdir -p "$SITE/src/wp-content/themes"
fi
if [ ! -f "$SITE/.env" ]; then
  cp .env.sample "$SITE/.env"
  ID="${JOB:-site}"; ID="$(echo "$ID" | tr 'A-Z-' 'a-z_' | tr -cd 'a-z0-9_')"
  for _ in $(seq 1 30); do
    PORT=$(shuf -i 13000-19999 -n 1)
    ss -ltn | grep -q ":${PORT}\b" || docker ps --format '{{.Ports}}' | grep -q ":${PORT}->" || break
  done
  sed -i "s|^PROJECT_ID=.*|PROJECT_ID=${ID}|; s|^PORT=.*|PORT=${PORT}|; s|^PROJECT_HOST=.*|PROJECT_HOST=http://localhost:${PORT}|; s|^WORDPRESS_DB_NAME=.*|WORDPRESS_DB_NAME=${ID}|" "$SITE/.env"
  printf "\n# WP ADMIN (local only)\nWP_ADMIN_USER=admin\nWP_ADMIN_PASSWORD='%s'\n" "$(openssl rand -base64 18 | tr -d '/+=')" >> "$SITE/.env"
fi
source scripts/_site.sh   # re-read the env now that it exists
: "${PROJECT_ID:?}" "${PORT:?}"

docker exec mysql mysql -uroot -p123456 -e "CREATE DATABASE IF NOT EXISTS \`${WORDPRESS_DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

bash scripts/wp-sync.sh --parent          # builds themes in this worktree and copies them into the site dir
mkdir -p "$SITE/src/wp-content/uploads"
dc up -d --build
for _ in $(seq 1 40); do
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${PORT}/" || true)
  [ "$code" != "000" ] && break; sleep 3
done
dc exec -T -u root wordpress sh -c 'chown -R www-data:www-data /var/www/html/wp-content/uploads && chmod -R 777 /var/www/html/wp-content/uploads'

if ! wpcli core is-installed 2>/dev/null; then
  wpcli core install --url="http://localhost:${PORT}" --title="$TITLE" --admin_user="${WP_ADMIN_USER:-admin}" \
    --admin_password="${WP_ADMIN_PASSWORD}" --admin_email="${DEV_EMAIL:-dev@zippy.sg}" --skip-email
else
  wpcli user update "${WP_ADMIN_USER:-admin}" --user_pass="${WP_ADMIN_PASSWORD}" >/dev/null
fi
wpcli option update siteurl "http://localhost:${PORT}" >/dev/null
wpcli option update home "http://localhost:${PORT}" >/dev/null
wpcli plugin is-installed woocommerce || wpcli plugin install woocommerce
wpcli plugin is-active woocommerce || wpcli plugin activate woocommerce
wpcli theme activate ai-zippy-child
wpcli rewrite structure '/%postname%/' --hard
wpcli option update woocommerce_cod_settings '{"enabled":"yes"}' --format=json >/dev/null

echo "---- evidence ----"
printf '{"url":"http://localhost:%s","container":"%s","db":"%s","site_dir":"%s","http":"%s","theme":"%s","woocommerce":"%s"}\n' \
  "$PORT" "$PROJECT_ID" "$WORDPRESS_DB_NAME" "$SITE" "$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${PORT}/")" \
  "$(wpcli theme list --status=active --field=name | tr '\n' ',')" "$(wpcli plugin get woocommerce --field=status)"
