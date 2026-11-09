<?php
/** Seiten mit kx_im_footer = true (Seitenfeld aus dem DataForm). */
$pages = get_posts( array(
	'post_type'   => 'page',
	'numberposts' => 12,
	'orderby'     => 'menu_order',
	'order'       => 'ASC',
	'meta_query'  => array( array( 'key' => 'kx_im_footer', 'value' => '1' ) ),
) );
if ( ! $pages ) {
	return;
}
?>
<ul <?php echo get_block_wrapper_attributes( array( 'class' => 'kx-footer-links', 'style' => 'list-style:none;padding:0;margin:0;line-height:1.9' ) ); ?>>
	<?php foreach ( $pages as $p ) : ?>
		<li><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></li>
	<?php endforeach; ?>
</ul>
