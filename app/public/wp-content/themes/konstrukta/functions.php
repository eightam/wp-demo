<?php
/**
 * Konstrukta – Kindthema.
 */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'konstrukta', get_stylesheet_directory_uri() . '/assets/theme.css', array(), wp_get_theme()->get( 'Version' ) );
} );

add_action( 'after_setup_theme', function () {
	add_editor_style( 'assets/theme.css' );
} );

// Eigene Pattern-Kategorie.
add_action( 'init', function () {
	register_block_pattern_category( 'konstrukta', array( 'label' => 'Konstrukta' ) );
} );
