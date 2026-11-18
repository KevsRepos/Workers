# Berechtigungskonzept

Stand: 2026-11 · gilt für Workers ab Version 1.0 · Verantwortlich: Geschäftsführung / Verwaltung

## Rollen

| Rolle | Zielgruppe | Vergabe |
|---|---|---|
| `ROLE_USER` (Mitarbeiter) | alle Beschäftigten mit Zugang | durch Verwaltung beim Anlegen des Kontos |
| `ROLE_ADMIN` (Verwaltung) | Geschäftsführung, Büro | nur durch bestehende Administratoren; mind. ein aktiver Admin wird technisch erzwungen |

## Rechte-Matrix

| Funktion | Mitarbeiter | Verwaltung |
|---|:-:|:-:|
| Kunden lesen, anlegen, ändern, Adressen pflegen | ✔ | ✔ |
| Kunden anonymisieren (Art. 17 DSGVO) | – | ✔ |
| Lieferscheine lesen, anlegen, ändern, Status, Rückgabe, Druck | ✔ | ✔ |
| Artikel, Pfand, Verbünde lesen | ✔ | ✔ |
| Artikel, Preise, Pfand, Verbünde ändern | – | ✔ |
| Bestellvorlagen pflegen | ✔ | ✔ |
| Eigene Zeiterfassung | ✔ | ✔ |
| Zeiterfassung anderer lesen/korrigieren | – | ✔ (protokolliert) |
| Benutzer verwalten, Passwörter setzen | – | ✔ |
| Audit-Log einsehen und prüfen | – | ✔ |

Die Durchsetzung erfolgt ausschließlich serverseitig (`#[IsGranted]`, `TimeSheetVoter`); die Oberfläche blendet nicht erlaubte Funktionen nur zusätzlich aus.

## Lebenszyklus von Konten

- **Eintritt**: Admin legt Konto an, übermittelt Startpasswort auf sicherem Weg, Nutzer ändert es unter *Einstellungen*.
- **Rollenwechsel**: Änderung durch Admin, wirksam mit dem nächsten Request (Rollen werden bei jedem Request aus der DB geladen).
- **Austritt**: Konto deaktivieren (nicht löschen – Zeiterfassung und Audit bleiben zuordenbar). Alle Sitzungen enden sofort.
- **Überprüfung**: halbjährlich Liste der aktiven Konten und Admins prüfen und Ergebnis dokumentieren.

Alle Änderungen an Konten werden im Audit-Log protokolliert (Passwort-Hashes werden nicht protokolliert).
