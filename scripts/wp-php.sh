#!/usr/bin/env bash
# Run a PHP seeder inside the WordPress container with its JSON spec.
# Usage: scripts/wp-php.sh scripts/seed-pages.php docs/build/pages.json
# The repo root is not mounted in the container, so the script, the spec and .mockup/images are copied to /tmp first.
set -euo pipefail
cd "$(dirname "$0")/.."
SCRIPT="$1"; SPEC="${2:-}"
docker compose cp "$SCRIPT" wordpress:/tmp/job.php
ARGS=()
if [ -n "$SPEC" ]; then docker compose cp "$SPEC" wordpress:/tmp/job.json; ARGS=(/tmp/job.json); fi
if [ -d .mockup/images ]; then docker compose cp .mockup/images wordpress:/tmp/mockup-images; fi
docker compose exec -T -u root wordpress php /tmp/job.php "${ARGS[@]}"
docker compose exec -T -u root wordpress sh -c 'chown -R www-data:www-data /var/www/html/wp-content/uploads' || true
