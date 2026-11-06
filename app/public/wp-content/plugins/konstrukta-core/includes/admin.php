<?php
/**
 * Admin: DataViews-Übersicht der Referenzprojekte mit DataForm-Schnellbearbeitung,
 * plus ein Dashboard-Widget, das die Demo erklärt.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	$hook = add_submenu_page(
		'edit.php?post_type=kx_projekt',
		'Projekt-Übersicht (DataViews)',
		'Übersicht (DataViews)',
		'edit_posts',
		'kx-projekte',
		fn() => print '<div class="wrap"><h1 class="wp-heading-inline">Projekt-Übersicht</h1><p>DataViews-Liste mit DataForm-Schnellbearbeitung. Dasselbe PHP-Schema wie die Maske im Editor.</p><div id="kx-projekte-root"></div></div>'
	);
	add_action( "admin_enqueue_scripts", function ( $current ) use ( $hook ) {
		if ( $current !== $hook || ! file_exists( KX_DIR . '/build/projekte-admin.asset.php' ) ) {
			return;
		}
		$asset = require KX_DIR . '/build/projekte-admin.asset.php';
		wp_enqueue_media();
		wp_enqueue_script( 'kx-projekte-admin', KX_URL . 'build/projekte-admin.js', array_merge( $asset['dependencies'], array( 'kx-schema-form' ) ), $asset['version'], true );
		wp_add_inline_script( 'kx-schema-form', 'window.kxEditor = ' . wp_json_encode( kx_editor_data() ) . ';', 'before' );
		wp_enqueue_style( 'kx-dataviews', KX_URL . 'build/dataviews.css', array( 'wp-components' ), $asset['version'] );
		wp_enqueue_style( 'kx-projekte-admin', KX_URL . 'build/projekte-admin.css', array( 'kx-dataviews', 'kx-schema-form' ), $asset['version'] );
	} );
} );

add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'kx_demo', 'Demo: Pflegen im Block-Editor', function () {
		$page = fn( $slug ) => ( $p = get_page_by_path( $slug ) ) ? get_edit_post_link( $p->ID ) : admin_url( 'edit.php?post_type=page' );
		$proj = get_posts( array( 'post_type' => 'kx_projekt', 'numberposts' => 1 ) );
		$rows = array(
			array( 'Schema-Maske, Daten in Post-Meta', $proj ? get_edit_post_link( $proj[0]->ID ) : '#', 'Referenzprojekt: Maske auf der Leinwand, Template gesperrt, Pflichtfelder sperren das Speichern, Felder in Revisionen.' ),
			array( 'Schema-Maske, Daten als Blockattribute', $page( 'unternehmen' ), 'Blöcke «Kennzahlen» und «Standorte»: Maske bei Auswahl, Server-Vorschau sonst.' ),
			array( 'DataForm (@wordpress/dataviews)', $page( 'unternehmen' ), 'Seitenleiste «Seitenkopf & Teaser» mit Validierung.' ),
			array( 'DataViews + DataForm im Admin', admin_url( 'edit.php?post_type=kx_projekt&page=kx-projekte' ), 'Projektliste mit Schnellbearbeitung.' ),
			array( 'Gesperrte Patterns (contentOnly)', $page( 'leistungen' ), 'Nur Texte und Bilder sind editierbar, Struktur fix.' ),
			array( 'Synced Pattern mit Overrides', $page( 'kontakt' ), '«Kontakt-Box»: Layout zentral, Name/Text pro Seite.' ),
			array( 'Block-Bindings, editierbar', $page( 'unternehmen' ), 'Seitenkopf ↔ Meta kx_claim; Footer ↔ Firmendaten.' ),
			array( 'ACF Pro-Block (Vergleich)', $page( 'unternehmen' ), 'Block «Team (ACF)» mit Repeater.' ),
		);
		echo '<table class="widefat striped"><tbody>';
		foreach ( $rows as $r ) {
			printf( '<tr><td><a href="%s"><strong>%s</strong></a><br><span class="description">%s</span></td></tr>', esc_url( $r[1] ), esc_html( $r[0] ), esc_html( $r[2] ) );
		}
		echo '</tbody></table>';
	} );
} );
