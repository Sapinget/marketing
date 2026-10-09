#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
WORKER_DIR="$PROJECT_ROOT/worker-proxy"
TUNNEL_LOG="${TUNNEL_LOG:-$PROJECT_ROOT/storage/logs/cloudflared-marketing.log}"
STATE_FILE="${TMPDIR:-/tmp}/marketing-worker-origin-url"

while true; do
    origin_url="$(rg -o 'https://[-a-z0-9]+\.trycloudflare\.com' "$TUNNEL_LOG" 2>/dev/null | tail -n 1 || true)"

    if [[ -n "$origin_url" && "$(cat "$STATE_FILE" 2>/dev/null || true)" != "$origin_url" ]]; then
        if (cd "$WORKER_DIR" && npx wrangler deploy --var "ORIGIN_URL:$origin_url"); then
            printf '%s' "$origin_url" > "$STATE_FILE"
        fi
    fi

    sleep 10
done
