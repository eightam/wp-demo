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
