<?php
/**
 * Title: Neuigkeiten
 * Slug: konstrukta/news
 * Categories: konstrukta, query
 * Description: Die drei neusten Beiträge. Gesperrt (contentOnly): Überschrift editierbar, Abfrage fix.
 */
?>
<!-- wp:group {"templateLock":"contentOnly","metadata":{"name":"Neuigkeiten (gesperrt)"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:paragraph {"align":"wide","className":"kx-eyebrow"} -->
	<p class="alignwide kx-eyebrow">News</p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"align":"wide"} -->
	<h2 class="wp-block-heading alignwide">Neuigkeiten aus der Konstrukta Gruppe</h2>
	<!-- /wp:heading -->
	<!-- wp:query {"queryId":7,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"align":"wide"} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
			<!-- wp:post-featured-image {"aspectRatio":"4/3","isLink":true} /-->
			<!-- wp:post-date {"format":"d.m.Y","style":{"typography":{"fontSize":"0.85rem"}},"textColor":"accent-4"} /-->
			<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"1.3rem"},"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} /-->
			<!-- wp:post-excerpt {"moreText":"Weiterlesen …","excerptLength":24} /-->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->
