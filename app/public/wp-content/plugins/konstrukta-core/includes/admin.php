<?php
/**
 * Admin: DataViews-Übersicht der Referenzprojekte mit DataForm-Schnellbearbeitung,
 * plus ein Dashboard-Widget, das die Demo erklärt.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	$hook = add_submenu_page(
		'edit.php?post_type=kx_projekt',
		'Referenzen',
		'Alle Projekte',
		'edit_posts',
		'kx-projekte',
		fn() => print '<div class="wrap"><h1 class="wp-heading-inline">Referenzen</h1> <a href="' . esc_url( admin_url( 'post-new.php?post_type=kx_projekt' ) ) . '" class="page-title-action">Neues Projekt</a><hr class="wp-header-end"><p class="description">DataViews-Liste mit DataForm-Schnellbearbeitung · <a href="' . esc_url( admin_url( 'edit.php?post_type=kx_projekt&kx_klassisch=1' ) ) . '">Klassische Liste</a> (Papierkorb, Massenaktionen)</p><div id="kx-projekte-root"></div></div>',
		0
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

/**
 * DataViews-Übersicht als Standardansicht für Referenzen:
 * «Alle Projekte» (edit.php) aus dem Menü nehmen und einfache Aufrufe umleiten.
 * Aufrufe mit Parametern (Papierkorb, Massenaktionen, Rückmeldungen) bleiben.
 */
add_action( 'admin_menu', function () {
	remove_submenu_page( 'edit.php?post_type=kx_projekt', 'edit.php?post_type=kx_projekt' );
}, 999 );

add_action( 'load-edit.php', function () {
	if ( 1 === count( $_GET ) && 'kx_projekt' === ( $_GET['post_type'] ?? '' ) ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=kx_projekt&page=kx-projekte' ) );
		exit;
	}
} );

add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'kx_demo', 'Demo: Pflegen im Block-Editor', function () {
		$page = fn( $slug ) => ( $p = get_page_by_path( $slug ) ) ? get_edit_post_link( $p->ID ) : admin_url( 'edit.php?post_type=page' );
		$proj = get_posts( array( 'post_type' => 'kx_projekt', 'numberposts' => 1 ) );
		$rows = array(
			array( 'Schema-Maske, Daten in Post-Meta', $proj ? get_edit_post_link( $proj[0]->ID ) : '#', 'Referenzprojekt: Maske auf der Leinwand, Template gesperrt, Pflichtfelder sperren das Speichern, Felder in Revisionen.' ),
			array( 'Schema-Maske, Daten als Blockattribute', $page( 'unternehmen' ), 'Blöcke «Kennzahlen», «Standorte», «Team»: Maske bei Auswahl, Server-Vorschau sonst.' ),
			array( 'DataForm (@wordpress/dataviews)', $page( 'unternehmen' ), 'Seitenleiste «Seitenkopf & Teaser» mit Validierung.' ),
			array( 'DataViews + DataForm im Admin', admin_url( 'edit.php?post_type=kx_projekt&page=kx-projekte' ), 'Referenzen → Alle Projekte: Liste mit Schnellbearbeitung (Standardansicht).' ),
			array( 'Gesperrte Patterns (contentOnly)', $page( 'leistungen' ), 'Nur Texte und Bilder sind editierbar, Struktur fix.' ),
			array( 'Synced Pattern mit Overrides', $page( 'kontakt' ), '«Kontakt-Box»: Layout zentral, Name/Text pro Seite.' ),
			array( 'Block-Bindings, editierbar', $page( 'unternehmen' ), 'Seitenkopf ↔ Meta kx_claim; Footer ↔ Firmendaten.' ),
			array( 'Team-Block (eigene Maske statt ACF)', $page( 'unternehmen' ), 'Block «Team»: Personen mit Foto, gespeichert als eine Liste im Block.' ),
		);
		echo '<table class="widefat striped"><tbody>';
		foreach ( $rows as $r ) {
			printf( '<tr><td><a href="%s"><strong>%s</strong></a><br><span class="description">%s</span></td></tr>', esc_url( $r[1] ), esc_html( $r[0] ), esc_html( $r[2] ) );
		}
		echo '</tbody></table>';
	} );
} );

/* ---------------------------------------------------------------------
 * Seitenfelder aus dem DataForm im Admin sichtbar machen:
 * Spalten in der Seitenliste und ein Widget «Inhalte zur Prüfung».
 * ------------------------------------------------------------------- */

/** Name der Ansprechperson als mailto-Link. */
function kx_user_link( int $user_id ): string {
	$u = $user_id ? get_userdata( $user_id ) : null;
	return $u ? '<a href="mailto:' . esc_attr( $u->user_email ) . '">' . esc_html( $u->display_name ) . '</a>' : '';
}

/** Prüfstatus: [Text, CSS-Farbe] oder null. */
function kx_pruef_status( string $date ): ?array {
	if ( ! $date ) {
		return null;
	}
	$days = (int) floor( ( strtotime( $date ) - strtotime( 'today' ) ) / DAY_IN_SECONDS );
	if ( $days < 0 ) {
		return array( sprintf( 'überfällig seit %d Tagen', -$days ), '#cc1818' );
	}
	if ( $days <= 30 ) {
		return array( 0 === $days ? 'heute fällig' : sprintf( 'in %d Tagen', $days ), '#b26200' );
	}
	return array( date_i18n( 'j. F Y', strtotime( $date ) ), '#3c434a' );
}

add_filter( 'manage_page_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['kx_rubrik']  = 'Rubrik';
			$new['kx_pruefen'] = 'Prüfen bis';
			$new['kx_kontakt'] = 'Ansprechperson';
			$new['kx_footer']  = 'Footer';
		}
	}
	return $new;
} );

add_action( 'manage_page_posts_custom_column', function ( $col, $post_id ) {
	$d = kx_data( 'seite', $post_id );
	switch ( $col ) {
		case 'kx_rubrik':
			echo esc_html( kx_option_label( 'seite', 'rubrik', $d['rubrik'] ) );
			break;
		case 'kx_pruefen':
			$s = kx_pruef_status( (string) $d['pruefen_bis'] );
			echo $s ? '<span style="color:' . esc_attr( $s[1] ) . ';font-weight:600">' . esc_html( $s[0] ) . '</span>' : '–';
			break;
		case 'kx_kontakt':
			echo kx_user_link( (int) $d['ansprechperson'] ) ?: '–';
			break;
		case 'kx_footer':
			echo $d['im_footer'] ? '✓' : '–';
			break;
	}
}, 10, 2 );

add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'kx_pruefen', 'Inhalte zur Prüfung', function () {
		$pages = get_posts( array(
			'post_type'   => 'page',
			'numberposts' => 20,
			'meta_key'    => 'kx_pruefen_bis',
			'orderby'     => 'meta_value',
			'order'       => 'ASC',
			'meta_query'  => array( array( 'key' => 'kx_pruefen_bis', 'value' => '', 'compare' => '!=' ) ),
		) );
		if ( ! $pages ) {
			echo '<p>Keine Seite hat ein Prüfdatum.</p>';
			return;
		}
		echo '<p class="description">Aus dem DataForm-Feld «Inhalt prüfen bis» jeder Seite.</p><table class="widefat striped"><tbody>';
		foreach ( $pages as $p ) {
			$d = kx_data( 'seite', $p->ID );
			$s = kx_pruef_status( (string) $d['pruefen_bis'] );
			printf(
				'<tr><td><a href="%s"><strong>%s</strong></a>%s</td><td style="text-align:right;color:%s;font-weight:600">%s</td></tr>',
				esc_url( get_edit_post_link( $p->ID ) ),
				esc_html( get_the_title( $p ) ),
				( $link = kx_user_link( (int) $d['ansprechperson'] ) ) ? '<br>' . $link : '',
				esc_attr( $s[1] ),
				esc_html( $s[0] )
			);
		}
		echo '</tbody></table>';
	} );
} );
