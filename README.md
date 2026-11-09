# wp-demo – Pflegen im Block-Editor

Demo-Website der erfundenen **Konstrukta Engineering AG** (Inhalte inspiriert von einem Schweizer Ingenieurbüro, alles fiktiv). Sie zeigt nebeneinander, wie man Redaktionen im modernen WordPress-Backend führt: von der schemagesteuerten Pflegemaske aus [`model-block.md`](model-block.md) bis zu allen Alternativen aus [`additional.txt`](additional.txt).

Local-Site: `http://wp-demo.localhost` · WordPress 7.1 · Kindthema von Twenty Twenty-Five · ACF Pro nur für den Vergleich.

## Was wo zu sehen ist

| Technik | Wo im Backend | Code |
| --- | --- | --- |
| **Schema-Maske, Daten in Post-Meta** (wie `pinto/model-configurator`) | Referenzen → ein Projekt | `konstrukta-core/blocks/projekt-daten`, `assets/js/schema-form.js` |
| Template gesperrt (`template_lock: all`) | Projekt: Maske lässt sich nicht löschen, nichts darunter einfügen | `includes/post-types.php` |
| Pflichtfelder sperren das Speichern (`lockPostSaving`) | Projekt: «Kunde» leeren | `useRequired()` in `schema-form.js` |
| Meta-Felder in Revisionen (`revisions_enabled`) | Projekt → Revisionen | `includes/meta.php` |
| **Schema-Maske, Daten als Blockattribute** – Maske bei Auswahl, Server-Vorschau sonst | Unternehmen / Startseite: Blöcke «Kennzahlen», «Standorte» (Auge-Symbol in der Werkzeugleiste = Vorschau) | `includes/blocks.php`, `blocks/kennzahlen`, `blocks/standorte` |
| `control()` und `field()` zusammengelegt: ein `Field` für Leinwand und Seitenleiste | Seitenleiste von «Kennzahlen», «Projektkarten» | `Field` in `schema-form.js` |
| **DataForm** (`@wordpress/dataviews`) mit Validierung | Jede Seite → Seitenleiste: «Zuordnung» (Rubrik, Ansprechperson als Benutzer, Prüfen bis) oben in der Zusammenfassung, Panel «Seitenkopf & Teaser (DataForm)» weiter unten | `src/dataform.js` |
| **DataViews + DataForm** im Admin, Schnellbearbeitung | Referenzen → Übersicht (DataViews) | `src/projekte-admin.js`, `includes/admin.php` |
| **Gesperrte Patterns** (`templateLock: contentOnly`) | Alle Abschnitte mit «(gesperrt)» im Namen | `themes/konstrukta/patterns/*.php` |
| **Editierbare Block-Bindings** an Post-Meta | Seitenkopf: Claim/Teaser auf der Leinwand tippen → DataForm ändert sich mit (und umgekehrt) | `patterns/seitenkopf-gebunden.php` |
| Eigene Bindings-Quelle, editierbar (Option) | Kontakt / Footer: Firmendaten (nur für Admins editierbar) | `includes/bindings.php`, `registerBlockBindingsSource` in `schema-form.js` |
| Eigene Bindings-Quelle, lesend | Projekt-Detailseite | `konstrukta/projekt` in `includes/bindings.php` |
| **Synced Pattern mit Overrides** | Leistungen, Kontakt: «Kontakt-Box» – Layout zentral, Name/Text/Foto pro Seite | `bin/seed.php` (wp_block) |
| **ACF-Pro-Block** zum Vergleich | Unternehmen: «Team (ACF)» – im Code-Editor sieht man `team_0_name …` | `acf-blocks/team`, `includes/acf.php` |

Das Dashboard-Widget «Demo: Pflegen im Block-Editor» verlinkt alle Stellen direkt.

## Das eine Schema

`konstrukta-core/includes/schema.php` deklariert jedes Feld genau einmal. Daraus entstehen:

- `register_post_meta()` mit REST-Schema, Säuberung, Rechteprüfung und Revisionen,
- die Attribute der Inhaltsblöcke (nicht in `block.json`),
- die Canvas-Maske (`window.kxEditor.schemas`, ohne Build),
- die DataForm-Felder (`src/schema-to-dataform.js`); Medien, Verläufe und Listen nutzen dort dieselben Komponenten wie die Maske.

`storage: meta` schreibt mit `editPost`, `storage: attributes` mit `setAttributes`. Feldtypen: text, textarea, number, select, toggle, url, email, date, media, gradient, rows.

## Build

Nur DataForm/DataViews brauchen einen Bundler (das Paket ist in Core nicht als Script verfügbar). `build/` ist eingecheckt; neu bauen mit:

```bash
cd app/public/wp-content/plugins/konstrukta-core && npm install && npm run build
```

## Einrichten

In einer frischen Local-Site (Site-Shell, damit WP-CLI die Datenbank findet):

```bash
bin/setup.sh
```

Aktiviert Theme und Plugins, stellt auf de_CH, erzeugt Bilder als Anhänge, Seiten, Projekte, News, Navigation, die Kontakt-Box und den Benutzer `redaktion` (Rolle Redakteur, Passwort in `bin/.demo-credentials`, nicht versioniert). Mehrfach ausführbar. Platzhalterbilder neu erzeugen: `php bin/generate-images.php`.

## Grenzen der Demo

- Firmendaten-Binding: Redakteure sehen die Werte, dürfen sie aber nicht ändern (Option braucht `manage_options`) – gewollt, zeigt `canUserEditValue`.
- `@wordpress/dataviews` ist gebündelt (v22) und nutzt Core-Komponenten; bei WP-Updates neu bauen und testen.
- Das ACF-Feld-Setup liegt im Code (`acf_add_local_field_group`), nicht in der Datenbank.
