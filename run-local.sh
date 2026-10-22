#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/php-app"
command -v php >/dev/null 2>&1 || { echo "PHP CLI 8.2+ belum terpasang." >&2; exit 1; }
echo "ZiuLabs running at http://127.0.0.1:8080"
echo "Press Ctrl+C to stop."
exec php -S 127.0.0.1:8080 -t public

