<?php
/**
 * Block-Bindings.
 *
 * - core/post-meta (Core): Seitenkopf-Pattern bindet Überschrift/Text an kx_claim
 *   und kx_teaser. Im Editor direkt auf der Leinwand editierbar (WP 6.7+).
 * - konstrukta/firma: Firmendaten aus einer Option. Editierbar, Quelle in JS
 *   registriert (schema-form.js). Ändert man die Telefonnummer im Footer,
 *   ändert sie sich überall.
 * - konstrukta/seite: Seitenfelder aufbereitet (Rubrik als Bezeichnung). Nur lesend.
 * - konstrukta/projekt: Projektdaten aufbereitet (z. B. Branche als Bezeichnung
 *   statt Schlüssel). Nur lesend.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_block_bindings_source( 'konstrukta/firma', array(
		'label'              => 'Firmendaten',
		'get_value_callback' => fn( array $args ) => kx_firma( $args['key'] ?? '' ),
	) );

	register_block_bindings_source( 'konstrukta/seite', array(
		'label'              => 'Seitenfelder',
		'uses_context'       => array( 'postId' ),
		'get_value_callback' => function ( array $args, WP_Block $block ) {
			$post_id = $block->context['postId'] ?? get_the_ID();
			$d       = kx_data( 'seite', $post_id );
			$key     = $args['key'] ?? '';
			if ( 'rubrik' === $key ) {
				return kx_option_label( 'seite', 'rubrik', $d['rubrik'] );
			}
			return isset( $d[ $key ] ) && is_scalar( $d[ $key ] ) ? (string) $d[ $key ] : null;
		},
	) );

	register_block_bindings_source( 'konstrukta/projekt', array(
		'label'              => 'Projektdaten',
		'uses_context'       => array( 'postId' ),
		'get_value_callback' => function ( array $args, WP_Block $block ) {
			$post_id = $block->context['postId'] ?? get_the_ID();
			if ( 'kx_projekt' !== get_post_type( $post_id ) ) {
				return null;
			}
			$d   = kx_data( 'projekt', $post_id );
			$key = $args['key'] ?? '';
			switch ( $key ) {
				case 'branche':
					return kx_option_label( 'projekt', 'branche', $d['branche'] );
				case 'kicker':
					return kx_option_label( 'projekt', 'branche', $d['branche'] ) . ' · ' . $d['kunde'];
				case 'ansicht_url':
					return $d['ansicht'] ? wp_get_attachment_image_url( (int) $d['ansicht'], 'full' ) : '';
				default:
					return isset( $d[ $key ] ) && is_scalar( $d[ $key ] ) ? (string) $d[ $key ] : null;
			}
		},
	) );
} );

/**
 * Schaltfläche, deren Link an kx_cta_url gebunden ist: ohne Link nicht ausgeben.
 */
add_filter( 'render_block_core/button', function ( $html, $block ) {
	$binding = $block['attrs']['metadata']['bindings']['url'] ?? null;
	if ( $binding && 'core/post-meta' === ( $binding['source'] ?? '' ) && ! get_post_meta( get_the_ID(), $binding['args']['key'] ?? '', true ) ) {
		return '';
	}
	return $html;
}, 10, 2 );

add_filter( 'render_block_core/buttons', fn( $html ) => false === strpos( $html, '<a' ) ? '' : $html );
