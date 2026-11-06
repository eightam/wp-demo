# PINTO Modell-Daten — wie die Pflegemaske gebaut ist

Der Block **PINTO Modell-Daten** (`pinto/model-configurator`) ist die
Eingabemaske eines Modells. Er sieht aus wie ein Block, ist aber ein Formular:
er hat keine Attribute, schreibt direkt in die Meta-Felder des Beitrags und
gibt auf der Website nichts aus. Die Detailseite baut das Theme aus denselben
Feldern zusammen.

## Bausteine

| Datei | Aufgabe |
| --- | --- |
| `plugins/pinto-core/includes/meta.php` | registriert die Felder (`_pinto_price`, `_pinto_specs` …) mit Typ, REST-Schema, Säuberung und Rechteprüfung |
| `plugins/pinto-core/includes/post-types.php` | setzt den Block als `template` des Inhaltstyps `pinto_model` — jedes neue Modell beginnt mit der Maske |
| `plugins/pinto-core/blocks/model-configurator/block.json` | Metadaten; `multiple: false`, `reusable: false`, alle Gestaltungs-Supports aus |
| `plugins/pinto-core/blocks/model-configurator/render.php` | gibt `''` zurück |
| `plugins/pinto-core/assets/js/editor.js` | die Maske selbst: `configFields`, `configTables`, `RowEditor`, `ModelConfigurator` (Abschnitt «Modell-Daten») |
| `plugins/pinto-core/assets/css/editor.css` | Gestaltung der Maske (`.pt-config…`) |
| `plugins/pinto-core/includes/editor.php` | `pintoEditorData.gradients`: die Verläufe aus `theme.json` für die Farbwahl |
| `plugins/pinto-core/includes/helpers.php` | `pinto_model_data()`: liest alle Felder gebündelt und mit Rückfallwerten |
| `themes/pinto/templates/single-pinto_model.html` | Detailseite; ihre Blöcke (`model-context`, `model-gallery`, `model-options`, `data-table`, `model-cards`) lesen über `pinto_model_data()` |

## Datenfluss

```
Maske (editor.js)
  └─ editPost( { meta: { _pinto_<key>: wert } } )      core/editor-Store, mit Rückgängig
       └─ «Speichern» → REST /wp/v2/modelle/<id>
            ├─ Schema-Prüfung (show_in_rest.schema)       nur deklarierte Spalten, nur Zeichenketten
            ├─ auth_callback → current_user_can( 'edit_post' )
            └─ sanitize_callback → pinto_sanitize_rows()  leere Zeilen fallen weg
                 └─ wp_postmeta (eine Liste = ein Schlüssel, serialisiert)
                      └─ pinto_model_data() → Blöcke des Templates
```

Gespeichert wird mit dem Beitrag, nicht pro Feld. Bis «Speichern» liegen die
Änderungen nur im Store des Editors; der Editor kennt sie deshalb als
ungespeicherte Änderungen und warnt beim Verlassen.

## Die Maske

Die Maske ist beschreibend aufgebaut: zwei Listen sagen, was erfasst wird,
zwei Komponenten zeichnen es.

**`configFields`** — Einzelwerte im Panel «Grunddaten», zweispaltig. Je Feld:
`key` (Meta-Schlüssel ohne `_pinto_`), `label`, `help`, optional `type`
(`text` · `number` · `select` · `textarea` · `toggle`), `options` und `wide`
(volle Breite).

**`configTables`** — Listen, je ein zugeklapptes Panel. Je Tabelle: `key`,
`title`, `help`, `empty` (Text ohne Zeilen), `add` (Beschriftung der
Schaltfläche) und `columns`. Je Spalte: `key`, `label`, `weight` (relative
Breite), `placeholder` und optional `type`:

- ohne Typ — Textfeld
- `media` — `MediaCell`: Vorschau, Mediathek, «Entfernen»; speichert die
  Anhang-ID als Zeichenkette
- `gradient` — `GradientCell`: Farbmuster plus Auswahl der Verläufe aus
  `theme.json`; ein von Hand gesetzter CSS-Wert bleibt als «Eigener Wert»
  wählbar

**`RowEditor`** zeichnet eine Tabelle: Spaltenköpfe, Zeilen, je Zeile
«Nach oben», «Nach unten», «Entfernen», darunter «hinzufügen». Vor jedem
Schreiben bringt `clean()` jede Zeile auf genau die deklarierten Spalten und
macht alle Werte zu Zeichenketten. Ohne das scheiterte das Speichern an einer
Zeile, die per WP-CLI oder Import einen zusätzlichen Schlüssel bekommen hat.

**`ModelConfigurator`** liest `meta` aus `core/editor`, schreibt über
`editPost` und setzt beides zusammen. Steht der Block nicht auf einem Modell,
zeigt er nur einen Hinweis.

## Warum so

- **Auf der Leinwand, nicht in der Seitenleiste.** Fünf der zwölf Felder sind
  Tabellen. In der 280 px schmalen Seitenleiste stehen keine vier Spalten
  nebeneinander.
- **Meta statt Blockattribute.** Die Daten gehören zum Modell, nicht zu einer
  Seite: Modellkarten, Vergleich und Block-Bindings lesen sie von überall.
  Attribute stünden im Seiteninhalt und wären nur dort lesbar.
- **Kein ACF.** Die Blöcke lesen eine Liste als ein Feld. Ein ACF-Repeater legt
  jede Zelle als eigenen Schlüssel ab (`specs_0_k`, `specs_0_v` …); dazwischen
  bräuchte es eine Übersetzung.
- **Dynamischer Block mit leerem `render.php`.** Ein statischer Block mit
  `save()` würde Markup in den Inhalt schreiben, und jede Änderung an der
  Maske machte bestehende Modelle «ungültig».
- **Symbole als Inline-SVG.** Die Leinwand ist ein iframe ohne
  Dashicon-Schrift; `icon: 'trash'` bliebe dort ein leeres Kästchen.
- **Kein Build-Schritt.** `wp.element.createElement` statt JSX — es gibt
  keine Werkzeugkette, die gepflegt werden muss.

## Ein Feld hinzufügen

1. `includes/meta.php` — Schlüssel in `$model_scalars` oder `$model_rows`
   eintragen (bei Listen mit allen Spalten).
2. `assets/js/editor.js` — denselben Schlüssel in `configFields` oder
   `configTables` beschreiben. Spaltenschlüssel müssen exakt denen aus Schritt 1
   entsprechen, sonst weist die REST-Schnittstelle das Speichern zurück.
3. `includes/helpers.php` — in `pinto_model_data()` mit Rückfallwert ausgeben.
4. Den Block, der das Feld zeigt, anpassen.
5. `bin/version.sh bump plugin minor` (neues Feld = Minor, siehe `CLAUDE.md`).

Spalte umbenennen oder entfernen heisst: gespeicherte Daten verlieren ihre
Werte beim nächsten Speichern — das ist ein Major-Schritt und braucht eine
Migration.

## Grenzen

- **Das Schema steht zweimal.** Schlüssel und Spalten sind in `meta.php` und in
  `editor.js` deklariert; nichts prüft, dass beide übereinstimmen.
- **Das Template ist nicht gesperrt.** Ohne `template_lock` lässt sich der
  Block löschen, und unter ihm lassen sich weitere Blöcke einfügen, die die
  Detailseite nie ausgibt. Die Daten bleiben beim Löschen erhalten, die Maske
  ist aber weg, bis jemand den Block wieder einfügt.
- **Keine Pflichtfelder.** Ein Modell ohne Preis oder Ansicht lässt sich
  veröffentlichen; die Detailseite fällt dann auf Platzhalter zurück.
- **Keine Revisionen der Felder.** Die Meta-Felder sind nicht mit
  `revisions_enabled` registriert; eine Revision stellt den Titel wieder her,
  nicht Preis oder Ausstattung.
- **Auswahl ohne gespeicherten Wert.** «Kartenstimmung» zeigt «Hell», solange
  nichts gespeichert ist; `pinto_model_data()` fällt auf denselben Wert zurück,
  das Ergebnis stimmt also, die Datenbank bleibt aber leer.
