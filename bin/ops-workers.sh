#!/usr/bin/env bash
# Start local production-like workers: Meilisearch (optional) + queue worker + scheduler.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

mkdir -p storage/logs storage/app/private/backups storage/meili

MEILI_BIN="$ROOT/bin/meilisearch"
if [[ ! -x "$MEILI_BIN" ]]; then
  echo "Downloading Meilisearch binary..."
  curl -fsSL -o "$MEILI_BIN" "https://github.com/meilisearch/meilisearch/releases/download/v1.12.8/meilisearch-linux-amd64"
  chmod +x "$MEILI_BIN"
fi

if ! curl -sf http://127.0.0.1:7700/health >/dev/null 2>&1; then
  echo "Starting Meilisearch on :7700"
  nohup "$MEILI_BIN" --db-path "$ROOT/storage/meili" --http-addr 127.0.0.1:7700 --env development --no-analytics \
    > storage/logs/meilisearch.log 2>&1 &
  echo $! > storage/logs/meilisearch.pid
  sleep 1
else
  echo "Meilisearch already running"
fi

echo "Starting queue worker"
nohup php artisan queue:work database --sleep=1 --tries=3 --timeout=90 \
  > storage/logs/queue-worker.log 2>&1 &
echo $! > storage/logs/queue-worker.pid

echo "Starting scheduler"
nohup php artisan schedule:work \
  > storage/logs/scheduler.log 2>&1 &
echo $! > storage/logs/scheduler.pid

echo "Workers started. Check storage/logs/."
