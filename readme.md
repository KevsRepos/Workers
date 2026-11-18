# Workers

Interne Web-Anwendung für Verkauf und Verleih: Kunden, Artikel mit Pfand, Lieferscheine mit Rückgabe und Druck, Bestellvorlagen, Arbeitszeiterfassung und ein Zeltplaner.

## Architektur

```
Browser ──HTTPS──> nginx ──> SvelteKit (BFF, Node) ──HTTP intern──> nginx:8080 ──FastCGI──> Symfony API (PHP-FPM) ──> MariaDB
                                                                                    └── Worker (Scheduler: Erinnerungen, Wartung)
```

- **Backend** (`backend/`): Symfony 7.4, PHP 8.4, Doctrine ORM 3. JSON-API unter `/api/v1`, Fehler als RFC 9457 (`application/problem+json`), Rollen `ROLE_USER`/`ROLE_ADMIN`, kurzlebige JWTs mit rotierenden Refresh-Tokens, manipulationssicheres Audit-Log (Hash-Kette + DB-Trigger).
- **Frontend** (`frontend/`): SvelteKit 2 / Svelte 5 / Tailwind 4. Arbeitet als Backend-for-Frontend: Tokens liegen ausschließlich in HttpOnly-Cookies, der Browser spricht nie direkt mit der API. Strikte CSP.
- **Betrieb** (`docker-compose.yml`, `deploy/`): gehärtete Container (non-root, read-only, keine Capabilities), API nicht öffentlich erreichbar, automatische TLS-Zertifikate, nächtliche Backups.

Details: [docs/architektur.md](docs/architektur.md) · Betrieb: [docs/betrieb.md](docs/betrieb.md) · Compliance: [docs/compliance/](docs/compliance) · Sicherheit: [SECURITY.md](SECURITY.md) · Audit-Bericht: [docs/AUDIT.md](docs/AUDIT.md)

## Entwicklung

Voraussetzungen: Docker, Node.js ≥ 22.12.

```bash
docker compose -f compose.dev.yaml up -d
docker compose -f compose.dev.yaml exec api composer install
docker compose -f compose.dev.yaml exec api bin/console lexik:jwt:generate-keypair --skip-if-exists
docker compose -f compose.dev.yaml exec api bin/console doctrine:migrations:migrate -n
docker compose -f compose.dev.yaml exec api bin/console app:account:create you@example.org Vorname Nachname --admin

cd frontend && cp .env.example .env && npm ci && npm run dev   # http://localhost:5173
```

E-Mails (Erinnerungen) landen in Mailpit: http://localhost:8025

## Tests und Qualität

| Bereich | Befehl |
|---|---|
| Backend: Code-Style, PHPStan, PHPUnit | `cd backend && composer qa` |
| Frontend: Prettier, ESLint, Typen | `cd frontend && npm run lint && npm run check` |
| Frontend: Unit-Tests | `cd frontend && npm test` |
| End-to-End (laufender Stack) | `cd frontend && E2E_BASE_URL=… E2E_ADMIN_EMAIL=… E2E_ADMIN_PASSWORD=… npm run test:e2e` |

Die CI (`.github/workflows/ci.yml`) führt alles aus, zusätzlich `composer audit`, `npm audit`, Trivy-Scan der Images und Gitleaks.

## Produktion

```bash
cp .env.example .env              # Werte setzen (openssl rand -hex 32)
docker compose --profile init run --rm certbot-init
docker compose up -d --build
docker compose run --rm php bin/console app:account:create admin@firma.de Vorname Nachname --admin
```

Siehe [docs/betrieb.md](docs/betrieb.md) für Backups, Wiederherstellung, Schlüsselrotation und Updates.
