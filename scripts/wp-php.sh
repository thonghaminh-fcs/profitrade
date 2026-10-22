#!/usr/bin/env bash
# Run a PHP seeder inside the WordPress container with its JSON spec.
# Usage: scripts/wp-php.sh scripts/seed-pages.php docs/build/pages.json
set -euo pipefail
cd "$(dirname "$0")/.."
source scripts/_site.sh
SCRIPT="$1"; SPEC="${2:-}"
dc cp "$SCRIPT" wordpress:/tmp/job.php
dc cp scripts/_wp-boot.php wordpress:/tmp/_wp-boot.php
ARGS=()
if [ -n "$SPEC" ]; then dc cp "$SPEC" wordpress:/tmp/job.json; ARGS=(/tmp/job.json); fi
if [ -d .mockup/images ]; then dc cp .mockup/images wordpress:/tmp/mockup-images; fi
dc exec -T -u root wordpress php /tmp/job.php "${ARGS[@]}"
dc exec -T -u root wordpress sh -c 'chown -R www-data:www-data /var/www/html/wp-content/uploads' || true
