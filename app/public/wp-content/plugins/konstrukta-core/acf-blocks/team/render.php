<?php
/**
 * ACF-Block: Felder kommen aus get_field(). ACF speichert den Repeater
 * im Blockkommentar als team_0_name, team_0_funktion … (siehe Code-Editor).
 *
 * @var array $block
 * @var bool  $is_preview
 */
$people = get_field( 'team' ) ?: array();
?>
<div <?php echo $is_preview ? 'class="kx-team"' : get_block_wrapper_attributes( array( 'class' => 'kx-team' ) ); ?>>
	<?php foreach ( $people as $p ) : ?>
		<div class="kx-team__person">
			<?php echo $p['foto'] ? wp_get_attachment_image( (int) $p['foto'], 'medium_large' ) : ''; ?>
			<p class="kx-team__name"><?php echo esc_html( $p['name'] ); ?></p>
			<p class="kx-team__role"><?php echo esc_html( $p['funktion'] ); ?></p>
			<?php if ( $p['email'] ) : ?><a class="kx-team__mail" href="mailto:<?php echo esc_attr( $p['email'] ); ?>"><?php echo esc_html( $p['email'] ); ?></a><?php endif; ?>
		</div>
	<?php endforeach; ?>
	<?php if ( ! $people && $is_preview ) : ?><p>Team erfassen: Block auswählen und in der Seitenleiste bearbeiten.</p><?php endif; ?>
</div>
