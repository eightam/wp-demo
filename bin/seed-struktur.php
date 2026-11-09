<?php
/**
 * Struktur für die Untermenüs: Referenzkategorien und Leistungs-Unterseiten,
 * plus die beiden Dropdowns in der Hauptnavigation.
 *
 *   wp eval-file bin/seed-struktur.php      (wird auch von seed.php geladen)
 *
 * Schonend: legt nur an, was fehlt, und ersetzt in der Navigation nur die
 * einfachen Links «Leistungen» und «Referenzen». Eigene Änderungen bleiben.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

$j = fn( $a ) => wp_json_encode( $a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

/* ---------------------------------------------------------------------
 * 1. Referenzkategorien (= Branchen)
 * ------------------------------------------------------------------- */
$kategorien = array(
	'bahn'      => array( 'Bahn & Verkehr', 'Bahnsteigtüren, Fahrzeugkomponenten und Nachrüstungen im laufenden Betrieb – dort, wo jede Sperrnacht zählt.' ),
	'energie'   => array( 'Energie', 'Wasserkraft, Netze und Speicher: Vorrichtungen, Berechnungen und Revisionen für Anlagen, die Jahrzehnte laufen.' ),
	'industrie' => array( 'Industrie & Anlagenbau', 'Montagelinien, Prüfstände und Sondermaschinen – vom Layout bis zur Inbetriebnahme.' ),
	'medtech'   => array( 'Medizintechnik', 'Geräte für Klinik und Labor: Design, Mechatronik und Dokumentation nach MDR.' ),
	'bau'       => array( 'Bau & Infrastruktur', 'Brücken, Tragwerke und Schwingungsfragen – Ingenieurwissen für den öffentlichen Raum.' ),
);
$term_ids = array();
foreach ( $kategorien as $slug => list( $name, $desc ) ) {
	$t = get_term_by( 'slug', $slug, 'kx_kategorie' );
	if ( ! $t ) {
		$r = wp_insert_term( $name, 'kx_kategorie', array( 'slug' => $slug, 'description' => $desc ) );
		$term_ids[ $slug ] = is_wp_error( $r ) ? 0 : $r['term_id'];
	} else {
		$term_ids[ $slug ] = $t->term_id;
	}
}
// Projekte zuordnen (aus kx_branche).
foreach ( get_posts( array( 'post_type' => 'kx_projekt', 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
	$b = get_post_meta( $p->ID, 'kx_branche', true );
	if ( $b ) {
		wp_set_object_terms( $p->ID, array( $b ), 'kx_kategorie' );
	}
}
WP_CLI::log( 'Referenzkategorien: ' . count( $term_ids ) );

/* ---------------------------------------------------------------------
 * 2. Leistungs-Unterseiten
 * ------------------------------------------------------------------- */
$parent = get_page_by_path( 'leistungen' );
$lea    = get_user_by( 'login', 'lea.gaemperli' );
$matteo = get_user_by( 'login', 'matteo.rossi' );
$reg    = WP_Block_Patterns_Registry::get_instance();
$pat    = fn( $slug, $r = array() ) => strtr( $reg->get_registered( $slug )['content'], $r ) . "\n\n";
$img    = get_stylesheet_directory_uri() . '/assets/img/';

$leistungen = array(
	'engineering' => array(
		'title'  => 'Engineering & Konstruktion',
		'claim'  => 'Konstruktionen, die sich bauen lassen',
		'teaser' => 'Mechanik, Elektrotechnik und Software aus einer Hand – mit Blick auf Fertigung, Montage und Service.',
		'bild'   => 'blaupause',
		'h2'     => 'Vom Lastenheft zur fertigungsreifen Baugruppe',
		'text'   => 'Wir konstruieren in CATIA, Creo und SolidWorks, rechnen nach, was nötig ist, und sprechen früh mit der Werkstatt. So entstehen Baugruppen, die beim ersten Prototyp passen.',
		'person' => $lea,
	),
	'simulation' => array(
		'title'  => 'Simulation & Berechnung',
		'claim'  => 'Wissen, bevor gefräst wird',
		'teaser' => 'FEM, CFD und Mehrkörpersimulation: Wir finden Schwachstellen, bevor das erste Bauteil existiert.',
		'bild'   => 'simulation',
		'h2'     => 'Nachweise, die vor Behörden bestehen',
		'text'   => 'Festigkeit, Ermüdung, Strömung, Crash: Wir simulieren nach Norm, dokumentieren nachvollziehbar und validieren mit Messungen im eigenen Labor.',
		'person' => $matteo,
	),
	'industriedesign' => array(
		'title'  => 'Industriedesign',
		'claim'  => 'Technik, die man gern bedient',
		'teaser' => 'Form, Ergonomie und Bedienung – vom Moodboard zum Prototyp in wenigen Wochen.',
		'bild'   => 'design',
		'h2'     => 'Design mit Ingenieurverstand',
		'text'   => 'Unser Designteam sitzt neben der Konstruktion. Darum sind unsere Entwürfe nicht nur schön, sondern auch herstellbar, reinigbar und wartungsfreundlich.',
		'person' => $lea,
	),
);
$child_ids = array();
$order     = 1;
foreach ( $leistungen as $slug => $l ) {
	$existing = get_page_by_path( 'leistungen/' . $slug );
	if ( $existing ) {
		$child_ids[ $slug ] = $existing->ID;
		$order++;
		continue;
	}
	$content = $pat( 'konstrukta/seitenkopf' )
		. $pat( 'konstrukta/text-bild', array(
			'villa.jpg'                                   => $l['bild'] . '.jpg',
			'>Unternehmen<'                               => '>' . esc_html( $l['title'] ) . '<',
			'Ein Haus mit Geschichte, ein Team mit Zukunft' => esc_html( $l['h2'] ),
			'Seit 2009 arbeiten wir in der Villa Seeblick in Romanshorn. Das Gebäude von 1924 ist heute Werkstatt, Labor und Treffpunkt – hier entstehen Ideen, die später in Zügen, Kraftwerken und Operationssälen ihren Dienst tun.' => esc_html( $l['text'] ),
			'href="/unternehmen/">Über uns'               => 'href="/referenzen/">Referenzen ansehen',
		) )
		. $pat( 'konstrukta/prozess' )
		. $pat( 'konstrukta/cta-band' );
	$child_ids[ $slug ] = wp_insert_post( wp_slash( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_parent'  => $parent->ID,
		'post_name'    => $slug,
		'post_title'   => $l['title'],
		'menu_order'   => $order++,
		'post_content' => $content,
		'meta_input'   => array(
			'kx_claim'          => $l['claim'],
			'kx_teaser'         => $l['teaser'],
			'kx_rubrik'         => 'leistungen',
			'kx_cta_url'        => '/kontakt/',
			'kx_ansprechperson' => $l['person'] ? $l['person']->ID : 0,
		),
	) ) );
}
WP_CLI::log( 'Leistungs-Unterseiten: ' . implode( ', ', array_keys( $child_ids ) ) );

// «Mehr erfahren» der Leistungskarten auf die Unterseiten zeigen lassen.
foreach ( array( 'startseite', 'leistungen' ) as $slug ) {
	$p = get_page_by_path( $slug );
	if ( ! $p ) {
		continue;
	}
	$c = $p->post_content;
	foreach ( array( 'engineering', 'simulation', 'industriedesign' ) as $child ) {
		$pos = strpos( $c, '<a href="/leistungen/">Mehr erfahren' );
		if ( false !== $pos ) {
			$c = substr_replace( $c, '<a href="/leistungen/' . $child . '/">Mehr erfahren', $pos, strlen( '<a href="/leistungen/">Mehr erfahren' ) );
		}
	}
	if ( $c !== $p->post_content ) {
		wp_update_post( array( 'ID' => $p->ID, 'post_content' => $c ) );
	}
}

/* ---------------------------------------------------------------------
 * 3. Dropdowns in der Hauptnavigation
 * ------------------------------------------------------------------- */
$nav = get_page_by_path( 'hauptnavigation', OBJECT, 'wp_navigation' );
if ( $nav ) {
	$c = $nav->post_content;

	$link = fn( $label, $url, $attrs = array() ) => '<!-- wp:navigation-link ' . $j( array_merge( array( 'label' => $label, 'url' => $url ), $attrs ) ) . ' /-->';

	// Leistungen
	$items = $link( 'Alle Leistungen', '/leistungen/', array( 'type' => 'page', 'id' => $parent->ID, 'kind' => 'post-type' ) );
	foreach ( $child_ids as $slug => $id ) {
		$items .= $link( esc_html( get_post_field( 'post_title', $id ) ), '/leistungen/' . $slug . '/', array( 'type' => 'page', 'id' => $id, 'kind' => 'post-type' ) );
	}
	$c = preg_replace(
		'#<!-- wp:navigation-link (\{[^}]*"label":"Leistungen"[^}]*\}) /-->#',
		'<!-- wp:navigation-submenu $1 -->' . $items . '<!-- /wp:navigation-submenu -->',
		$c, 1
	);

	// Referenzen
	$items = $link( 'Alle Referenzen', '/referenzen/', array( 'kind' => 'custom', 'type' => 'custom' ) );
	foreach ( $term_ids as $slug => $id ) {
		$items .= $link( get_term( $id )->name, wp_make_link_relative( get_term_link( $id ) ), array( 'type' => 'kx_kategorie', 'id' => $id, 'kind' => 'taxonomy' ) );
	}
	$c = preg_replace(
		'#<!-- wp:navigation-link (\{[^}]*"label":"Referenzen"[^}]*\}) /-->#',
		'<!-- wp:navigation-submenu $1 -->' . $items . '<!-- /wp:navigation-submenu -->',
		$c, 1
	);

	if ( $c !== $nav->post_content ) {
		wp_update_post( wp_slash( array( 'ID' => $nav->ID, 'post_content' => $c ) ) );
		WP_CLI::log( 'Navigation: Dropdowns eingebaut.' );
	} else {
		WP_CLI::log( 'Navigation: schon eingebaut.' );
	}
}
flush_rewrite_rules();
