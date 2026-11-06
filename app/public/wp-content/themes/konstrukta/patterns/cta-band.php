<?php
/**
 * Title: Aufruf-Band
 * Slug: konstrukta/cta-band
 * Categories: konstrukta, call-to-action
 * Description: Farbiges Band mit Satz und Schaltfläche. Gesperrt (contentOnly).
 */
?>
<!-- wp:group {"templateLock":"contentOnly","metadata":{"name":"Aufruf-Band (gesperrt)"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"backgroundColor":"accent-1","textColor":"base","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull has-base-color has-accent-1-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:heading {"style":{"typography":{"fontSize":"clamp(1.6rem, 3vw, 2.4rem)"}},"textColor":"base"} -->
		<h2 class="wp-block-heading has-base-color has-text-color" style="font-size:clamp(1.6rem, 3vw, 2.4rem)">Haben Sie ein kniffliges Projekt?</h2>
		<!-- /wp:heading -->
		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"backgroundColor":"contrast","textColor":"base"} -->
			<div class="wp-block-button"><a class="wp-block-button__link has-base-color has-contrast-background-color has-text-color has-background wp-element-button" href="/kontakt/">Gespräch vereinbaren</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
