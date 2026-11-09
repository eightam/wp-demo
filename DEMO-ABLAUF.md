# Demo-Ablauf: Pflegen im modernen WordPress

Für Kundinnen und Kunden, die von einem alten Contao kommen. Dauer rund 25 Minuten. Die Leitidee: **Man bearbeitet die Seite so, wie sie aussieht, und das System lässt nur zu, was gewollt ist.**

**Vorbereitung**
- Zwei Browserfenster: links die Website `http://wp-demo.localhost`, rechts das Backend.
- Im Backend als **`redaktion`** anmelden (Rolle Redakteur, Passwort in `bin/.demo-credentials`). So sieht die Kundschaft genau die eigenen Rechte und Sperren, nicht die Admin-Sicht.
- Kontrast zu Contao bewusst benennen: dort Artikel, Inhaltselemente und Formulare ohne Vorschau, hier die echte Seite.

---

## 1. Die Website als Ausgangspunkt (2 Min.)
**Frontend:** Startseite zeigen, kurz scrollen. Dann das Menü: über «Leistungen» und «Referenzen» fahren, die Untermenüs öffnen. Eine Referenz anklicken.
> «Alles, was Sie hier sehen, pflegen Sie gleich selbst, und zwar genau so, wie es hier aussieht.»

## 2. Übersicht nach dem Login (2 Min.)
**Backend:** [Dashboard](http://wp-demo.localhost/wp-admin/)
- Widget **«Inhalte zur Prüfung»**: welche Seite überfällig ist und wer zuständig ist.
- Links das Menü: Seiten, Referenzen, Medien. Mehr braucht die Redaktion nicht.
> «Das System erinnert Sie, wenn ein Inhalt veraltet.»

## 3. Eine Seite bearbeiten: was man sieht, ist was man bekommt (5 Min.)
**Backend:** Seiten → **Unternehmen** öffnen.
- Die Seite sieht im Editor aus wie auf der Website, nicht wie ein Formular.
- Auf die grosse Überschrift klicken und tippen. Ein Bild im Abschnitt «Text und Bild» per Klick ersetzen (Mediathek).
- **Gesperrte Abschnitte zeigen:** Einen Abschnitt anklicken. Texte und Bilder sind änderbar, aber man kann die Gestaltung nicht kaputt machen (keine Spalten verschieben, keine Farben, keine fremden Elemente).
- Oben **Rückgängig** und rechts **Revisionen**: jede gespeicherte Fassung ist wiederherstellbar.
> «Sie können Inhalte ändern, nicht das Design. Das hält die Website über Jahre sauber.»

## 4. Geführte Felder statt freier Text (3 Min.)
**Backend:** gleiche Seite, rechts Tab **«Seite»**.
- Oben in der Zusammenfassung: **Rubrik, Ansprechperson, Prüfen bis**. Ansprechperson ist eine Auswahl aus den Benutzern, kein Freitext.
- Weiter unten das Panel **«Seitenkopf & Teaser»**: Claim im Panel ändern, oben auf der Seite ändert er sich sofort mit (und umgekehrt).
- Claim leeren: Warnung erscheint, **Speichern ist gesperrt**.
> «Pflichtfelder sind wirklich Pflicht. Unvollständige Seiten gehen nicht online.»

## 5. Neue Inhalte aus Bausteinen (3 Min.)
**Backend:** Seiten → **Seite hinzufügen** (oder auf einer bestehenden Seite unten).
- Blau **«+»** → Tab «Vorlagen» → Kategorie **«Konstrukta»**: Bühne, Leistungen, Kundenstimme, Aufruf-Band usw.
- Eine Vorlage einfügen, Texte überschreiben. Fertig ist ein neuer Abschnitt im Corporate Design.
- Optional den Block **«Kontakt-Box»** zeigen: Layout zentral gepflegt, Name und Text pro Seite anpassbar.
> «Ihre Agentur liefert die Bausteine, Sie stellen die Seiten zusammen.»
> (Entwurf danach nicht speichern oder in den Papierkorb legen.)

## 6. Strukturierte Daten: Referenzen (5 Min.)
**Backend:** [Referenzen → Alle Projekte](http://wp-demo.localhost/wp-admin/edit.php?post_type=kx_projekt&page=kx-projekte)
- Moderne Liste: Suche, Filter (z. B. Branche), Ansicht Tabelle/Raster umschalten.
- Über eine Zeile fahren → **Schnellbearbeitung**: Daten im Fenster ändern, speichern, ohne die Liste zu verlassen.
- Ein Projekt öffnen: Statt einer leeren Seite erscheint eine **Pflegemaske** (Kunde, Branche, Jahr, Titelbild, Kennzahlen, Galerie). Zeilen hinzufügen, verschieben, löschen.
- «Kunde» leeren: Speichern gesperrt, das Feld wird rot.
- Im Frontend die Referenz neu laden: Detailseite, Karten auf der Startseite und das Menü «Referenzen» füllen sich automatisch aus diesen Feldern.
> «Einmal erfassen, überall korrekt: Übersicht, Detailseite, Startseite, Menü.»

## 7. Wiederkehrende Elemente, einmal gepflegt (2 Min.)
**Backend:** Seite **Kontakt** öffnen (als Administrator, falls die Redaktion keine Firmendaten ändern darf).
- Telefonnummer im Abschnitt «Erreichbarkeit» ändern → auch im **Footer** jeder Seite geändert.
- Als `redaktion` sind diese Felder gesperrt: zeigen, dass Rechte pro Rolle greifen.
> «Adresse und Telefon gibt es nur einmal. Keine veralteten Nummern mehr auf Unterseiten.»

## 8. Inhaltsblöcke mit eigener Maske (2 Min.)
**Backend:** Seite **Unternehmen** → Block **«Kennzahlen»** oder **«Team»** anklicken.
- Ausgewählt: aufgeräumte Eingabemaske. Abgewählt: die fertige Darstellung.
- Rechts in der Seitenleiste Einstellungen wie «Darstellung: Hell/Dunkel/Verlauf».
> «Auch komplexe Elemente pflegen Sie wie ein Formular, sehen aber sofort das Ergebnis.»

## 9. Abschluss (1 Min.)
- Seiten → Alle Seiten: Spalten **Prüfen bis** und **Ansprechperson** zeigen.
- Kurz zusammenfassen: **sehen, was man bearbeitet · geführt statt frei · Fehler werden verhindert · alles ist rückgängig machbar.**

---

**Falls Zeit bleibt:** Handy-Ansicht der Website (Menü-Overlay), Vorschau-Knopf im Editor (Desktop/Tablet/Handy), Medienbibliothek.

**Was man eher nicht zeigt:** Site Editor / Templates, Plugins, Einstellungen. Das ist Agentursache und verwirrt beim ersten Mal.
