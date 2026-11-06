<?php
/**
 * Title: Text und Bild
 * Slug: konstrukta/text-bild
 * Categories: konstrukta, text
 * Description: Bild links, Text rechts. Gesperrt (contentOnly).
 */
$img = get_stylesheet_directory_uri() . '/assets/img/';
?>
<!-- wp:group {"templateLock":"contentOnly","metadata":{"name":"Text und Bild (gesperrt)"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:media-text {"align":"wide","mediaType":"image","mediaWidth":52,"verticalAlignment":"center","imageFill":false} -->
	<div class="wp-block-media-text alignwide is-stacked-on-mobile is-vertically-aligned-center" style="grid-template-columns:52% auto"><figure class="wp-block-media-text__media"><img src="<?php echo esc_url( $img ); ?>villa.jpg" alt=""/></figure><div class="wp-block-media-text__content">
		<!-- wp:paragraph {"className":"kx-eyebrow"} -->
		<p class="kx-eyebrow">Unternehmen</p>
		<!-- /wp:paragraph -->
		<!-- wp:heading -->
		<h2 class="wp-block-heading">Ein Haus mit Geschichte, ein Team mit Zukunft</h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p>Seit 2009 arbeiten wir in der Villa Seeblick in Romanshorn. Das Gebäude von 1924 ist heute Werkstatt, Labor und Treffpunkt – hier entstehen Ideen, die später in Zügen, Kraftwerken und Operationssälen ihren Dienst tun.</p>
		<!-- /wp:paragraph -->
		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/unternehmen/">Über uns</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div></div>
	<!-- /wp:media-text -->
</div>
<!-- /wp:group -->
