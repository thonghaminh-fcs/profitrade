#!/usr/bin/env bash
# Build the child theme in THIS worktree and copy it into the live site dir.
# Usage: scripts/wp-sync.sh [--parent]   (--parent also builds + copies the parent theme; needed once by wp-env.sh)
set -euo pipefail
cd "$(dirname "$0")/.."
source scripts/_site.sh
[ -d "$SITE/src/wp-content" ] || { echo "BLOCKED: site dir $SITE missing; run scripts/wp-env.sh first"; exit 3; }

# Worktrees have no node_modules: share one install per package.json (cached outside the repo).
if [ ! -e node_modules ]; then
  CACHE="$HOME/.cache/wpa-node/$(sha1sum package.json | cut -c1-12)"
  if [ ! -d "$CACHE/node_modules" ]; then
    mkdir -p "$CACHE" && cp package.json "$CACHE/" && (cd "$CACHE" && npm install --no-audit --no-fund)
  fi
  ln -s "$CACHE/node_modules" node_modules
fi

THEMES=src/wp-content/themes
if [ "${1:-}" = "--parent" ]; then
  npm run build:parent
  rsync -a --delete --exclude node_modules "$THEMES/ai-zippy/" "$SITE/$THEMES/ai-zippy/"
fi
npm run build:child
rsync -a --delete --exclude node_modules "$THEMES/ai-zippy-child/" "$SITE/$THEMES/ai-zippy-child/"
echo "synced child theme -> $SITE"
