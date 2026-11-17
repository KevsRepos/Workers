# Datenschutz (DSGVO) – technische Dokumentation

Diese Datei dokumentiert die Verarbeitung aus technischer Sicht und dient als Grundlage für das Verzeichnis von Verarbeitungstätigkeiten (Art. 30) und die TOMs (Art. 32). Die rechtliche Bewertung und Freigabe erfolgt durch den Verantwortlichen bzw. die/den Datenschutzbeauftragte(n).

## Verarbeitungstätigkeiten

| Tätigkeit | Betroffene | Daten | Zweck / Rechtsgrundlage | Löschung |
|---|---|---|---|---|
| Kundenverwaltung, Lieferscheine | Kunden, Ansprechpartner | Name, Firma, Adressen, Telefon, E-Mail, Bestellungen | Vertragserfüllung, Art. 6 Abs. 1 lit. b; Aufbewahrung lit. c | nach Ablauf der handels-/steuerrechtlichen Fristen (6/8/10 Jahre) Anonymisierung; vorher nur Sperrung möglich |
| Arbeitszeiterfassung | Beschäftigte | Arbeitsbeginn/-ende, Pausen, Abwesenheitsart (Urlaub/Krank/Feiertag), Notiz | gesetzliche Pflicht (§ 16 Abs. 2 ArbZG, BAG 1 ABR 22/21), § 26 BDSG | `TIME_TRACKING_RETENTION_YEARS` Jahre nach Ablauf des Kalenderjahres (Standard 2 = Minimum nach § 16 Abs. 2 ArbZG), Löschung per `app:time-tracking:purge` |
| Benutzerkonten | Beschäftigte | Name, E-Mail, Passwort-Hash, Rollen, letzte Anmeldung | Zugangssteuerung, Art. 6 Abs. 1 lit. b/f | Konto deaktivieren bei Austritt; Löschung nach Ablauf der Fristen der zugeordneten Daten |
| Audit- und Sicherheitsprotokoll | Beschäftigte, ggf. Kunden (in Änderungen) | Zeitpunkt, Konto, Aktion, alte/neue Werte, IP-Adresse, User-Agent | Nachvollziehbarkeit (GoBD), Sicherheit, Art. 6 Abs. 1 lit. c/f | `AUDIT_RETENTION_DAYS` (Standard 6 Jahre), Bereinigung per `app:audit:purge` |
| Sitzungen | Beschäftigte | Refresh-Token-Hash, Ablaufzeiten | Sicherheit | täglich automatisch nach Ablauf + 30 Tage |
| Erinnerungs-E-Mails | Beschäftigte | E-Mail-Adresse, Uhrzeit, Wochentage | Unterstützung der Erfassungspflicht, Einwilligung/lit. f (freiwillig, vom Nutzer selbst aktiviert) | mit dem Konto |

Hinweis: Krankheitstage sind als Abwesenheitsart gespeichert, **ohne** Diagnose oder Gesundheitsdaten. Notizen bei Krankheit sollen keine Gesundheitsinformationen enthalten (Hinweis an Beschäftigte).

## Betroffenenrechte

- **Auskunft (Art. 15)**: Kundendaten über die Kundenansicht, Änderungshistorie über das Audit-Log (Filter Objekt-ID).
- **Berichtigung (Art. 16)**: direkt in der Anwendung, Änderung wird protokolliert.
- **Löschung (Art. 17)**: *Kunde → Datenschutz → Anonymisieren* (nur Verwaltung). Technisch verhindert, solange Lieferscheine der gesetzlichen Aufbewahrung unterliegen (Art. 17 Abs. 3 lit. b).

## Technische und organisatorische Maßnahmen (Auszug)

| Ziel | Maßnahmen |
|---|---|
| Zutritts-/Zugangskontrolle | individuelle Konten, starke Passwortrichtlinie, Login-Drosselung, Sitzungsablauf, sofortige Sperre bei Deaktivierung |
| Zugriffskontrolle | Rollenmodell (siehe Berechtigungskonzept), serverseitige Prüfung inkl. Objektebene |
| Weitergabekontrolle | TLS 1.2/1.3, HSTS, API nur intern erreichbar, keine Drittanbieter-Skripte (CSP) |
| Eingabekontrolle | lückenloses, manipulationsgeschütztes Audit-Log mit Akteur, Zeitpunkt, Vorher/Nachher-Werten |
| Verfügbarkeit | tägliche Backups mit Prüfsumme, dokumentierter Restore-Test, Health-Checks, automatischer Neustart |
| Trennung | eigene Datenbank, getrennte Netze (internes Docker-Netz ohne Internetzugang) |
| Datenminimierung | nur notwendige Felder, Entwürfe im Browser nur in `sessionStorage` (endet mit dem Tab), automatische Löschung abgelaufener Sitzungen |
| Pseudonymisierung/Verschlüsselung | Passwörter und Tokens nur als Hash; Backups vor Auslagerung verschlüsseln (Betriebshandbuch) |

## Arbeitszeitrecht (ArbZG) in der Anwendung

| Vorschrift | Umsetzung |
|---|---|
| Erfassungspflicht (BAG 2022, § 16 Abs. 2) | Beginn, Ende, Pause je Tag; Nachtschichten über Mitternacht; Erinnerungs-E-Mail optional |
| § 3 Höchstarbeitszeit 10 h | Hinweis am Eintrag und in der Monatsübersicht |
| § 4 Ruhepausen 30/45 min | Hinweis bei Unterschreitung |
| § 5 Ruhezeit 11 h | Hinweis, wenn zwischen Ende des Vortags (auch Vormonat) und Beginn weniger als 11 h liegen |
| §§ 9–11 Sonntagsarbeit | Hinweis mit Verweis auf Ersatzruhetag |
| Nachvollziehbarkeit | Korrekturen (auch durch die Verwaltung) mit Vorher/Nachher-Wert im Audit-Log; Arbeitszeit kann nicht für zukünftige Tage erfasst werden |
| Aufbewahrung | mind. 2 Jahre, danach Löschung (siehe oben) |

Hinweise blockieren das Speichern bewusst nicht: Der Nachweis muss die tatsächlich geleistete Zeit zeigen. Verstöße werden sichtbar gemacht, damit die Verwaltung reagieren kann.

**Zielkonflikt Audit-Log:** Das Audit-Log enthält die Werte geänderter Zeiteinträge bis zum Ablauf von `AUDIT_RETENTION_DAYS` (Nachweis gegenüber Prüfbehörden, GoBD für Lieferscheine). Für die Zeiterfassung allein würde eine kürzere Frist genügen; die gewählte Frist ist im Verzeichnis der Verarbeitungstätigkeiten zu begründen oder `AUDIT_RETENTION_DAYS` entsprechend zu verkürzen.

## Offene organisatorische Punkte

- Verzeichnis der Verarbeitungstätigkeiten auf Basis dieser Tabelle formal erstellen.
- Information der Beschäftigten zur Arbeitszeiterfassung und zum Audit-Log (Art. 13, ggf. Betriebsrat § 87 Abs. 1 Nr. 6 BetrVG).
- AV-Verträge mit Hosting- und E-Mail-Anbieter abschließen.
- Jährlichen Löschlauf festlegen (Januar): `app:time-tracking:purge --dry-run`, dann ohne `--dry-run`; Kundendaten nach Fristablauf anonymisieren. Aufbewahrungsdauer der Zeiterfassung mit Steuerberatung abstimmen, falls sie als Lohnunterlage dient.
