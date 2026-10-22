# Source me: resolves the site dir (outside every git worktree) and exports the site env.
#   source scripts/_site.sh
# The WordPress docker site lives in ~/.wpa-sites/<job> because each WPA task runs in its own git worktree.
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
JOB="$(sed -n 's/^job: *//p' "$ROOT/zippy.yaml" | tr -d '"' | head -1)"
SITE="${WPA_SITE:-$HOME/.wpa-sites/${JOB:-site}}"
if [ -f "$SITE/.env" ]; then set -a; . "$SITE/.env"; set +a; SITE_URL="http://localhost:${PORT:-}"; fi
export ROOT JOB SITE SITE_URL
dc() { docker compose --project-directory "$SITE" -f "$SITE/docker-compose.yml" "$@"; }
wpcli() { docker exec -u root "${PROJECT_ID}" wp --allow-root "$@"; }
