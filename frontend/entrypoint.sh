#!/bin/sh
set -e
# If a mounted CA exists, make sure it's imported into the system truststore
CA_FILE=""
if [ -f /ssl/ca.pem ]; then
  CA_FILE=/ssl/ca.pem
elif [ -f /usr/local/share/ca-certificates/local-ca.crt ]; then
  CA_FILE=/usr/local/share/ca-certificates/local-ca.crt
fi

if [ -n "$CA_FILE" ]; then
  echo "Found mounted CA at $CA_FILE, updating truststore"
  cp "$CA_FILE" /usr/local/share/ca-certificates/local-ca.crt 2>/dev/null || true
  # install ca-certificates if missing (best effort)
  if command -v apt-get >/dev/null 2>&1; then
    apt-get update >/dev/null 2>&1 || true
    apt-get install -y ca-certificates >/dev/null 2>&1 || true
  elif command -v apk >/dev/null 2>&1; then
    apk add --no-cache ca-certificates >/dev/null 2>&1 || true
  fi
  update-ca-certificates || true
fi

exec "$@"
