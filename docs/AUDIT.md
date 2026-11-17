# Workers – Code-, Sicherheits- und Architektur-Audit

Stand: 2026-11-17 · Basis: Branch `ccr-da845230-3m8mcc` (Commit `d5b837d`) · Methode: manuelles Review aller Backend-Module, Auth-Pfad, Frontend-BFF, Infrastruktur; `composer audit`, `npm audit`. Kein Laufzeit-/Pentest.

## 1. Zweck und Ist-Architektur

Interne Web-App (PWA) für einen Betrieb mit Verkauf/Verleih (Getränke, Pfand, Zelte/Events):
Produkte & Pfand (PIM), Produktverbünde, Kunden & Adressen, Lieferscheine inkl. Rückgabescheine und Auftragsvorlagen, Arbeitszeiterfassung, Zeltplaner. Geplant laut README: Rechnungen.

```
Browser ──HTTPS──> nginx ─┬─ /workers/* ──FastCGI──> PHP-FPM (Symfony 7.4, Doctrine ORM 2.20) ──> MariaDB 12
                          └─ /*        ──HTTP────> Node (SvelteKit 2 / Svelte 5, adapter-node)
                                                     └─ /api/workers (generischer Proxy) ──> https://DOMAIN/workers/*  (Hairpin über nginx)
Auth: JWT (RS256, 15 min) + Refresh-Token (7 Tage), beide als HttpOnly-Cookies.
Deployment: docker-compose, certbot standalone + renew-Loop.
```

Backend-Struktur `src/Modules/<Domäne>/{Controller,Service,Repository,Factory,Entity,Dto}` – grundsätzlich sauber geschnitten, aber anämische Entities mit `public`-Properties, Fehlerbehandlung über `\Error|Success`-Rückgabewerte, keine Tests, keine statische Analyse, keine CI.

## 2. Befunde

Schweregrad: **K** kritisch · **H** hoch · **M** mittel · **N** niedrig

### 2.1 Sicherheit

| # | S | Befund | Ort |
|---|---|---|---|
| S1 | K | **IDOR Zeiterfassung**: Einträge werden nur per ID geladen; jeder angemeldete Nutzer kann Zeiteinträge anderer Mitarbeiter anlegen, ändern, löschen. Zusätzlich Hard-Delete ohne Historie (Beweiswert der Zeiterfassung = 0). | `TimeTracking/Service.php` `createEntry/updateEntry/deleteEntry`, `Repository::findEntryById` |
| S2 | K | **Refresh-Token-Ablauf wird nie geprüft** (`expires` wird gespeichert, aber nicht ausgewertet) → gestohlener Token ist unbegrenzt gültig, solange er rotiert wird. Tokens im Klartext gespeichert, Unique-Index ging in Migration `Version20261114165559` verloren, keine Reuse-Detection, kein Widerruf bei Passwortwechsel. | `Account/RefreshToken/Service.php`, Migration |
| S3 | H | **Keine Autorisierung/Rollen**: `getRoles()` liefert `[]`; jeder Account darf alles (Preise, Produkte, Lieferscheine, fremde Daten). | `Account/Account.php`, `security.yaml` |
| S4 | H | **Kein Brute-Force-Schutz** am Login (kein `login_throttling`, kein nginx `limit_req`). | `security.yaml`, `nginx.conf` |
| S5 | H | **35 bekannte Advisories in Symfony 7.4.7** (u. a. Firewall-Bypass, HEAD-Bypass bei `#[IsGranted]`, MonologBridge-Deserialisierung, ReDoS). Fix: Patch-Update `symfony/*` ≥ 7.4.12. `doctrine/cache` abandoned. | `composer.lock` |
| S6 | H | **Interne Fehlermeldungen an Client** (`new Error($e->getMessage(), 500)` → DB-/SQL-Details). Bei `APP_ENV=dev` (Default in `.env.example`) zusätzlich Stacktraces. | alle `Service.php` |
| S7 | H | **Generischer BFF-Proxy** `/api/workers` reicht beliebige `endpoint`/`method` durch (kein Allowlisting, Path-Traversal innerhalb Origin möglich), verschluckt Statuscodes (immer 200), leitet Backend-Fehlertexte an den Browser. Server-seitige Requests laufen über die öffentliche Domain (Hairpin), `BACKEND_INTERNAL_URL` ist gesetzt, aber ungenutzt. | `frontend/src/routes/api/workers/+server.ts`, `lib/api/server/requestWorkers.ts` |
| S8 | M | **Keine Security-Header** (HSTS, CSP, X-Content-Type-Options, frame-ancestors, Referrer-Policy), `server_tokens` an, kein HTTP/2. | `nginx.conf`, `svelte.config.js` |
| S9 | M | `/api/doc` öffentlich: listet alle Routen + DTO-Schemata. `/api/uuid` öffentlich, ohne Zweck. | `ApiDoc/Controller.php`, `security.yaml` |
| S10 | M | CORS `allow_origin: ['*']` für alle Pfade – unnötig, da same-origin über nginx. | `nelmio_cors.yaml` |
| S11 | M | Logout per `GET` (CSRF-fähig) und hinter JWT-Firewall → bei abgelaufenem Access-Token wird der Refresh-Token serverseitig nicht gelöscht. | `RefreshToken/Controller.php` |
| S12 | M | Entities werden direkt serialisiert (`$this->json($deliveryNote)`) → Over-Exposure des gesamten Objektgraphen, Risiko zirkulärer Referenzen. | `DeliveryNote/Controller.php` u. a. |
| S13 | M | Container: PHP-Prod-Image enthält `build-essential`, `git`, Composer; Node-Image installiert zur Laufzeit Pakete via `apt-get` (Entrypoint), läuft als root, liefert devDependencies aus; `certbot:latest`/`nginx:stable` ungepinnt; Frontend ohne `.dockerignore` (lokale `.env` landet im Build-Kontext). | `*/Dockerfile`, `frontend/entrypoint.sh`, `docker-compose.yml` |
| S14 | N | `npm audit`: 14 Vulns (13 high), ausschließlich Build-Tooling – via `npm audit fix` behebbar. | `frontend/package-lock.json` |
| S15 | N | Passwortrichtlinie 8–50 Zeichen; Maximalbegrenzung 50 unnötig (NIST 800-63B: ≥ 64 zulassen). Kein Account-Anlage-Weg (Register auskommentiert) → Accounts vermutlich direkt in DB. | `ChangePasswordRequestDto.php` |

### 2.2 Funktionale Fehler

| # | S | Befund |
|---|---|---|
| F1 | H | `DELETE /delivery-notes/{id}` antwortet „deleted“, löscht aber nichts (Stub). |
| F2 | H | `new JsonResponse($error)` mit `\Error`-Objekt serialisiert zu `{}` → Frontend erhält leere Fehlermeldungen (Customer, Product, DeliveryNote, Address Controller). |
| F3 | M | `GET /delivery-notes/{status}` mit ungültigem Status → `ValueError` → 500. Keine Pagination bei Lieferscheinen. |
| F4 | M | `GET /customers` liefert hart `[]` (Platzhalter). |
| F5 | M | Zeiterfassungs-Reminder: Handler leer, Cron `* * * * *`, und es läuft kein `messenger:consume`-Worker → Scheduler wird nie ausgeführt. |
| F6 | M | Mehrstufige Schreibvorgänge ohne Transaktion (`save` Lieferschein: zwei `flush` – Teilzustand bei Fehler). |
| F7 | M | `PUBLIC_BACKEND_URL` ist `$env/static/public` (Build-Zeit), wird im Docker-Build nicht gesetzt → Build nicht reproduzierbar. `npm install --frozen-lockfile` ist kein npm-Flag (→ `npm ci`). |
| F8 | N | Zeitzone nicht konfiguriert (Container = UTC) → Monatswechsel/„heute“ in der Zeiterfassung um 1–2 h verschoben. Urlaub/Krank pauschal 8 h hart codiert. |
| F9 | N | Totes/inkonsistentes Material: `backend/docker-compose*.yml` (Postgres-Skeleton), `.ddev`, unbenutzte Dependency `lombok-php`, `adapter-auto`, `users_in_memory`-Provider, `main`-Firewall, auskommentierter Code, `console.log` im Frontend, Debug-Output im Dockerfile. |

### 2.3 Wartbarkeit / Qualität

- **0 Tests** (Backend & Frontend), keine CI, kein PHPStan/Psalm, kein PHP-CS-Fixer, kein ESLint/Prettier, kein Dependabot/Renovate.
- Uneinheitliche REST-Konventionen (`/delivery-note/{id}` vs. `/delivery-notes/{id}`, `POST /customer-address/{customerId}`, Status als Pfadsegment), uneinheitliche Fehlerformate (String, `{error}`, `{}`), keine API-Versionierung, kein OpenAPI.
- Anämisches Domänenmodell: Statusübergänge (z. B. `RETURNED`) per freier Zuweisung, keine Invarianten; `UpdateDeliveryNoteRequestDto` erlaubt beliebigen Status.
- `JWTExtractor` decodiert den Token erneut, statt `Security::getUser()` zu nutzen.
- 24 auto-generierte Migrationen ohne Beschreibung; Migrations-Dateinamen mit Zukunftsdatum (Version2026…) neben 2025 – Reihenfolge prüfen.
- Kein strukturiertes Logging/Correlation-IDs, kein Monitoring, keine Health-Endpoints, keine Backups der DB.

## 3. Compliance-Relevanz (DE/EU)

| Rahmen | Relevanz | Lücken |
|---|---|---|
| **DSGVO** | Kunden- und Mitarbeiterdaten (Zeiterfassung = Beschäftigtendaten) | Kein Verzeichnis von Verarbeitungstätigkeiten/TOMs im Repo, kein Lösch-/Aufbewahrungskonzept, keine Zugriffsprotokollierung, kein RBAC (Art. 25/32), Kunden-PII in `localStorage`-Drafts auf Geräten. |
| **Arbeitszeiterfassung** (BAG 13.09.2022, § 16 ArbZG) | Pflicht zur objektiven, verlässlichen Erfassung | S1: fremde Manipulation möglich, Hard-Delete, keine Änderungshistorie. |
| **GoBD / HGB / AO** | Lieferscheine sind Handels-/Geschäftsbriefe, Rechnungen geplant | Keine Unveränderbarkeit/Versionierung, keine fortlaufende Nummerierung, kein Audit-Trail, keine Verfahrensdokumentation, keine revisionssichere Archivierung (6/8/10 Jahre). Für Rechnungen zusätzlich E-Rechnungspflicht B2B (XRechnung/ZUGFeRD) seit 2025 (Empfang) bzw. 2027/2028 (Versand). |
| **Security-Baseline** (OWASP ASVS L2, BSI IT-Grundschutz APP.3.1/APP.3.2) | Referenz für „auditierbar“ | Siehe 2.1; fehlende Nachweise (Tests, SBOM, Scans, Change-Log). |

## 4. Zielbild

- **Backend**: Symfony 7.4 LTS (aktueller Patch), PHP 8.4, Doctrine ORM 3, `declare(strict_types=1)` überall, PHPStan Level max, Rector, PHP-CS-Fixer.
- **API**: einheitliche REST-Ressourcen unter `/api/v1`, Fehler als RFC 9457 (`application/problem+json`), Response-DTOs statt Entity-Serialisierung, OpenAPI 3.1 (NelmioApiDoc, nur authentifiziert), Pagination überall.
- **AuthN/AuthZ**: Rollen (z. B. `ROLE_EMPLOYEE`, `ROLE_DISPATCH`, `ROLE_ADMIN`) + Symfony-Voter für Objektzugriffe; Refresh-Token gehasht (SHA-256), Ablauf + Rotation + Reuse-Detection (Family-Revoke), Widerruf bei Passwortwechsel; `login_throttling`; Account-Verwaltung per Admin-UI/CLI; optional TOTP-2FA für Admins.
- **Frontend**: SvelteKit als echter BFF – typisierte, server-seitige Load-Functions/Form-Actions je Use-Case, Backend-Aufruf intern (`BACKEND_INTERNAL_URL`, kein Hairpin), `$env/dynamic`, CSP via `kit.csp`, Status-/Fehler-Weitergabe, Zod-Validierung.
- **Domäne**: Statusmaschine für Lieferscheine (Symfony Workflow), Soft-Delete/Storno statt Löschen, fortlaufende Belegnummern, Transaktionen je Use-Case.
- **Audit-Trail**: append-only Audit-Log (wer/was/wann/vorher/nachher) für alle schreibenden Operationen auf Beschäftigten- und Belegdaten (Doctrine-Listener → eigene Tabelle, optional Hash-Kette für Manipulationsnachweis).
- **Betrieb**: gehärtete Multi-Stage-Images (non-root, minimal, Digest-gepinnt), nginx mit Security-Headern/Rate-Limits/HTTP2, Messenger-Worker-Service, Health-Endpoints, strukturierte JSON-Logs, automatisierte verschlüsselte DB-Backups mit Restore-Test.
- **Lieferkette/Nachweise**: GitHub Actions (Lint, PHPStan, PHPUnit, Vitest, Playwright, `composer audit`, `npm audit`, Trivy, Gitleaks, SBOM via Syft/CycloneDX), Renovate, Branch-Protection, Conventional Commits + CHANGELOG, `SECURITY.md`.

## 5. Roadmap

**Phase 0 – Sofortmaßnahmen (1–2 Tage)**
1. `composer update "symfony/*"` (Patch), `npm audit fix`.
2. S1 Ownership-Check in Zeiterfassung; S2 Ablaufprüfung + Hashing + Unique-Index Refresh-Token.
3. S6 generische 500er, `APP_ENV=prod`/`APP_DEBUG=0` erzwingen.
4. S4 `login_throttling`; S9 `/api/doc` hinter Auth; S10 CORS entfernen; Security-Header in nginx.
5. F1/F2/F3 korrigieren.

**Phase 1 – Fundament (1–2 Wochen)**
CI-Pipeline, PHPStan/CS-Fixer/ESLint/Prettier, PHPUnit + Vitest Grundgerüst mit Tests für Auth und Zeiterfassung, Docker-Härtung, Messenger-Worker, Backups, `.dockerignore`, Aufräumen toter Dateien.

**Phase 2 – Sicherheitsarchitektur (1–2 Wochen)**
RBAC + Voter, Account-Verwaltung, Refresh-Token-Rotation mit Reuse-Detection, Audit-Log, BFF-Neuschnitt (Allowlist → typisierte Endpunkte), CSP.

**Phase 3 – API & Domäne (2–4 Wochen)**
`/api/v1`, Problem-Details, Response-DTOs, OpenAPI, Pagination, Statusmaschine, Soft-Delete/Storno, Belegnummernkreise, Transaktionen, ORM-3-Migration.

**Phase 4 – Compliance-Dokumentation (parallel)**
Verfahrensdokumentation (GoBD), VVT/TOMs (DSGVO), Lösch-/Aufbewahrungskonzept inkl. technischer Umsetzung, Rollen-/Berechtigungskonzept, Betriebshandbuch, Incident-/Backup-Restore-Prozess. Vor Rechnungsmodul: E-Rechnung (ZUGFeRD/XRechnung) und Nummernkreise festlegen.
