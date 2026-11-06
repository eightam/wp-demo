<?php
/**
 * Vergleich: ACF Pro. Nur aktiv, wenn ACF Pro läuft.
 *
 * Vorteile: Pflichtfelder, min/max Zeilen, bedingte Logik ohne eigenen Code.
 * Nachteile: Lizenz-Abhängigkeit; Repeater-Zellen landen als einzelne Schlüssel
 * (team_0_name …), nicht als ein Feld.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	register_block_type( KX_DIR . '/acf-blocks/team' );

	acf_add_local_field_group( array(
		'key'      => 'group_kx_team',
		'title'    => 'Team (ACF)',
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'konstrukta/team-acf' ) ) ),
		'fields'   => array(
			array(
				'key'          => 'field_kx_team',
				'name'         => 'team',
				'label'        => 'Personen',
				'type'         => 'repeater',
				'layout'       => 'block',
				'min'          => 1,
				'max'          => 8,
				'button_label' => 'Person hinzufügen',
				'collapsed'    => 'field_kx_team_name',
				'sub_fields'   => array(
					array( 'key' => 'field_kx_team_name', 'name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => 1 ),
					array( 'key' => 'field_kx_team_funktion', 'name' => 'funktion', 'label' => 'Funktion', 'type' => 'text' ),
					array( 'key' => 'field_kx_team_foto', 'name' => 'foto', 'label' => 'Foto', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail' ),
					array( 'key' => 'field_kx_team_email', 'name' => 'email', 'label' => 'E-Mail', 'type' => 'email' ),
				),
			),
		),
	) );
} );
