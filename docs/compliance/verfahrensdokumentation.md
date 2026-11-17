# Verfahrensdokumentation (GoBD) – technischer Teil

Grundlage für die Verfahrensdokumentation nach GoBD (BMF-Schreiben, Rz. 151 ff.). Allgemeine Beschreibung, Anwenderdokumentation und Betriebsdokumentation sind durch das Unternehmen zu ergänzen.

## Belegarten

| Beleg | Entstehung | Nummerierung | Änderbarkeit |
|---|---|---|---|
| Lieferschein | Erfassung in Workers | `LS-<Jahr>-<laufende Nr.>`, lückenlos je Jahr, unter Sperre vergeben | änderbar; jede Änderung mit Vorher/Nachher-Wert, Zeitpunkt und Bearbeiter im Audit-Log; keine Löschung, nur Storno |
| Rückgabeschein | Erfassung zur Lieferung | gehört zum Lieferschein | Korrektur möglich, protokolliert |
| Arbeitszeitnachweis | Erfassung durch Beschäftigte | Monat/Jahr je Person | Korrekturen protokolliert (inkl. gelöschter Einträge mit letztem Stand) |

Positionen speichern den Artikelnamen zum Zeitpunkt der Erfassung, sodass spätere Stammdatenänderungen den Beleg nicht verändern.

## Unveränderbarkeit und Nachvollziehbarkeit (Rz. 58 ff.)

- Alle Einfügungen, Änderungen und Löschungen fachlicher Daten werden automatisch im Audit-Log gespeichert (Doctrine-Listener, in derselben Transaktion wie die Änderung).
- Das Audit-Log ist eine Hash-Kette (SHA-256 über Inhalt und Vorgänger). Datenbank-Trigger verhindern `UPDATE` und `DELETE`; nur die dokumentierte Fristbereinigung darf älteste Einträge löschen und wird selbst protokolliert.
- Integritätsprüfung: `bin/console app:audit:verify` bzw. Oberfläche *Audit-Log → Integrität prüfen*. Erkannt werden veränderte, eingefügte, entfernte und abgeschnittene Einträge.

## Aufbewahrung (Rz. 113 ff.)

- Lieferscheine als Handelsbriefe: 6 Jahre (§ 257 HGB, § 147 AO); sofern sie als Buchungsbeleg dienen: 8 Jahre (§ 147 Abs. 3 AO i. d. F. ab 2025).
- Audit-Log: Standard 6 Jahre (`AUDIT_RETENTION_DAYS`), an die längste relevante Frist anpassen.
- Datensicherung täglich, Aufbewahrung der Sicherungen gemäß Betriebshandbuch.

## Datenzugriff der Finanzverwaltung (Rz. 158 ff.)

Lesender Zugriff über ein Konto mit Leserechten bzw. Datenexport per Datenbank-Dump. Ein strukturierter Export (z. B. CSV je Belegart) ist als Erweiterung vorgesehen, bevor Rechnungen über Workers erzeugt werden.

## Vor Einführung von Rechnungen

- E-Rechnung (XRechnung/ZUGFeRD) gemäß Wachstumschancengesetz: Empfang seit 2025, Versand ab 2027/2028.
- Eigener Nummernkreis, Unveränderbarkeit nach Versand (Storno/Gutschrift statt Änderung), revisionssichere Archivierung der erzeugten Dokumente.
