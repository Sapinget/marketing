#!/bin/bash

set -euo pipefail

PORT="${1:-${PORT:-8000}}"
HOST="127.0.0.1"
TARGET="http://${HOST}:${PORT}"

cd "$(dirname "$0")/../.."

if ! command -v ngrok >/dev/null 2>&1; then
    echo "ngrok tidak ditemukan di PATH."
    echo "Install / login ngrok dulu, lalu jalankan ulang script ini."
    exit 1
fi

if ! curl -fsS -o /dev/null "${TARGET}"; then
    echo "Server lokal ${TARGET} belum merespons."
    echo "Jalankan dashboard dulu, lalu ulangi script ini."
    exit 1
fi

echo "Menjalankan ngrok untuk ${TARGET}"
echo "Inspector: http://127.0.0.1:4040"
echo "Tekan Ctrl+C untuk stop."
echo

exec ngrok http "${PORT}"
