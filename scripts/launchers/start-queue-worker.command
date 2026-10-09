#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../.."
exec php artisan queue:work --sleep=3 --tries=3 --timeout=90
