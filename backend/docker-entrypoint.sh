#!/bin/sh
set -e

WORKDIR=/var/www/workers/backend
cd "$WORKDIR" || exit 1

# Ensure config/jwt exists (may be a host bind) and generate keys once if missing
mkdir -p config/jwt
if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
  echo "[entrypoint] JWT keypair missing — generating"
  php bin/console lexik:jwt:generate-keypair --no-interaction || {
    echo "[entrypoint] JWT generation failed" >&2
    exit 1
  }
  chown -R www-data:www-data config/jwt || true
else
  echo "[entrypoint] JWT keypair already present"
fi

# Exec the main process (php-fpm)
exec "$@"
