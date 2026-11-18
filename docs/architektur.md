# Architektur

## Komponenten

| Komponente | Technik | Aufgabe |
|---|---|---|
| nginx | nginx 1.28 | TLS, Security-Header, Rate-Limits, öffentlicher Einstieg (nur Frontend); interner Port 8080 für die API |
| Frontend / BFF | SvelteKit 2 (adapter-node) | Server-Rendering, Sitzungsverwaltung über HttpOnly-Cookies, Proxy `/api/*` mit Allowlist |
| API | Symfony 7.4 / PHP-FPM 8.4 | Fachlogik, Validierung, Autorisierung, Audit |
| Worker | gleiches Image | `messenger:consume scheduler_default`: Erinnerungsmails (alle 5 min), tägliche Wartung |
| Datenbank | MariaDB 12.3 | Daten, Audit-Log, Nummernkreise |
| Backup | mariadb-dump | nächtliche Sicherung mit Prüfsumme und Aufbewahrung |

## Backend-Struktur

```
src/
  Audit/            Audit-Log: Writer (Hash-Kette), Doctrine-Listener, Reader/Verifikation, API, Befehle
  Modules/
    Account/        Konten, Rollen, Login-Handler, Refresh-Tokens, Admin-API, CLI
    Customer/       Kunden, Adressen, Anonymisierung
    Pim/            Artikel, Pfand, Verbünde, Lieferscheine (+ Rückgabe, Vorlagen), HTTP-Controller
    TimeTracking/   Monatsblätter, Einträge, Voter, Erinnerungen
  Shared/           Basis-Entity, UUID-Typ, Exceptions, Problem-Details, Request-ID, Transaktion je Request,
                    Security-Header, Logging-Processor, Nummernkreise, Health-Check
```

Grundsätze:

- **Ein Request = eine Transaktion**: Jeder schreibende Request läuft in einer DB-Transaktion (`TransactionalControllerSubscriber`). Fachdaten und Audit-Einträge werden gemeinsam festgeschrieben oder gemeinsam verworfen.
- **Fehler**: Services werfen `ApiException`-Unterklassen (404/409/422/…); `ProblemDetailsSubscriber` erzeugt RFC-9457-Antworten. Unerwartete Fehler werden vollständig geloggt, nach außen nur mit Korrelations-ID beantwortet.
- **DTOs statt Entities** in Requests und Responses; Entities werden nie direkt serialisiert.
- **IDs**: UUIDv7, in der DB als `BINARY(16)`, in PHP/JSON als RFC-4122-String.
- **Belegnummern**: lückenlos je Jahr (`LS-2026-00001`), vergeben unter Zeilensperre in der laufenden Transaktion.
- **Lieferschein-Status** sind bewusst frei wählbar (betriebliche Praxis); jede Änderung wird protokolliert. Lieferscheine werden nicht gelöscht, sondern storniert.

## API

Basis `/api/v1`. Authentifizierung per `Authorization: Bearer <JWT>`.

| Bereich | Endpunkte |
|---|---|
| Auth | `POST auth/login`, `POST auth/refresh`, `POST auth/logout`, `GET auth/me`, `POST auth/change-password` |
| Kunden | `GET/POST customers`, `GET/PUT customers/{id}`, `POST customers/{id}/addresses`, `PUT customers/{id}/addresses/{addressId}`, `PUT customers/{id}/default-addresses`, `POST customers/{id}/anonymization` (Admin) |
| Artikel | `GET products`, `GET products/{id}`, `POST/PUT` (Admin); `deposits`, `product-unions` (Schreiben: Admin), `order-presets` |
| Lieferscheine | `GET/POST delivery-notes`, `GET/PUT delivery-notes/{id}`, `PUT …/status`, `PUT …/quantities`, `GET …/return-groups`, `PUT …/return-note`, `GET delivery-notes/status-counts` |
| Zeiterfassung | `GET time-sheets/years`, `GET time-sheets/{year}`, `GET time-sheets/{year}/{month}`, `POST time-sheets/{year}/{month}/entries`, `PUT/DELETE time-sheets/entries/{id}`, `GET/PUT time-tracking/reminder` – Admins mit `?accountId=` |
| Verwaltung | `admin/accounts` (CRUD, Passwort setzen), `admin/audit-log`, `admin/audit-log/verification` |
| Betrieb | `GET health` (öffentlich) |

Listen sind paginiert: `?page=1&limit=25` → `{ items, page, limit, total, pages }`.

## Frontend

- `src/hooks.server.ts`: Request-ID, Security-Header, Sitzung (Token-Erneuerung mit Schutz gegen parallele Rotation), Zugriffsschutz.
- `src/lib/server/`: Backend-Client, Sitzungs-Cookies, Proxy-Allowlist. Nur serverseitig.
- `src/routes/api/[...path]`: Same-Origin-Proxy für interaktive Komponenten (Origin- und Content-Type-Prüfung).
- `src/hooks.client.ts` + `src/routes/client-errors`: Meldung von Browserfehlern ins Server-Log (Referenz für den Support, Datenminimierung, Rate-Limit).
- `src/lib/components/ui/`: eigenes, barrierearmes UI-Kit (Formfelder mit Fehlerbindung, Combobox nach ARIA-Muster, Dialog, Toasts).
- Druckansichten (`…/print`) erzeugen A4-Dokumente direkt aus dem Browser.
