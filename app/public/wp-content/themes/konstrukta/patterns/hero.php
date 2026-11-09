<?php
/**
 * Title: Bühne mit Bild
 * Slug: konstrukta/hero
 * Categories: konstrukta, banner
 * Description: Grosse Einstiegsbühne. Gesperrt (contentOnly): nur Texte, Bild und Links sind änderbar.
 * Keywords: hero, bühne, header
 */
$img = get_stylesheet_directory_uri() . '/assets/img/';
?>
<!-- wp:group {"templateLock":"contentOnly","metadata":{"name":"Bühne (gesperrt)"},"align":"full","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull">
	<!-- wp:cover {"url":"<?php echo esc_url( $img ); ?>bahnhof.jpg","dimRatio":70,"customGradient":"linear-gradient(110deg,rgb(20,33,43) 35%,rgba(31,78,107,0.2) 100%)","minHeight":640,"contentPosition":"center left","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
	<div class="wp-block-cover alignfull has-custom-content-position is-position-center-left" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50);min-height:640px"><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( $img ); ?>bahnhof.jpg" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-70 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(110deg,rgb(20,33,43) 35%,rgba(31,78,107,0.2) 100%)"></span><div class="wp-block-cover__inner-container">
		<!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"680px","justifyContent":"left"}} -->
		<div class="wp-block-group alignwide">
			<!-- wp:paragraph {"className":"kx-eyebrow"} -->
			<p class="kx-eyebrow">Engineering · Design · Simulation</p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(2.6rem, 6vw, 4.6rem)","lineHeight":"1.02"}},"textColor":"base"} -->
			<h1 class="wp-block-heading has-base-color has-text-color" style="font-size:clamp(2.6rem, 6vw, 4.6rem);line-height:1.02">Wir machen aus komplexer Technik funktionierende Produkte.</h1>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"style":{"typography":{"fontSize":"1.2rem"}},"textColor":"accent-2"} -->
			<p class="has-accent-2-color has-text-color" style="font-size:1.2rem">Die Konstrukta Gruppe ist Ihr Engineering-Partner für technologisch anspruchsvolle Entwicklungsprojekte – mit 240 Fachleuten an fünf Standorten.</p>
			<!-- /wp:paragraph -->
			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
			<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--40)">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/leistungen/">Unsere Leistungen</a></div>
				<!-- /wp:button -->
				<!-- wp:button {"className":"is-style-outline","style":{"color":{"text":"#ffffff"}}} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-text-color wp-element-button" href="/referenzen/" style="color:#ffffff">Referenzen ansehen</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:group -->
	</div></div>
	<!-- /wp:cover -->
</div>
<!-- /wp:group -->
