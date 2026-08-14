#!/usr/bin/env bash
set -euo pipefail

PORT="${1:-8090}"

if ! command -v cloudflared >/dev/null 2>&1; then
  echo "cloudflared belum terpasang. Install dulu: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/"
  exit 1
fi

echo "Starting Cloudflare quick tunnel to http://127.0.0.1:${PORT}"
echo "Copy URL https://*.trycloudflare.com yang muncul, lalu set sebagai ORIGIN_URL di Worker."

cloudflared tunnel --url "http://127.0.0.1:${PORT}"
