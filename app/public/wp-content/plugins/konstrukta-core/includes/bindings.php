<?php
/**
 * Block-Bindings.
 *
 * - core/post-meta (Core): Seitenkopf-Pattern bindet Überschrift/Text an kx_claim
 *   und kx_teaser. Im Editor direkt auf der Leinwand editierbar (WP 6.7+).
 * - konstrukta/firma: Firmendaten aus einer Option. Editierbar, Quelle in JS
 *   registriert (schema-form.js). Ändert man die Telefonnummer im Footer,
 *   ändert sie sich überall.
 * - konstrukta/projekt: Projektdaten aufbereitet (z. B. Branche als Bezeichnung
 *   statt Schlüssel). Nur lesend.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_block_bindings_source( 'konstrukta/firma', array(
		'label'              => 'Firmendaten',
		'get_value_callback' => fn( array $args ) => kx_firma( $args['key'] ?? '' ),
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
