<?php
/** @var array $attributes */
$d     = kx_block_data( 'kennzahlen', $attributes );
$style = '';
$class = 'kx-stats-wrap kx-stats-wrap--' . $d['stil'];
if ( 'dunkel' === $d['stil'] ) {
	$style = 'background:var(--wp--preset--color--contrast);color:#fff;';
} elseif ( 'verlauf' === $d['stil'] ) {
	$style = kx_gradient_style( $d['flaeche'] ) . 'color:#fff;';
} else {
	$style = 'background:var(--wp--preset--color--accent-5);';
}
$wrapper = get_block_wrapper_attributes( array( 'class' => $class, 'style' => $style . 'padding:clamp(2rem,5vw,4rem);' ) );
?>
<section <?php echo $wrapper; ?>>
	<?php if ( $d['titel'] ) : ?>
		<p class="kx-eyebrow" style="margin-top:0;<?php echo 'hell' === $d['stil'] ? '' : 'color:#fff;opacity:.8'; ?>"><?php echo esc_html( $d['titel'] ); ?></p>
	<?php endif; ?>
	<div class="kx-stats">
		<?php foreach ( $d['werte'] as $row ) : ?>
			<div class="kx-stat">
				<div class="kx-stat__value"><?php echo esc_html( $row['wert'] ); ?><span class="kx-stat__unit"><?php echo esc_html( $row['einheit'] ); ?></span></div>
				<div class="kx-stat__label"><?php echo esc_html( $row['label'] ); ?></div>
			</div>
		<?php endforeach; ?>
		<?php if ( ! $d['werte'] && wp_is_serving_rest_request() ) : ?>
			<p>Noch keine Zahlen – Block auswählen, um sie zu erfassen.</p>
		<?php endif; ?>
	</div>
</section>
