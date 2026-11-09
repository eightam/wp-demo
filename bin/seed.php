<?php
/**
 * Demo-Inhalte anlegen. Mehrfach ausführbar (sucht vorhandene Einträge per Slug).
 *
 *   wp eval-file bin/seed.php
 *
 * Alle Inhalte sind erfunden.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

/* ---------------------------------------------------------------------
 * Grundeinstellungen
 * ------------------------------------------------------------------- */
if ( ! function_exists( 'kx_schemas' ) || 'konstrukta' !== get_stylesheet() ) {
	WP_CLI::error( 'Erst Theme «konstrukta» und Plugin «konstrukta-core» aktivieren (bin/setup.sh).' );
}

update_option( 'blogname', 'Konstrukta' );
update_option( 'blogdescription', 'Engineering-Partner für anspruchsvolle Entwicklungsprojekte' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'date_format', 'j. F Y' );
update_option( 'kx_firma', array(
	'name'    => 'Konstrukta Engineering AG',
	'strasse' => 'Seestrasse 41',
	'ort'     => '8590 Romanshorn',
	'telefon' => '+41 71 466 20 00',
	'email'   => 'info@konstrukta.example',
	'zeiten'  => 'Mo–Fr, 7.30–17.30 Uhr',
) );

// Beispielinhalte von WordPress nur zurückstellen, nicht löschen.
foreach ( array( 'hallo-welt', 'beispielseite' ) as $slug ) {
	foreach ( get_posts( array( 'name' => $slug, 'post_type' => array( 'post', 'page' ), 'post_status' => 'any' ) ) as $p ) {
		wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'draft' ) );
	}
}

/* ---------------------------------------------------------------------
 * Hilfsfunktionen
 * ------------------------------------------------------------------- */
function kx_seed_image( string $name ): int {
	$found = get_posts( array( 'post_type' => 'attachment', 'meta_key' => '_kx_seed', 'meta_value' => $name, 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( $found ) {
		return (int) $found[0];
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$src = get_stylesheet_directory() . "/assets/img/$name.jpg";
	$tmp = wp_tempnam( $name );
	copy( $src, $tmp );
	$id = media_handle_sideload( array( 'name' => "kx-$name.jpg", 'tmp_name' => $tmp ), 0, ucfirst( str_replace( '-', ' ', $name ) ) );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_kx_seed', $name );
	return (int) $id;
}

function kx_seed_post( array $args ): int {
	$existing = get_page_by_path( $args['post_name'], OBJECT, $args['post_type'] );
	$args     = wp_parse_args( $args, array( 'post_status' => 'publish', 'post_author' => 1 ) );
	if ( $existing ) {
		$args['ID'] = $existing->ID;
	}
	$meta = $args['meta'] ?? array();
	unset( $args['meta'] );
	$id = wp_insert_post( wp_slash( $args ), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	return $id;
}

/** Inhalt eines registrierten Patterns, optional mit Textersetzungen. */
function kx_pattern( string $slug, array $replace = array() ): string {
	$p = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	if ( ! $p ) {
		WP_CLI::error( "Pattern $slug fehlt." );
	}
	return strtr( $p['content'], $replace ) . "\n\n";
}

/** Abschnitt mit eingeschränkter Breite. */
function kx_section( string $inner, string $bg = '' ): string {
	$attrs = array(
		'align'  => 'full',
		'style'  => array( 'spacing' => array( 'padding' => array( 'top' => 'var:preset|spacing|60', 'bottom' => 'var:preset|spacing|60', 'left' => 'var:preset|spacing|50', 'right' => 'var:preset|spacing|50' ) ) ),
		'layout' => array( 'type' => 'constrained', 'contentSize' => '1240px' ),
	);
	$class = 'wp-block-group alignfull';
	if ( $bg ) {
		$attrs['backgroundColor'] = $bg;
		$class               .= " has-$bg-background-color has-background";
	}
	return '<!-- wp:group ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' --><div class="' . $class . '" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">' . $inner . '</div><!-- /wp:group -->' . "\n\n";
}

function kx_heading( string $text, string $eyebrow = '' ): string {
	$out = '';
	if ( $eyebrow ) {
		$out .= '<!-- wp:paragraph {"align":"wide","className":"kx-eyebrow"} --><p class="alignwide kx-eyebrow">' . esc_html( $eyebrow ) . '</p><!-- /wp:paragraph -->';
	}
	return $out . '<!-- wp:heading {"align":"wide"} --><h2 class="wp-block-heading alignwide">' . esc_html( $text ) . '</h2><!-- /wp:heading -->';
}

function kx_block( string $name, array $attrs = array() ): string {
	return '<!-- wp:' . $name . ( $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : '' ) . ' /-->';
}

function kx_p( string $text ): string {
	return '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
}

/* ---------------------------------------------------------------------
 * Bilder
 * ------------------------------------------------------------------- */
$img = array();
foreach ( array( 'blaupause', 'bahnhof', 'turbine', 'werkstatt', 'simulation', 'design', 'villa', 'bruecke', 'roboter', 'tueren', 'team-1', 'team-2', 'team-3', 'team-4' ) as $n ) {
	$img[ $n ] = kx_seed_image( $n );
}
WP_CLI::log( 'Bilder: ' . count( $img ) );

/* ---------------------------------------------------------------------
 * Referenzprojekte (Daten in Post-Meta)
 * ------------------------------------------------------------------- */
$projekte = array(
	array(
		'slug' => 'bahnsteigtueren-metro', 'title' => 'Bahnsteigtüren für einen bestehenden Metro-Bahnhof', 'order' => 1,
		'kunde' => 'Metro Lissabon', 'branche' => 'bahn', 'jahr' => 2025, 'standort' => 'Lissabon, Portugal', 'ansicht' => 'tueren', 'stimmung' => 'var(--wp--preset--gradient--stahl)', 'hervorheben' => true,
		'kurz' => 'Enge Platzverhältnisse, laufender Betrieb, keine Sperrnacht zu viel: Wir haben ein modulares Bahnsteigtür-System entwickelt, das sich in bestehende Stationen nachrüsten lässt.',
		'kennzahlen' => array( array( '42', 'm', 'Bahnsteiglänge pro Modul' ), array( '6', 'Mt.', 'von der Idee zum Prototyp' ), array( '0', '', 'zusätzliche Sperrnächte' ) ),
		'leistungen' => array( array( 'Konzept & Machbarkeit', 'Vermessung der Stationen, Variantenstudie, Kostenschätzung.' ), array( 'Konstruktion', 'Türmodule, Unterkonstruktion, Montagevorrichtungen.' ), array( 'Simulation', 'Windlast bei Zugeinfahrt, Crashlasten nach EN 14752.' ) ),
		'galerie' => array( array( 'bahnhof', 'Visualisierung der Station im Endzustand', '' ), array( 'blaupause', 'Montagekonzept Türmodul', 'var(--wp--preset--gradient--stahl)' ) ),
	),
	array(
		'slug' => 'pumpturbine-grimsel', 'title' => 'Revision einer Pumpturbine im Hochgebirge', 'order' => 2,
		'kunde' => 'Kraftwerke Oberhasli (fiktiv)', 'branche' => 'energie', 'jahr' => 2024, 'standort' => 'Guttannen, Schweiz', 'ansicht' => 'turbine', 'stimmung' => 'var(--wp--preset--gradient--gletscher)', 'hervorheben' => true,
		'kurz' => 'Für die Revision einer 140-MW-Pumpturbine haben wir Hebe- und Drehvorrichtungen konstruiert, die in einer Kaverne mit 3 m Restfreiheit funktionieren.',
		'kennzahlen' => array( array( '140', 'MW', 'Leistung der Maschine' ), array( '86', 't', 'Gewicht des Laufrads' ), array( '3', 'm', 'Freiraum in der Kaverne' ) ),
		'leistungen' => array( array( 'Vorrichtungsbau', 'Hebe- und Drehvorrichtung für das Laufrad.' ), array( 'FEM-Berechnung', 'Nachweis aller Lastfälle inkl. Erdbeben.' ) ),
		'galerie' => array( array( 'turbine', 'Laufrad in der Drehvorrichtung', '' ) ),
	),
	array(
		'slug' => 'chirurgie-roboterarm', 'title' => 'Leichter Roboterarm für die Chirurgie', 'order' => 3,
		'kunde' => 'Medica Systems AG', 'branche' => 'medtech', 'jahr' => 2026, 'standort' => 'Zug, Schweiz', 'ansicht' => 'roboter', 'stimmung' => 'var(--wp--preset--gradient--abend)', 'hervorheben' => true,
		'kurz' => 'Sieben Achsen, unter neun Kilogramm, sterilisierbar: Industriedesign, Mechanik und Zulassungsdokumentation aus einer Hand.',
		'kennzahlen' => array( array( '7', '', 'Achsen' ), array( '8.6', 'kg', 'Gesamtgewicht' ), array( '0.1', 'mm', 'Wiederholgenauigkeit' ) ),
		'leistungen' => array( array( 'Industriedesign', 'Ergonomie für das OP-Team, Reinigbarkeit.' ), array( 'Mechatronik', 'Antriebe, Bremsen, Kabelführung.' ), array( 'Regulatorik', 'Technische Dokumentation nach MDR.' ) ),
		'galerie' => array( array( 'roboter', 'Kinematikstudie', '' ), array( 'design', 'Designvarianten Gehäuse', '' ) ),
	),
	array(
		'slug' => 'fussgaengerbruecke-rhein', 'title' => 'Fussgängerbrücke mit Schwingungsdämpfern', 'order' => 4,
		'kunde' => 'Stadt Basel (fiktiv)', 'branche' => 'bau', 'jahr' => 2023, 'standort' => 'Basel, Schweiz', 'ansicht' => 'bruecke', 'stimmung' => 'var(--wp--preset--gradient--signal)', 'hervorheben' => false,
		'kurz' => 'Eine schlanke Brücke schwingt, wenn viele Menschen im Gleichschritt gehen. Wir haben die Dämpfer ausgelegt, die das verhindern.',
		'kennzahlen' => array( array( '96', 'm', 'Spannweite' ), array( '4', '', 'Schwingungstilger' ) ),
		'leistungen' => array( array( 'Dynamische Analyse', 'Eigenfrequenzen, Personeninduzierte Schwingungen.' ), array( 'Tilger-Auslegung', 'Dimensionierung und Abnahmemessung.' ) ),
		'galerie' => array(),
	),
	array(
		'slug' => 'montagelinie-getriebe', 'title' => 'Halbautomatische Montagelinie für Getriebe', 'order' => 5,
		'kunde' => 'Ostschweizer Antriebstechnik', 'branche' => 'industrie', 'jahr' => 2022, 'standort' => 'Arbon, Schweiz', 'ansicht' => 'werkstatt', 'stimmung' => 'var(--wp--preset--gradient--stahl)', 'hervorheben' => false,
		'kurz' => 'Acht Stationen, zwei Mitarbeitende, ein Getriebe alle 90 Sekunden – inklusive Prüfstand am Ende der Linie.',
		'kennzahlen' => array( array( '90', 's', 'Taktzeit' ), array( '8', '', 'Stationen' ) ),
		'leistungen' => array( array( 'Anlagenkonzept', 'Layout, Taktzeitanalyse, Ergonomie.' ) ),
		'galerie' => array( array( 'werkstatt', 'Station 4: Lagerpressung', '' ) ),
	),
);

foreach ( $projekte as $p ) {
	$id = kx_seed_post( array(
		'post_type'    => 'kx_projekt',
		'post_name'    => $p['slug'],
		'post_title'   => $p['title'],
		'menu_order'   => $p['order'],
		'post_excerpt' => $p['kurz'],
		'post_content' => '<!-- wp:konstrukta/projekt-daten /-->',
		'meta'         => array(
			'kx_kunde'         => $p['kunde'],
			'kx_branche'       => $p['branche'],
			'kx_jahr'          => $p['jahr'],
			'kx_standort'      => $p['standort'],
			'kx_ansicht'       => $img[ $p['ansicht'] ],
			'kx_stimmung'      => $p['stimmung'],
			'kx_kurzbeschrieb' => $p['kurz'],
			'kx_hervorheben'   => $p['hervorheben'],
			'kx_kennzahlen'    => array_map( fn( $r ) => array( 'wert' => $r[0], 'einheit' => $r[1], 'label' => $r[2] ), $p['kennzahlen'] ),
			'kx_leistungen'    => array_map( fn( $r ) => array( 'titel' => $r[0], 'text' => $r[1] ), $p['leistungen'] ),
			'kx_galerie'       => array_map( fn( $r ) => array( 'bild' => (string) $img[ $r[0] ], 'legende' => $r[1], 'flaeche' => $r[2] ), $p['galerie'] ),
		),
	) );
	set_post_thumbnail( $id, $img[ $p['ansicht'] ] );
}
WP_CLI::log( 'Projekte: ' . count( $projekte ) );

/* ---------------------------------------------------------------------
 * News
 * ------------------------------------------------------------------- */
$news = array(
	array( 'komplexe-konzepte-verstaendlich', 'Komplexe Konzepte verständlich machen', 'tueren', '2026-10-21', 'Wie erklärt man einem Verwaltungsrat, warum eine Bahnsteigtür 40 Zentimeter tiefer montiert werden muss? Mit Visualisierungen, die man nicht erklären muss.' ),
	array( 'creative-meeting-industriedesign', 'Creative Meeting: Industriedesign in der Praxis', 'design', '2026-10-07', 'Vom ersten Strich zur überzeugenden Visualisierung in einem Nachmittag: Unser Designteam zeigte, wie Skizze, CAD und Bildgenerierung zusammenspielen.' ),
	array( 'hundert-jahre-villa-seeblick', '100 Jahre Villa Seeblick', 'villa', '2026-09-12', 'Als wir die Villa Seeblick 2009 übernommen haben, war klar: Dieses Haus ist mehr als ein Firmensitz. Ein Rückblick zum Jubiläum.' ),
);
foreach ( $news as $n ) {
	$id = kx_seed_post( array(
		'post_type'     => 'post',
		'post_name'     => $n[0],
		'post_title'    => $n[1],
		'post_date'     => $n[3] . ' 09:00:00',
		'post_excerpt'  => $n[4],
		'post_content'  => kx_p( $n[4] ) . kx_p( 'Dieser Beitrag ist ein Platzhalter für die Demo. Er zeigt, wie News im Pattern «Neuigkeiten» erscheinen, das nur die Überschrift freigibt.' ),
	) );
	set_post_thumbnail( $id, $img[ $n[2] ] );
}

/* ---------------------------------------------------------------------
 * Synced Pattern mit Overrides: «Kontakt-Box»
 * Layout zentral, Name/Funktion/Text/Bild pro Einsatz überschreibbar.
 * ------------------------------------------------------------------- */
$ov = fn( $name ) => array( 'name' => $name, 'bindings' => array( '__default' => array( 'source' => 'core/pattern-overrides' ) ) );
$j  = fn( $a ) => wp_json_encode( $a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
$box = '<!-- wp:group {"metadata":{"name":"Kontakt-Box"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"left":{"color":"var:preset|color|accent-1","width":"4px"}}},"backgroundColor":"accent-5","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group has-accent-5-background-color has-background" style="border-left-color:var(--wp--preset--color--accent-1);border-left-width:4px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
<!-- wp:columns {"verticalAlignment":"center","align":"wide"} --><div class="wp-block-columns alignwide are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center","width":"160px"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:160px">
<!-- wp:image ' . $j( array( 'id' => $img['team-2'], 'sizeSlug' => 'thumbnail', 'linkDestination' => 'none', 'metadata' => $ov( 'Foto' ) ) ) . ' --><figure class="wp-block-image size-thumbnail"><img src="' . esc_url( wp_get_attachment_image_url( $img['team-2'], 'thumbnail' ) ) . '" alt="" class="wp-image-' . $img['team-2'] . '"/></figure><!-- /wp:image -->
</div><!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} --><div class="wp-block-column is-vertically-aligned-center">
<!-- wp:paragraph {"className":"kx-eyebrow"} --><p class="kx-eyebrow">Ihre Ansprechperson</p><!-- /wp:paragraph -->
<!-- wp:heading ' . $j( array( 'level' => 3, 'metadata' => $ov( 'Name' ) ) ) . ' --><h3 class="wp-block-heading">Lea Gämperli</h3><!-- /wp:heading -->
<!-- wp:paragraph ' . $j( array( 'metadata' => $ov( 'Funktion' ), 'textColor' => 'accent-4' ) ) . ' --><p class="has-accent-4-color has-text-color">Leiterin Business Development</p><!-- /wp:paragraph -->
<!-- wp:paragraph ' . $j( array( 'metadata' => $ov( 'Text' ) ) ) . ' --><p>Erzählen Sie uns von Ihrem Vorhaben – wir melden uns innert zwei Arbeitstagen.</p><!-- /wp:paragraph -->
</div><!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center","width":"240px"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:240px">
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button ' . $j( array( 'metadata' => $ov( 'Schaltfläche' ) ) ) . ' --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="mailto:info@konstrukta.example">E-Mail schreiben</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:column -->
</div><!-- /wp:columns -->
</div><!-- /wp:group -->';
$box_id = kx_seed_post( array( 'post_type' => 'wp_block', 'post_name' => 'kontakt-box', 'post_title' => 'Kontakt-Box', 'post_content' => $box, 'meta' => array( 'wp_pattern_sync_status' => '' ) ) );
$box_ref = fn( array $content = array() ) => kx_section( '<!-- wp:block ' . $j( array_filter( array( 'ref' => $box_id, 'content' => $content ) ) ) . ' /-->' );

/* ---------------------------------------------------------------------
 * Seiten
 * ------------------------------------------------------------------- */
$kennzahlen = fn( array $extra = array() ) => kx_block( 'konstrukta/kennzahlen', array_merge( array(
	'titel' => 'Konstrukta in Zahlen',
	'stil'  => 'hell',
	'align' => 'full',
	'werte' => array(
		array( 'wert' => '240', 'einheit' => '', 'label' => 'Ingenieurinnen und Ingenieure' ),
		array( 'wert' => '5', 'einheit' => '', 'label' => 'Standorte in der Schweiz und Deutschland' ),
		array( 'wert' => '1200', 'einheit' => '+', 'label' => 'abgeschlossene Projekte' ),
		array( 'wert' => '1999', 'einheit' => '', 'label' => 'Gründungsjahr' ),
	),
), $extra ) );

$standorte = kx_block( 'konstrukta/standorte', array(
	'align'        => 'wide',
	'zeige_bilder' => true,
	'orte'         => array(
		array( 'bild' => (string) $img['villa'], 'ort' => 'Romanshorn (Hauptsitz)', 'adresse' => 'Seestrasse 41, 8590 Romanshorn', 'telefon' => '+41 71 466 20 00' ),
		array( 'bild' => (string) $img['werkstatt'], 'ort' => 'Winterthur', 'adresse' => 'Zürcherstrasse 212, 8406 Winterthur', 'telefon' => '+41 52 260 44 10' ),
		array( 'bild' => (string) $img['simulation'], 'ort' => 'Bern', 'adresse' => 'Effingerstrasse 9, 3008 Bern', 'telefon' => '+41 31 382 71 00' ),
		array( 'bild' => (string) $img['blaupause'], 'ort' => 'Friedrichshafen', 'adresse' => 'Allmannsweilerstrasse 4, 88046 Friedrichshafen', 'telefon' => '+49 7541 38 99 0' ),
	),
) );

// ACF-Block: ACF legt Repeater-Zellen als team_0_name … im Blockkommentar ab.
$team = array(
	array( 'Reto Schällibaum', 'CEO, Gründer', 'team-1', 'reto.schaellibaum@konstrukta.example' ),
	array( 'Lea Gämperli', 'Business Development', 'team-2', 'lea.gaemperli@konstrukta.example' ),
	array( 'Matteo Rossi', 'Leiter Simulation', 'team-3', 'matteo.rossi@konstrukta.example' ),
	array( 'Sandra Keller', 'Leiterin Industriedesign', 'team-4', 'sandra.keller@konstrukta.example' ),
);
$acf = array( 'team' => count( $team ), '_team' => 'field_kx_team' );
foreach ( $team as $i => $t ) {
	foreach ( array( 'name' => $t[0], 'funktion' => $t[1], 'foto' => $img[ $t[2] ], 'email' => $t[3] ) as $k => $v ) {
		$acf[ "team_{$i}_{$k}" ]  = $v;
		$acf[ "_team_{$i}_{$k}" ] = "field_kx_team_$k";
	}
}
$team_block = kx_block( 'konstrukta/team-acf', array( 'name' => 'konstrukta/team-acf', 'data' => $acf, 'mode' => 'preview', 'align' => 'wide' ) );

$firma_bind = fn( $key, $tag = 'p', $style = '' ) => '<!-- wp:paragraph ' . $j( array( 'metadata' => array( 'bindings' => array( 'content' => array( 'source' => 'konstrukta/firma', 'args' => array( 'key' => $key ) ) ), 'name' => 'Firmendaten: ' . kx_firma_fields()[ $key ] ) ) ) . ' --><p>' . esc_html( kx_firma( $key ) ) . '</p><!-- /wp:paragraph -->';

// Ansprechpersonen als echte Benutzer (Rolle Redakteur, ohne bekanntes Passwort).
$people = array();
foreach ( array( 'lea' => array( 'lea.gaemperli', 'Lea Gämperli' ), 'matteo' => array( 'matteo.rossi', 'Matteo Rossi' ) ) as $k => $u ) {
	$existing = get_user_by( 'login', $u[0] );
	$people[ $k ] = $existing ? $existing->ID : wp_insert_user( array( 'user_login' => $u[0], 'user_pass' => wp_generate_password( 24 ), 'user_email' => $u[0] . '@konstrukta.example', 'display_name' => $u[1], 'role' => 'editor' ) );
}

$pages = array(
	'startseite' => array(
		'title'   => 'Startseite',
		'order'   => 0,
		'content' => kx_pattern( 'konstrukta/hero' )
			. $kennzahlen()
			. kx_pattern( 'konstrukta/leistungen-raster' )
			. kx_section( kx_heading( 'Ausgewählte Projekte', 'Referenzen' ) . kx_block( 'konstrukta/projekt-karten', array( 'anzahl' => 3, 'nurHervorgehoben' => true, 'align' => 'wide' ) ) )
			. kx_pattern( 'konstrukta/zitat' )
			. kx_pattern( 'konstrukta/news' )
			. kx_pattern( 'konstrukta/cta-band' ),
		'meta'    => array( 'kx_claim' => 'Engineering-Partner für anspruchsvolle Projekte', 'kx_teaser' => 'Engineering, Design und Simulation aus einer Hand.', 'kx_rubrik' => 'unternehmen' ),
	),
	'unternehmen' => array(
		'title'   => 'Unternehmen',
		'order'   => 1,
		'content' => kx_pattern( 'konstrukta/seitenkopf' )
			. kx_pattern( 'konstrukta/text-bild' )
			. $kennzahlen( array( 'stil' => 'verlauf', 'flaeche' => 'var(--wp--preset--gradient--stahl)', 'titel' => 'Seit 1999' ) )
			. kx_section( kx_heading( 'Geschäftsleitung', 'Team' ) . $team_block )
			. kx_section( kx_heading( 'Unsere Standorte' ) . $standorte, 'accent-5' )
			. kx_pattern( 'konstrukta/cta-band' ),
		'meta'    => array( 'kx_claim' => 'Ingenieurskunst mit Bodenhaftung', 'kx_teaser' => 'Wir sind 240 Fachleute aus Maschinenbau, Elektrotechnik, Informatik und Design – und wir mögen schwierige Aufgaben.', 'kx_rubrik' => 'unternehmen', 'kx_ansprechperson' => $people['lea'], 'kx_pruefen_bis' => '2027-03-31', 'kx_im_footer' => true ),
	),
	'leistungen' => array(
		'title'   => 'Leistungen',
		'order'   => 2,
		'content' => kx_pattern( 'konstrukta/seitenkopf' )
			. kx_pattern( 'konstrukta/leistungen-raster', array( 'Vom ersten Strich bis zur Serie' => 'Drei Disziplinen, ein Team', 'Ihr Mehrwert' => 'Kompetenzen' ) )
			. kx_pattern( 'konstrukta/prozess' )
			. kx_pattern( 'konstrukta/zitat' )
			. $box_ref( array( 'Name' => array( 'content' => 'Matteo Rossi' ), 'Funktion' => array( 'content' => 'Leiter Simulation' ), 'Text' => array( 'content' => 'Fragen zu FEM, CFD oder Mehrkörpersimulation? Ich nehme mir gern eine Stunde Zeit.' ), 'Foto' => array( 'id' => $img['team-3'], 'url' => wp_get_attachment_image_url( $img['team-3'], 'thumbnail' ) ) ) ),
		'meta'    => array( 'kx_claim' => 'Was wir für Sie tun', 'kx_teaser' => 'Engineering, Simulation und Industriedesign – einzeln oder als Gesamtpaket mit einer Ansprechperson.', 'kx_rubrik' => 'leistungen', 'kx_cta_url' => '/kontakt/', 'kx_ansprechperson' => $people['matteo'], 'kx_pruefen_bis' => gmdate( 'Y-m-d', strtotime( '+11 days' ) ), 'kx_im_footer' => true ),
	),
	'kontakt' => array(
		'title'   => 'Kontakt',
		'order'   => 4,
		'content' => kx_pattern( 'konstrukta/seitenkopf' )
			. kx_section(
				'<!-- wp:columns {"align":"wide"} --><div class="wp-block-columns alignwide"><!-- wp:column --><div class="wp-block-column">'
				. '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Hauptsitz</h3><!-- /wp:heading -->'
				. $firma_bind( 'name' ) . $firma_bind( 'strasse' ) . $firma_bind( 'ort' )
				. '</div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column">'
				. '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Erreichbarkeit</h3><!-- /wp:heading -->'
				. $firma_bind( 'telefon' ) . $firma_bind( 'email' ) . $firma_bind( 'zeiten' )
				. '</div><!-- /wp:column --></div><!-- /wp:columns -->'
				. '<!-- wp:paragraph {"align":"wide","textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} --><p class="alignwide has-accent-4-color has-text-color" style="font-size:0.9rem">Diese Angaben sind per Block-Binding an die Firmendaten gebunden: Eine Änderung hier ändert auch den Footer.</p><!-- /wp:paragraph -->'
			)
			. $box_ref()
			. kx_section( kx_heading( 'Alle Standorte' ) . $standorte ),
		'meta'    => array( 'kx_claim' => 'Reden wir über Ihr Projekt', 'kx_teaser' => 'Rufen Sie an, schreiben Sie uns – oder kommen Sie in der Villa Seeblick vorbei. Kaffee gibt es immer.', 'kx_rubrik' => 'kontakt', 'kx_im_footer' => true, 'kx_ansprechperson' => $people['lea'], 'kx_pruefen_bis' => gmdate( 'Y-m-d', strtotime( '-9 days' ) ) ),
	),
	'news' => array(
		'title'   => 'News',
		'order'   => 3,
		'content' => '',
		'meta'    => array( 'kx_claim' => 'Neuigkeiten', 'kx_rubrik' => 'unternehmen' ),
	),
);

$ids = array();
foreach ( $pages as $slug => $p ) {
	$ids[ $slug ] = kx_seed_post( array(
		'post_type'    => 'page',
		'post_name'    => $slug,
		'post_title'   => $p['title'],
		'menu_order'   => $p['order'],
		'post_content' => $p['content'],
		'meta'         => $p['meta'],
	) );
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ids['startseite'] );
update_option( 'page_for_posts', $ids['news'] );
WP_CLI::log( 'Seiten: ' . implode( ', ', array_keys( $ids ) ) );

/* ---------------------------------------------------------------------
 * Navigation
 * ------------------------------------------------------------------- */
$link = fn( $label, $url, $id = 0, $kind = 'post-type', $type = 'page' ) => '<!-- wp:navigation-link ' . $j( array_filter( array( 'label' => $label, 'url' => $url, 'id' => $id, 'kind' => $kind, 'type' => $type ) ) ) . ' /-->';
kx_seed_post( array(
	'post_type'    => 'wp_navigation',
	'post_name'    => 'hauptnavigation',
	'post_title'   => 'Hauptnavigation',
	'post_content' => $link( 'Unternehmen', '/unternehmen/', $ids['unternehmen'] )
		. $link( 'Leistungen', '/leistungen/', $ids['leistungen'] )
		. $link( 'Referenzen', '/referenzen/', 0, 'custom', 'custom' )
		. $link( 'News', '/news/', $ids['news'] )
		. $link( 'Kontakt', '/kontakt/', $ids['kontakt'] ),
) );

/* ---------------------------------------------------------------------
 * Redaktions-Benutzer (Rolle «Redakteur») – sieht die Sperren so, wie
 * Kundinnen und Kunden sie sehen. Passwort in bin/.demo-credentials.
 * ------------------------------------------------------------------- */
$cred_file = dirname( __FILE__ ) . '/.demo-credentials';
if ( ! username_exists( 'redaktion' ) ) {
	$pw = wp_generate_password( 16, false );
	wp_insert_user( array( 'user_login' => 'redaktion', 'user_pass' => $pw, 'user_email' => 'redaktion@konstrukta.example', 'role' => 'editor', 'display_name' => 'Redaktion', 'locale' => 'de_CH' ) );
	file_put_contents( $cred_file, "Benutzer: redaktion\nPasswort: $pw\nRolle: Redakteur\n" );
}

require __DIR__ . '/seed-struktur.php';

flush_rewrite_rules();
WP_CLI::success( 'Demo-Inhalte angelegt.' );
