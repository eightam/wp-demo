<?php
/**
 * Blöcke registrieren. Bei Schema-Blöcken mit storage = attributes kommen die
 * Attribute aus dem Schema, nicht aus block.json – das Schema steht nur einmal.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$by_block = array();
	foreach ( kx_schemas() as $id => $schema ) {
		if ( 'attributes' === $schema['storage'] ) {
			$by_block[ $schema['block'] ] = $id;
		}
	}

	foreach ( glob( KX_DIR . '/blocks/*/block.json' ) as $file ) {
		$meta = wp_json_file_decode( $file, array( 'associative' => true ) );
		$args = array();
		if ( isset( $by_block[ $meta['name'] ] ) ) {
			$attrs = array();
			foreach ( kx_schema( $by_block[ $meta['name'] ] )['fields'] as $field ) {
				$attrs[ $field['key'] ] = kx_field_json_schema( $field ) + array( 'default' => kx_field_default( $field ) );
			}
			$args['attributes'] = $attrs;
		}
		register_block_type( dirname( $file ), $args );
	}
}, 20 );
