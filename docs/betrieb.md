# Betriebshandbuch

## Erstinstallation

1. Server mit Docker Engine ≥ 27 und Compose v2; Ports 80/443 offen, DNS-Eintrag für `DOMAIN`.
2. `cp .env.example .env` und alle Geheimnisse setzen (`openssl rand -hex 32`). `.env` nur für root lesbar (`chmod 600`).
3. Optional Firmenlogo: `deploy/branding/logo.png`.
4. Zertifikat: `docker compose --profile init run --rm certbot-init`
5. Start: `docker compose up -d --build` (legt JWT-Schlüssel an und migriert die Datenbank)
6. Erstes Admin-Konto: `docker compose run --rm php bin/console app:account:create admin@firma.de Vorname Nachname --admin`

## Updates

```bash
git pull
docker compose build --pull
docker compose up -d          # migrate läuft automatisch vor php/worker
```

Vorher ein Backup erstellen (siehe unten). Abhängigkeits-Updates kommen wöchentlich per Dependabot; Sicherheitsupdates zeitnah einspielen.

## Backups

- Der Dienst `backup` erstellt täglich um 02:00 Uhr einen komprimierten Dump inkl. Triggern und eine SHA-256-Prüfsumme in `BACKUP_DIR`; Aufbewahrung `BACKUP_RETENTION_DAYS` (Standard 30).
- Sofortiges Backup: `docker compose run --rm backup /backup.sh --once`
- **Pflicht**: Backups verschlüsselt an einen zweiten Ort kopieren (3-2-1-Regel), z. B. `restic` oder `borg` auf externen Speicher.
- **Wiederherstellung testen** (mindestens quartalsweise, Ergebnis dokumentieren):
  ```bash
  sha256sum -c workers-YYYYMMDD-HHMMSS.sql.gz.sha256
  gunzip -c workers-…sql.gz | docker compose exec -T mariadb mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" workers
  docker compose run --rm php bin/console app:audit:verify
  ```

## Audit-Log

- Prüfung der Integrität: `docker compose run --rm php bin/console app:audit:verify` (oder in der Oberfläche unter *Audit-Log → Integrität prüfen*). Empfehlung: wöchentlich per Cron, Ergebnis überwachen.
- Audit-Ereignisse werden zusätzlich als JSON auf stdout (Kanal `audit`) ausgegeben und können an ein SIEM bzw. einen WORM-Speicher weitergeleitet werden.
- Bereinigung nach Ablauf der Aufbewahrung (`AUDIT_RETENTION_DAYS`, Standard 6 Jahre): `bin/console app:audit:purge --dry-run`, dann ohne `--dry-run`. Die Bereinigung wird selbst protokolliert; die Restkette bleibt prüfbar.
- Härtung (empfohlen): Dem Anwendungs-DB-Benutzer `UPDATE`/`DELETE` auf `audit_log` entziehen.

## Jährliche Löschläufe (Januar)

```bash
docker compose run --rm php bin/console app:time-tracking:purge --dry-run
docker compose run --rm php bin/console app:time-tracking:purge
docker compose run --rm php bin/console app:audit:purge --dry-run
```

Jeder Löschlauf wird im Audit-Log protokolliert (nur Anzahl, keine Inhalte).

## Logs und Überwachung

- Alle Container loggen strukturiert (JSON) nach stdout/stderr, jeweils mit `request_id`; die ID wird an den Browser (`X-Request-Id`) und in Fehlermeldungen („Referenz“) gegeben.
- Browserfehler (JavaScript-Fehler, unbehandelte Promises, Fehler bei der Navigation) werden an `POST /client-errors` gemeldet und vom Frontend-Container als JSON mit `"source":"browser"` geloggt. Die angezeigte Referenz entspricht dem Feld `reference`. Meldungen werden vorher bereinigt (E-Mail-Adressen, Tokens, Passwörter und URL-Parameter entfernt), gekürzt und auf 20 pro Minute und Client begrenzt.
  Suche nach Referenz: `docker compose logs node | grep <referenz>` bzw. für Serverfehler `docker compose logs php node | grep <referenz>`.
- Health-Checks: Frontend `GET /health`, API `GET /api/v1/health` (prüft DB), PHP-FPM-Ping im Container.
- Empfohlen: Log-Rotation des Docker-Daemons (`max-size`), externe Verfügbarkeitsüberwachung von `https://DOMAIN/health`.

## Schlüssel und Geheimnisse rotieren

| Geheimnis | Vorgehen | Auswirkung |
|---|---|---|
| JWT-Schlüssel | Volume `jwt_keys` leeren bzw. `lexik:jwt:generate-keypair --overwrite` im `jwt-keys`-Container, dann `docker compose up -d` | aktive Access-Tokens ungültig; Sitzungen erneuern sich über Refresh-Tokens |
| Alle Sitzungen beenden | `docker compose exec mariadb mariadb -uroot -p … -e "UPDATE workers.refresh_token SET revoked_at = NOW()"` | alle Nutzer müssen sich neu anmelden |
| `APP_SECRET`, DB-Passwörter | in `.env` ändern (DB-Passwort zusätzlich per `ALTER USER`), `docker compose up -d` | – |

## Sicherheitsvorfall

1. Betroffene Konten deaktivieren (Benutzerverwaltung) – Sitzungen enden sofort.
2. Audit-Log sichern und prüfen (`app:audit:verify`, Filter nach Konto/Zeitraum, Ereignisse `auth.*`).
3. Bei Verdacht auf Schlüsselkompromittierung: Schlüssel und Geheimnisse rotieren (siehe oben).
4. Datenschutzverletzung? Meldepflicht an die Aufsichtsbehörde binnen 72 Stunden prüfen (Art. 33 DSGVO).
