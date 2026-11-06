<?php
/**
 * Lesen mit Rückfallwerten.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Alle Felder eines Meta-Schemas für einen Beitrag, gebündelt.
 */
function kx_data( string $schema_id, ?int $post_id = null ): array {
	$schema  = kx_schema( $schema_id );
	$post_id = $post_id ?: get_the_ID();
	$out     = array();
	foreach ( $schema['fields'] as $field ) {
		$key   = $schema['prefix'] . $field['key'];
		$value = metadata_exists( 'post', $post_id, $key ) ? get_post_meta( $post_id, $key, true ) : kx_field_default( $field );
		$out[ $field['key'] ] = $value;
	}
	return $out;
}

/** Attribute eines Schema-Blocks mit Rückfallwerten. */
function kx_block_data( string $schema_id, array $attributes ): array {
	$out = array();
	foreach ( kx_schema( $schema_id )['fields'] as $field ) {
		$out[ $field['key'] ] = $attributes[ $field['key'] ] ?? kx_field_default( $field );
	}
	return $out;
}

/** Bezeichnung zu einem select-Wert. */
function kx_option_label( string $schema_id, string $key, $value ): string {
	foreach ( kx_schema( $schema_id )['fields'] as $field ) {
		if ( $field['key'] === $key ) {
			foreach ( $field['options'] ?? array() as $o ) {
				if ( $o['value'] === $value ) {
					return $o['label'];
				}
			}
		}
	}
	return (string) $value;
}

/** Hintergrund-Stil für einen Verlaufswert. */
function kx_gradient_style( string $value ): string {
	return $value ? 'background:' . esc_attr( $value ) . ';' : 'background:var(--wp--preset--gradient--stahl);';
}
