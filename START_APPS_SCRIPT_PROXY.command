#!/bin/bash

set -euo pipefail

PROJECT_DIR="/Users/serverbot/Library/CloudStorage/GoogleDrive-official@purapuraponsel.com/Shared drives/PURA PURA PONSEL/DATA/DASHBOARD/MARKETING"
HOST="127.0.0.1"
PORT="8000"
NGROK_HOSTNAME="deranged-manhood-gully.ngrok-free.dev"
LARAVEL_URL="http://${HOST}:${PORT}"
PUBLIC_URL="https://${NGROK_HOSTNAME}"
LOG_FILE="/tmp/marketing-dashboard-laravel.log"

cd "$PROJECT_DIR"

echo "== Marketing Dashboard Proxy Starter =="
echo "Project : $PROJECT_DIR"
echo "Laravel : $LARAVEL_URL"
echo "Public  : $PUBLIC_URL"
echo

if curl -fsS -o /dev/null "$LARAVEL_URL"; then
  echo "Laravel sudah aktif di $LARAVEL_URL"
else
  echo "Menjalankan Laravel di $LARAVEL_URL ..."
  php artisan serve --host="$HOST" --port="$PORT" >"$LOG_FILE" 2>&1 &
  LARAVEL_PID=$!
  sleep 2

  if ! curl -fsS -o /dev/null "$LARAVEL_URL"; then
    echo "Laravel gagal start. Cek log:"
    echo "  $LOG_FILE"
    if kill -0 "$LARAVEL_PID" 2>/dev/null; then
      kill "$LARAVEL_PID" 2>/dev/null || true
    fi
    exit 1
  fi

  echo "Laravel aktif. PID: $LARAVEL_PID"
  echo "Log: $LOG_FILE"
fi

echo
echo "Menjalankan ngrok ke $PUBLIC_URL ..."
echo "Biarkan window ini tetap terbuka selama Apps Script dipakai."
echo

exec ngrok http --url="$NGROK_HOSTNAME" "$PORT"
