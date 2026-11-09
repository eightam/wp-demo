<?php
/**
 * @var array    $attributes
 * @var WP_Block $block
 */
$args = array(
	'post_type'      => 'kx_projekt',
	'posts_per_page' => max( 1, (int) $attributes['anzahl'] ),
	'post_status'    => 'publish',
	'meta_query'     => array(),
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
);
// Auf einer Projekt-Detailseite das aktuelle Projekt auslassen (nicht im Archiv:
// dort ist postId nur das erste Projekt der Hauptabfrage).
if ( is_singular( 'kx_projekt' ) && ! empty( $block->context['postId'] ) ) {
	$args['post__not_in'] = array( $block->context['postId'] );
}
if ( $attributes['nurHervorgehoben'] ) {
	$args['meta_query'][] = array( 'key' => 'kx_hervorheben', 'value' => '1' );
}
// Kategorie: fest gewählt oder – im Kategorie-Archiv – die aktuelle.
$kategorie = $attributes['branche'] ?: ( is_tax( 'kx_kategorie' ) ? get_queried_object()->slug : '' );
if ( $kategorie ) {
	$args['tax_query'] = array( array( 'taxonomy' => 'kx_kategorie', 'field' => 'slug', 'terms' => $kategorie ) );
}
$q = new WP_Query( $args );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'kx-projects' ) ); ?>>
	<?php foreach ( $q->posts as $p ) : $d = kx_data( 'projekt', $p->ID ); ?>
		<a class="kx-project-card" href="<?php echo esc_url( get_permalink( $p ) ); ?>" style="<?php echo kx_gradient_style( $d['stimmung'] ); ?>">
			<?php echo $d['ansicht'] ? wp_get_attachment_image( (int) $d['ansicht'], 'large', false, array( 'class' => 'kx-project-card__img' ) ) : ''; ?>
			<div class="kx-project-card__body">
				<div class="kx-project-card__meta"><?php echo esc_html( kx_option_label( 'projekt', 'branche', $d['branche'] ) ); ?><?php echo $d['jahr'] ? ' · ' . (int) $d['jahr'] : ''; ?></div>
				<h3 class="kx-project-card__title"><?php echo esc_html( get_the_title( $p ) ); ?></h3>
				<div style="font-size:.9rem;opacity:.85;margin-top:.4rem"><?php echo esc_html( $d['kunde'] ); ?></div>
			</div>
		</a>
	<?php endforeach; ?>
	<?php if ( ! $q->posts ) : ?><p>Keine Projekte gefunden.</p><?php endif; ?>
</div>
