<?php
/**
 * Plugin Name:       Konstrukta Core
 * Description:       Demo: Pflegemasken im Block-Editor. Ein Schema in PHP steuert Meta-Felder, Blockattribute, die Canvas-Maske und das DataForm.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      8.0
 * Text Domain:       konstrukta
 */

defined( 'ABSPATH' ) || exit;

define( 'KX_VERSION', '1.0.0' );
define( 'KX_DIR', __DIR__ );
define( 'KX_URL', plugin_dir_url( __FILE__ ) );

require KX_DIR . '/includes/schema.php';      // Das eine Schema.
require KX_DIR . '/includes/meta.php';        // Meta-Felder aus dem Schema.
require KX_DIR . '/includes/post-types.php';  // Referenzprojekte, Template-Sperre.
require KX_DIR . '/includes/helpers.php';     // kx_data(): Lesen mit Rückfallwerten.
require KX_DIR . '/includes/blocks.php';      // Blöcke, Attribute aus dem Schema.
require KX_DIR . '/includes/bindings.php';    // Block-Bindings-Quellen.
require KX_DIR . '/includes/editor.php';      // Schema + Verläufe an den Editor.
require KX_DIR . '/includes/settings.php';    // Firmendaten (Optionen, REST).
require KX_DIR . '/includes/admin.php';       // DataViews-Seite, Dashboard-Hinweis.
