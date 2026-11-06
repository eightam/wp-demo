<?php
/**
 * Meta-Felder aus dem Schema registrieren.
 *
 * Eine Liste (rows) ist ein Meta-Schlüssel mit einem serialisierten Array –
 * kein ACF-artiges kx_galerie_0_bild. Blöcke und Bindings lesen sie als ein Feld.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	foreach ( kx_schemas() as $schema ) {
		if ( 'meta' !== $schema['storage'] ) {
			continue;
		}
		foreach ( $schema['fields'] as $field ) {
			$json = kx_field_json_schema( $field );
			$args = array(
				'type'              => $json['type'],
				'single'            => true,
				'default'           => kx_field_default( $field ),
				'label'             => $field['label'],
				'description'       => $field['help'] ?? '',
				// Revisionen stellen auch Preis, Galerie & Co. wieder her (WP 6.4+).
				'revisions_enabled' => true,
				'show_in_rest'      => array( 'schema' => $json ),
				'sanitize_callback' => fn( $value ) => kx_sanitize_field( $value, $field ),
				'auth_callback'     => fn( $allowed, $key, $post_id ) => current_user_can( 'edit_post', $post_id ),
			);
			register_post_meta( $schema['post_type'], $schema['prefix'] . $field['key'], $args );
		}
	}
}, 20 ); // Nach register_post_type(): revisions_enabled verlangt Revisions-Support.
