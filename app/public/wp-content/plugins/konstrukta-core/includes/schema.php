<?php
/**
 * Das Schema – einmal deklariert, überall verwendet.
 *
 * Aus diesen Arrays entstehen:
 *  - register_post_meta() mit REST-Schema, Säuberung, Rechten, Revisionen (meta.php)
 *  - Blockattribute der Inhaltsblöcke (blocks.php)
 *  - die Canvas-Maske (assets/js/schema-form.js, ohne Build)
 *  - die DataForm-Felder (src/dataform.js, src/projekte-admin.js)
 *
 * Feldtypen: text · textarea · number · select · toggle · url · email · date · media · gradient · rows
 * rows-Spalten: text (Standard) · media · gradient
 *
 * storage:
 *  - meta        → editPost( { meta } ), gespeichert mit dem Beitrag
 *  - attributes  → setAttributes(), gespeichert im Blockkommentar
 */

defined( 'ABSPATH' ) || exit;

function kx_schemas(): array {
	static $schemas = null;
	if ( null !== $schemas ) {
		return $schemas;
	}

	$branchen = array(
		array( 'value' => 'bahn', 'label' => 'Bahn & Verkehr' ),
		array( 'value' => 'energie', 'label' => 'Energie' ),
		array( 'value' => 'industrie', 'label' => 'Industrie & Anlagenbau' ),
		array( 'value' => 'medtech', 'label' => 'Medizintechnik' ),
		array( 'value' => 'bau', 'label' => 'Bau & Infrastruktur' ),
	);

	$schemas = array(

		/* ------------------------------------------------------------------
		 * Referenzprojekt: Pflegemaske auf der Leinwand, Daten in Post-Meta.
		 * ---------------------------------------------------------------- */
		'projekt' => array(
			'title'     => 'Projektdaten',
			'storage'   => 'meta',
			'post_type' => 'kx_projekt',
			'prefix'    => 'kx_',
			'fields'    => array(
				array( 'key' => 'kunde', 'label' => 'Kunde', 'help' => 'Erscheint über dem Titel und auf der Karte.', 'required' => true ),
				array( 'key' => 'branche', 'label' => 'Branche', 'type' => 'select', 'options' => $branchen, 'default' => 'industrie' ),
				array( 'key' => 'jahr', 'label' => 'Abschluss', 'type' => 'number', 'help' => 'Jahr der Inbetriebnahme.', 'min' => 1990, 'max' => 2040 ),
				array( 'key' => 'standort', 'label' => 'Standort', 'help' => 'Ort, Land' ),
				array( 'key' => 'ansicht', 'label' => 'Titelbild', 'type' => 'media', 'required' => true, 'help' => 'Hauptbild für Karte und Kopf.' ),
				array( 'key' => 'stimmung', 'label' => 'Kartenstimmung', 'type' => 'gradient', 'default' => 'var(--wp--preset--gradient--stahl)', 'help' => 'Verlauf hinter Karte und Projektkopf.' ),
				array( 'key' => 'kurzbeschrieb', 'label' => 'Kurzbeschrieb', 'type' => 'textarea', 'wide' => true, 'help' => 'Zwei, drei Sätze. Wird per Block-Binding auf der Detailseite ausgegeben.' ),
				array( 'key' => 'hervorheben', 'label' => 'Auf der Startseite zeigen', 'type' => 'toggle', 'default' => false ),
				array(
					'key'     => 'kennzahlen',
					'label'   => 'Kennzahlen',
					'type'    => 'rows',
					'help'    => 'Drei bis vier Zahlen, die das Projekt greifbar machen.',
					'empty'   => 'Noch keine Kennzahlen.',
					'add'     => 'Kennzahl hinzufügen',
					'columns' => array(
						array( 'key' => 'wert', 'label' => 'Wert', 'weight' => 1, 'placeholder' => '120' ),
						array( 'key' => 'einheit', 'label' => 'Einheit', 'weight' => 1, 'placeholder' => 't' ),
						array( 'key' => 'label', 'label' => 'Beschriftung', 'weight' => 3, 'placeholder' => 'Gesamtgewicht der Anlage' ),
					),
				),
				array(
					'key'     => 'leistungen',
					'label'   => 'Unsere Leistungen im Projekt',
					'type'    => 'rows',
					'empty'   => 'Noch keine Leistungen.',
					'add'     => 'Leistung hinzufügen',
					'columns' => array(
						array( 'key' => 'titel', 'label' => 'Leistung', 'weight' => 2, 'placeholder' => 'FEM-Simulation' ),
						array( 'key' => 'text', 'label' => 'Beschreibung', 'weight' => 5, 'placeholder' => 'Was wir konkret gemacht haben' ),
					),
				),
				array(
					'key'     => 'galerie',
					'label'   => 'Galerie',
					'type'    => 'rows',
					'empty'   => 'Noch keine Bilder.',
					'add'     => 'Bild hinzufügen',
					'columns' => array(
						array( 'key' => 'bild', 'label' => 'Bild', 'type' => 'media', 'weight' => 2 ),
						array( 'key' => 'legende', 'label' => 'Legende', 'weight' => 4, 'placeholder' => 'Bildlegende' ),
						array( 'key' => 'flaeche', 'label' => 'Fläche', 'type' => 'gradient', 'weight' => 2 ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * Seiten-Einstellungen: DataForm in der Seitenleiste, Daten in Post-Meta.
		 * kx_claim und kx_teaser sind zusätzlich per Block-Binding im
		 * Seitenkopf-Pattern editierbar – beide Wege schreiben dasselbe Feld.
		 * ---------------------------------------------------------------- */
		'seite' => array(
			'title'     => 'Seitenkopf & Teaser',
			'storage'   => 'meta',
			'post_type' => 'page',
			'prefix'    => 'kx_',
			'ui'        => 'dataform',
			'fields'    => array(
				array( 'key' => 'claim', 'label' => 'Claim', 'help' => 'Grosse Zeile im Seitenkopf.', 'required' => true, 'maxLength' => 80 ),
				array( 'key' => 'teaser', 'label' => 'Teaser', 'type' => 'textarea', 'help' => 'Ein Satz unter dem Claim, auch für Übersichten.', 'maxLength' => 220 ),
				array( 'key' => 'rubrik', 'label' => 'Rubrik', 'type' => 'select', 'default' => 'unternehmen', 'options' => array(
					array( 'value' => 'unternehmen', 'label' => 'Unternehmen' ),
					array( 'value' => 'leistungen', 'label' => 'Leistungen' ),
					array( 'value' => 'referenzen', 'label' => 'Referenzen' ),
					array( 'value' => 'kontakt', 'label' => 'Kontakt' ),
				) ),
				array( 'key' => 'kontakt_email', 'label' => 'Ansprechperson (E-Mail)', 'type' => 'email', 'help' => 'Für Rückfragen zu dieser Seite.' ),
				array( 'key' => 'cta_url', 'label' => 'Link der Schaltfläche', 'type' => 'url', 'help' => 'Muss mit https:// oder / beginnen.' ),
				array( 'key' => 'pruefen_bis', 'label' => 'Inhalt prüfen bis', 'type' => 'date', 'help' => 'Erinnerung für die Redaktion.' ),
				array( 'key' => 'im_footer', 'label' => 'Im Footer verlinken', 'type' => 'toggle', 'default' => false ),
			),
		),

		/* ------------------------------------------------------------------
		 * Kennzahlen: Inhaltsblock, Daten als Blockattribute.
		 * ---------------------------------------------------------------- */
		'kennzahlen' => array(
			'title'   => 'Kennzahlen',
			'storage' => 'attributes',
			'block'   => 'konstrukta/kennzahlen',
			'fields'  => array(
				array( 'key' => 'titel', 'label' => 'Überschrift', 'wide' => true, 'default' => 'Konstrukta in Zahlen' ),
				array( 'key' => 'stil', 'label' => 'Darstellung', 'type' => 'select', 'placement' => 'sidebar', 'default' => 'hell', 'options' => array(
					array( 'value' => 'hell', 'label' => 'Hell' ),
					array( 'value' => 'dunkel', 'label' => 'Dunkel' ),
					array( 'value' => 'verlauf', 'label' => 'Verlauf' ),
				) ),
				array( 'key' => 'flaeche', 'label' => 'Verlauf', 'type' => 'gradient', 'placement' => 'sidebar', 'default' => 'var(--wp--preset--gradient--stahl)', 'help' => 'Nur bei Darstellung «Verlauf».' ),
				array(
					'key'     => 'werte',
					'label'   => 'Zahlen',
					'type'    => 'rows',
					'empty'   => 'Noch keine Zahlen.',
					'add'     => 'Zahl hinzufügen',
					'columns' => array(
						array( 'key' => 'wert', 'label' => 'Wert', 'weight' => 1, 'placeholder' => '250' ),
						array( 'key' => 'einheit', 'label' => 'Einheit', 'weight' => 1, 'placeholder' => '+' ),
						array( 'key' => 'label', 'label' => 'Beschriftung', 'weight' => 3, 'placeholder' => 'Mitarbeitende' ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------
		 * Standorte: Inhaltsblock mit Bildern pro Zeile.
		 * ---------------------------------------------------------------- */
		'standorte' => array(
			'title'   => 'Standorte',
			'storage' => 'attributes',
			'block'   => 'konstrukta/standorte',
			'fields'  => array(
				array( 'key' => 'zeige_bilder', 'label' => 'Bilder zeigen', 'type' => 'toggle', 'placement' => 'sidebar', 'default' => true ),
				array(
					'key'     => 'orte',
					'label'   => 'Standorte',
					'type'    => 'rows',
					'empty'   => 'Noch keine Standorte.',
					'add'     => 'Standort hinzufügen',
					'columns' => array(
						array( 'key' => 'bild', 'label' => 'Bild', 'type' => 'media', 'weight' => 2 ),
						array( 'key' => 'ort', 'label' => 'Ort', 'weight' => 2, 'placeholder' => 'Winterthur' ),
						array( 'key' => 'adresse', 'label' => 'Adresse', 'weight' => 3, 'placeholder' => 'Strasse, PLZ Ort' ),
						array( 'key' => 'telefon', 'label' => 'Telefon', 'weight' => 2, 'placeholder' => '+41 …' ),
					),
				),
			),
		),
	);

	return apply_filters( 'kx_schemas', $schemas );
}

function kx_schema( string $id ): ?array {
	return kx_schemas()[ $id ] ?? null;
}

/** Rückfallwert eines Feldes. */
function kx_field_default( array $field ) {
	if ( array_key_exists( 'default', $field ) ) {
		return $field['default'];
	}
	switch ( $field['type'] ?? 'text' ) {
		case 'rows':
			return array();
		case 'toggle':
			return false;
		case 'number':
		case 'media':
			return 0;
		default:
			return '';
	}
}

/** JSON-Schema eines Feldes – für REST (Meta) und Blockattribute. */
function kx_field_json_schema( array $field ): array {
	switch ( $field['type'] ?? 'text' ) {
		case 'number':
		case 'media':
			return array( 'type' => 'integer' );
		case 'toggle':
			return array( 'type' => 'boolean' );
		case 'rows':
			$props = array();
			foreach ( $field['columns'] as $col ) {
				$props[ $col['key'] ] = array( 'type' => 'string' );
			}
			return array(
				'type'  => 'array',
				'items' => array(
					'type'                 => 'object',
					'properties'           => $props,
					'additionalProperties' => false,
				),
			);
		default:
			return array( 'type' => 'string' );
	}
}

/** Säubert einen Wert nach Feldtyp. Leere Zeilen fallen weg, fremde Spalten auch. */
function kx_sanitize_field( $value, array $field ) {
	switch ( $field['type'] ?? 'text' ) {
		case 'number':
		case 'media':
			return (int) $value;
		case 'toggle':
			return (bool) $value;
		case 'textarea':
			return sanitize_textarea_field( (string) $value );
		case 'url':
			return esc_url_raw( (string) $value );
		case 'email':
			return sanitize_email( (string) $value );
		case 'gradient':
			return preg_replace( '/[^a-zA-Z0-9#%(),.\s\-\/_]/', '', (string) $value );
		case 'select':
			$allowed = wp_list_pluck( $field['options'], 'value' );
			return in_array( $value, $allowed, true ) ? $value : kx_field_default( $field );
		case 'rows':
			$rows = array();
			foreach ( (array) $value as $row ) {
				$clean = array();
				foreach ( $field['columns'] as $col ) {
					$v                     = (string) ( ( (array) $row )[ $col['key'] ] ?? '' );
					$clean[ $col['key'] ] = 'gradient' === ( $col['type'] ?? '' ) ? kx_sanitize_field( $v, $col ) : sanitize_text_field( $v );
				}
				if ( implode( '', $clean ) !== '' ) {
					$rows[] = $clean;
				}
			}
			return $rows;
		default:
			return sanitize_text_field( (string) $value );
	}
}
