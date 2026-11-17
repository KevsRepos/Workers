#!/bin/sh
# Container entrypoint: validates required configuration, then execs the command.
set -eu

if [ "${APP_ENV:-prod}" = "prod" ]; then
  for var in APP_SECRET DATABASE_URL JWT_PASSPHRASE; do
    eval "value=\${$var:-}"
    if [ -z "$value" ]; then
      echo "FATAL: required environment variable $var is not set" >&2
      exit 1
    fi
  done
  if [ ! -r config/jwt/private.pem ] || [ ! -r config/jwt/public.pem ]; then
    echo "FATAL: JWT key pair missing in config/jwt (run 'make jwt-keys' / bin/console lexik:jwt:generate-keypair)" >&2
    exit 1
  fi
fi

exec "$@"
