# Sicherheit

## Schwachstellen melden

Bitte keine öffentlichen Issues für Sicherheitslücken. Meldung vertraulich an die Verwaltung bzw. den Repository-Eigentümer mit Beschreibung, betroffener Version und Reproduktionsschritten. Wir bestätigen den Eingang innerhalb von 5 Werktagen.

## Sicherheitsmaßnahmen (Überblick)

| Bereich | Maßnahme |
|---|---|
| Authentifizierung | Passwort-Hashing (bcrypt/argon2 via Symfony `auto`), Passwortrichtlinie ≥ 12 Zeichen + Stärkeprüfung, Login-Drosselung (5 Fehlversuche / 15 min je Konto+IP), einheitliche Fehlermeldung (keine Konto-Enumeration) |
| Sitzungen | JWT 15 min (RS256), Refresh-Token 7 Tage gleitend / 30 Tage absolut, nur als SHA-256-Hash gespeichert, Rotation bei jeder Nutzung, Wiederverwendung ⇒ ganze Sitzung gesperrt; Widerruf bei Logout, Passwortwechsel, Deaktivierung |
| Cookies | `__Host-`-Präfix, HttpOnly, Secure, SameSite=Lax; Tokens nie im Browser-JavaScript |
| Autorisierung | Rollen `ROLE_USER`/`ROLE_ADMIN` serverseitig je Endpunkt, Objektprüfung (Voter) für Zeiterfassung, Adress-Zugehörigkeit wird geprüft, Konto-Status wird bei jedem Request neu geladen |
| Eingaben | Validierung aller DTOs (Symfony Validator), UUID-Routenanforderungen, Proxy-Allowlist im BFF |
| Ausgaben / Fehler | RFC 9457, keine internen Details in Antworten, Korrelations-ID für Support |
| Web | CSP mit Nonces, X-Frame-Options/frame-ancestors, HSTS, Referrer-Policy, Permissions-Policy, CSRF-Schutz (Origin-Prüfung) |
| Nachvollziehbarkeit | Audit-Log aller Datenänderungen und Sicherheitsereignisse, Hash-Kette, DB-Trigger gegen UPDATE/DELETE, Prüfbefehl `app:audit:verify` |
| Infrastruktur | API nicht öffentlich, Container non-root/read-only/`cap_drop: ALL`, gepinnte Images, Trivy- und Gitleaks-Scans, Dependabot |
