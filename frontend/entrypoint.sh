#!/bin/sh
set -e
# If a mounted CA exists, make sure it's imported into the system truststore
if [ -f /usr/local/share/ca-certificates/local-ca.crt ]; then
  echo "Found mounted CA at /usr/local/share/ca-certificates/local-ca.crt, updating truststore"
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
