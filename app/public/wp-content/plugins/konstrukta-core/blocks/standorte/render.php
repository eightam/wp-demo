<?php
/** @var array $attributes */
$d = kx_block_data( 'standorte', $attributes );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'kx-locations' ) ); ?>>
	<?php foreach ( $d['orte'] as $row ) : ?>
		<article class="kx-location">
			<?php if ( $d['zeige_bilder'] && $row['bild'] ) : ?>
				<?php echo wp_get_attachment_image( (int) $row['bild'], 'large' ); ?>
			<?php endif; ?>
			<div class="kx-location__body">
				<h3><?php echo esc_html( $row['ort'] ); ?></h3>
				<p><?php echo esc_html( $row['adresse'] ); ?></p>
				<?php if ( $row['telefon'] ) : ?>
					<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $row['telefon'] ) ); ?>"><?php echo esc_html( $row['telefon'] ); ?></a></p>
				<?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
	<?php if ( ! $d['orte'] && wp_is_serving_rest_request() ) : ?>
		<p>Noch keine Standorte – Block auswählen, um sie zu erfassen.</p>
	<?php endif; ?>
</div>
