<?php
/**
 * Firmendaten als eine Option – in REST sichtbar, damit die Bindings-Quelle
 * «konstrukta/firma» sie im Editor lesen und schreiben kann.
 */

defined( 'ABSPATH' ) || exit;

function kx_firma_fields(): array {
	return array(
		'name'    => 'Firmenname',
		'strasse' => 'Strasse',
		'ort'     => 'PLZ Ort',
		'telefon' => 'Telefon',
		'email'   => 'E-Mail',
		'zeiten'  => 'Öffnungszeiten',
	);
}

function kx_firma( ?string $key = null ) {
	$defaults = array(
		'name'    => 'Konstrukta Engineering AG',
		'strasse' => 'Seestrasse 41',
		'ort'     => '8590 Romanshorn',
		'telefon' => '+41 71 466 20 00',
		'email'   => 'info@konstrukta.example',
		'zeiten'  => 'Mo–Fr, 7.30–17.30 Uhr',
	);
	$data = wp_parse_args( (array) get_option( 'kx_firma', array() ), $defaults );
	return null === $key ? $data : ( $data[ $key ] ?? '' );
}

add_action( 'init', function () {
	$props = array();
	foreach ( kx_firma_fields() as $key => $label ) {
		$props[ $key ] = array( 'type' => 'string' );
	}
	register_setting( 'general', 'kx_firma', array(
		'type'              => 'object',
		'label'             => 'Firmendaten',
		'default'           => array(),
		'sanitize_callback' => function ( $value ) {
			$out = array();
			foreach ( array_keys( kx_firma_fields() ) as $key ) {
				if ( isset( $value[ $key ] ) ) {
					$out[ $key ] = sanitize_text_field( $value[ $key ] );
				}
			}
			return $out;
		},
		'show_in_rest'      => array(
			'schema' => array(
				'type'                 => 'object',
				'properties'           => $props,
				'additionalProperties' => false,
			),
		),
	) );
} );
