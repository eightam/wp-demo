<?php
/** @var array $attributes */
$d = kx_block_data( 'team', $attributes );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'kx-team' . ( $d['graustufen'] ? ' is-grayscale' : '' ) ) ); ?>>
	<?php foreach ( $d['personen'] as $row ) : ?>
		<div class="kx-team__person">
			<?php echo $row['foto'] ? wp_get_attachment_image( (int) $row['foto'], 'medium_large' ) : ''; ?>
			<p class="kx-team__name"><?php echo esc_html( $row['name'] ); ?></p>
			<p class="kx-team__role"><?php echo esc_html( $row['funktion'] ); ?></p>
			<?php if ( $row['email'] ) : ?><a class="kx-team__mail" href="mailto:<?php echo esc_attr( $row['email'] ); ?>"><?php echo esc_html( $row['email'] ); ?></a><?php endif; ?>
		</div>
	<?php endforeach; ?>
	<?php if ( ! $d['personen'] && wp_is_serving_rest_request() ) : ?>
		<p>Noch keine Personen – Block auswählen, um sie zu erfassen.</p>
	<?php endif; ?>
</div>
