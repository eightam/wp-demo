<?php
/**
 * Title: Seitenkopf (an Seitenfelder gebunden)
 * Slug: konstrukta/seitenkopf
 * Categories: konstrukta, header
 * Post Types: page
 * Description: Claim und Teaser sind per Block-Binding an die Meta-Felder kx_claim/kx_teaser der Seite gebunden – editierbar auf der Leinwand und im DataForm der Seitenleiste.
 * Keywords: header, seitenkopf, binding
 */
?>
<!-- wp:group {"templateLock":"contentOnly","metadata":{"name":"Seitenkopf (gebunden)"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"accent-5","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull has-accent-5-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"820px","justifyContent":"left"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:post-title {"level":0,"className":"kx-eyebrow","style":{"typography":{"fontSize":"0.78rem"}}} /-->
		<!-- wp:heading {"level":1,"metadata":{"bindings":{"content":{"source":"core/post-meta","args":{"key":"kx_claim"}}},"name":"Claim (Seitenfeld)"},"style":{"typography":{"fontSize":"clamp(2.4rem, 5vw, 3.8rem)","lineHeight":"1.05"}}} -->
		<h1 class="wp-block-heading" style="font-size:clamp(2.4rem, 5vw, 3.8rem);line-height:1.05">Claim der Seite</h1>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"core/post-meta","args":{"key":"kx_teaser"}}},"name":"Teaser (Seitenfeld)"},"style":{"typography":{"fontSize":"1.25rem"}},"textColor":"accent-4"} -->
		<p class="has-accent-4-color has-text-color" style="font-size:1.25rem">Teaser der Seite</p>
		<!-- /wp:paragraph -->
		<!-- wp:separator {"className":"is-style-default kx-rule","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"backgroundColor":"accent-1"} -->
		<hr class="wp-block-separator has-text-color has-accent-1-color has-alpha-channel-opacity has-accent-1-background-color has-background is-style-default kx-rule" style="margin-top:var(--wp--preset--spacing--40)"/>
		<!-- /wp:separator -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
