<?php
/**
 * Inhaltstyp «Referenzprojekt».
 *
 * Das Template besteht nur aus der Pflegemaske und ist gesperrt
 * (template_lock = all): die Maske lässt sich nicht löschen, und es lassen
 * sich keine Blöcke darunter einfügen, die die Detailseite nie ausgibt.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'kx_projekt', array(
		'labels'        => array(
			'name'               => 'Referenzen',
			'singular_name'      => 'Referenzprojekt',
			'add_new'            => 'Neues Projekt',
			'add_new_item'       => 'Neues Referenzprojekt',
			'edit_item'          => 'Projekt bearbeiten',
			'all_items'          => 'Alle Projekte',
			'search_items'       => 'Projekte durchsuchen',
			'not_found'          => 'Keine Projekte gefunden.',
			'menu_name'          => 'Referenzen',
		),
		'public'        => true,
		'has_archive'   => 'referenzen',
		'rewrite'       => array( 'slug' => 'referenzen' ),
		'menu_icon'     => 'dashicons-building',
		'menu_position' => 21,
		'show_in_rest'  => true,
		'rest_base'     => 'projekte',
		// custom-fields: nötig, damit REST/Editor die Meta-Felder kennen.
		'supports'      => array( 'title', 'editor', 'custom-fields', 'revisions', 'excerpt' ),
		'template'      => array( array( 'konstrukta/projekt-daten' ) ),
		'template_lock' => 'all',
	) );
} );

/**
 * Taxonomie «Referenzkategorie» (= Branche).
 *
 * Eine Quelle: Redakteure wählen die Branche in der Projektdaten-Maske
 * (Meta kx_branche, Wert = Begriff-Slug). Beim Speichern wird der Begriff
 * zugewiesen. Die Begriffe selbst (Name, Beschreibung, Reihenfolge) pflegt
 * man unter Referenzen → Kategorien; sie speisen Auswahl, Archive und Menü.
 */
add_action( 'init', function () {
	register_taxonomy( 'kx_kategorie', 'kx_projekt', array(
		'labels'            => array(
			'name'          => 'Referenzkategorien',
			'singular_name' => 'Referenzkategorie',
			'menu_name'     => 'Kategorien',
			'all_items'     => 'Alle Kategorien',
			'edit_item'     => 'Kategorie bearbeiten',
			'add_new_item'  => 'Neue Kategorie',
			'search_items'  => 'Kategorien durchsuchen',
			'not_found'     => 'Keine Kategorien gefunden.',
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'referenzen/kategorie', 'with_front' => false ),
	) );
}, 0 );

/** Begriffe als select-Optionen. */
function kx_kategorie_options(): array {
	if ( ! taxonomy_exists( 'kx_kategorie' ) ) {
		return array();
	}
	$terms = get_terms( array( 'taxonomy' => 'kx_kategorie', 'hide_empty' => false, 'orderby' => 'name' ) );
	return is_wp_error( $terms ) ? array() : array_map( fn( $t ) => array( 'value' => $t->slug, 'label' => $t->name ), $terms );
}

/** Meta kx_branche → Begriff zuweisen (Editor, REST, WP-CLI). */
$kx_sync_kategorie = function ( $meta_id, $post_id, $key, $value ) {
	if ( 'kx_branche' === $key && 'kx_projekt' === get_post_type( $post_id ) ) {
		wp_set_object_terms( $post_id, $value ? array( (string) $value ) : array(), 'kx_kategorie' );
	}
};
add_action( 'added_post_meta', $kx_sync_kategorie, 10, 4 );
add_action( 'updated_post_meta', $kx_sync_kategorie, 10, 4 );

/** Taxonomie-Panel im Projekt-Editor ausblenden: die Maske ist die Quelle. */
add_filter( 'rest_prepare_taxonomy', function ( $response, $taxonomy ) {
	if ( 'kx_kategorie' === $taxonomy->name ) {
		$data                    = $response->get_data();
		$data['visibility']['show_ui'] = false;
		$response->set_data( $data );
	}
	return $response;
}, 10, 2 );
