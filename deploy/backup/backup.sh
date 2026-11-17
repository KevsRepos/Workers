#!/bin/sh
# Nightly logical backup of the database with retention.
# Copy /backups to off-site, encrypted storage (3-2-1 rule) – see docs/betrieb.md.
set -eu

: "${BACKUP_RETENTION_DAYS:=30}"
: "${BACKUP_HOUR:=2}"

run_backup() {
  file="/backups/workers-$(date +%Y%m%d-%H%M%S).sql.gz"
  echo "$(date -Iseconds) creating $file"
  mariadb-dump --host=mariadb --user=root --password="$MARIADB_ROOT_PASSWORD" \
    --single-transaction --routines --triggers --events --hex-blob "$MARIADB_DATABASE" | gzip -9 > "$file.tmp"
  mv "$file.tmp" "$file"
  sha256sum "$file" > "$file.sha256"
  find /backups -name 'workers-*.sql.gz*' -mtime +"$BACKUP_RETENTION_DAYS" -delete
}

[ "${1:-}" = "--once" ] && { run_backup; exit 0; }

while :; do
  if [ "$(date +%H)" -eq "$BACKUP_HOUR" ]; then
    run_backup
    sleep 3600
  fi
  sleep 300
done
