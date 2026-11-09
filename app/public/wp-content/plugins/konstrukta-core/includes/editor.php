<?php
/**
 * Editor-Skripte. Die Maske braucht keinen Build: wp.element.createElement
 * statt JSX. Nur das DataForm (npm-Paket @wordpress/dataviews) ist gebündelt.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	wp_register_script(
		'kx-schema-form',
		KX_URL . 'assets/js/schema-form.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-core-data', 'wp-editor', 'wp-server-side-render', 'wp-primitives', 'wp-notices', 'wp-compose' ),
		filemtime( KX_DIR . '/assets/js/schema-form.js' ),
		true
	);
	wp_register_style( 'kx-schema-form', KX_URL . 'assets/css/editor.css', array(), filemtime( KX_DIR . '/assets/css/editor.css' ) );
}, 5 );

/** Daten für den Editor: Schema, Verläufe aus theme.json, Firmendaten. */
function kx_editor_data(): array {
	$gradients = array();
	foreach ( wp_get_global_settings( array( 'color', 'gradients' ) ) as $origin => $list ) {
		if ( 'theme' !== $origin ) {
			continue;
		}
		foreach ( $list as $g ) {
			$gradients[] = array(
				'name'     => $g['name'],
				'slug'     => $g['slug'],
				'value'    => 'var(--wp--preset--gradient--' . $g['slug'] . ')',
				'gradient' => $g['gradient'],
			);
		}
	}
	return array(
		'schemas'     => kx_schemas(),
		'gradients'   => $gradients,
		'firma'       => kx_firma(),
		'firmaFields' => kx_firma_fields(),
		'users'       => kx_user_options(),
		// Beispielprojekt für die Vorschau im Template-Editor.
		'previewProjekt' => (int) ( get_posts( array( 'post_type' => 'kx_projekt', 'numberposts' => 1, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC' ) )[0] ?? 0 ),
	);
}

add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_script( 'kx-schema-form' );
	wp_add_inline_script( 'kx-schema-form', 'window.kxEditor = ' . wp_json_encode( kx_editor_data() ) . ';', 'before' );

	// DataForm für Seiten (gebündelt).
	$asset_file = KX_DIR . '/build/dataform.asset.php';
	if ( file_exists( $asset_file ) && 'page' === get_current_screen()?->post_type ) {
		$asset = require $asset_file;
		wp_enqueue_script( 'kx-dataform', KX_URL . 'build/dataform.js', array_merge( $asset['dependencies'], array( 'kx-schema-form' ) ), $asset['version'], true );
		wp_enqueue_style( 'kx-dataviews', KX_URL . 'build/dataviews.css', array( 'wp-components' ), $asset['version'] );
		wp_enqueue_style( 'kx-dataform', KX_URL . 'build/dataform.css', array( 'kx-dataviews' ), $asset['version'] );
	}
} );

/** Mögliche Ansprechpersonen: alle, die Inhalte bearbeiten dürfen. */
function kx_user_options(): array {
	return array_map(
		fn( $u ) => array( 'value' => (int) $u->ID, 'label' => $u->display_name, 'email' => $u->user_email ),
		get_users( array( 'capability' => 'edit_pages', 'orderby' => 'display_name' ) )
	);
}
