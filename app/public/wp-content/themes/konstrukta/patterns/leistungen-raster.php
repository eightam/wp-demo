<?php
/**
 * Title: Leistungen – drei Karten
 * Slug: konstrukta/leistungen-raster
 * Categories: konstrukta, services
 * Description: Drei Leistungskarten. Gesperrt (contentOnly): Struktur fix, Texte/Bilder/Links frei.
 * Keywords: leistungen, karten, services
 */
$img = get_stylesheet_directory_uri() . '/assets/img/';
?>
<!-- wp:group {"templateLock":"contentOnly","metadata":{"name":"Leistungen (gesperrt)"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"accent-2","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull has-accent-2-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:paragraph {"align":"wide","className":"kx-eyebrow"} -->
	<p class="alignwide kx-eyebrow">Ihr Mehrwert</p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"align":"wide","style":{"typography":{"fontSize":"clamp(2rem, 4vw, 3rem)"}}} -->
	<h2 class="wp-block-heading alignwide" style="font-size:clamp(2rem, 4vw, 3rem)">Vom ersten Strich bis zur Serie</h2>
	<!-- /wp:heading -->
	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--50)">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"kx-card","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group kx-card">
				<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
				<figure class="wp-block-image size-large"><img src="<?php echo esc_url( $img ); ?>blaupause.jpg" alt=""/></figure>
				<!-- /wp:image -->
				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"default"}} -->
				<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
					<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.35rem"}}} -->
					<h3 class="wp-block-heading" style="font-size:1.35rem">Engineering &amp; Konstruktion</h3>
					<!-- /wp:heading -->
					<!-- wp:paragraph {"textColor":"accent-4"} -->
					<p class="has-accent-4-color has-text-color">Mechanik, Elektrotechnik und Software aus einer Hand. Wir entwickeln Baugruppen, die sich fertigen lassen – und die halten.</p>
					<!-- /wp:paragraph -->
					<!-- wp:paragraph {"style":{"typography":{"fontWeight":"700"}}} -->
					<p style="font-weight:700"><a href="/leistungen/engineering/">Mehr erfahren →</a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"kx-card","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group kx-card">
				<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
				<figure class="wp-block-image size-large"><img src="<?php echo esc_url( $img ); ?>simulation.jpg" alt=""/></figure>
				<!-- /wp:image -->
				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"default"}} -->
				<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
					<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.35rem"}}} -->
					<h3 class="wp-block-heading" style="font-size:1.35rem">Simulation &amp; Berechnung</h3>
					<!-- /wp:heading -->
					<!-- wp:paragraph {"textColor":"accent-4"} -->
					<p class="has-accent-4-color has-text-color">FEM, CFD und Mehrkörpersimulation. Wir finden die Schwachstelle, bevor das erste Bauteil gefräst ist.</p>
					<!-- /wp:paragraph -->
					<!-- wp:paragraph {"style":{"typography":{"fontWeight":"700"}}} -->
					<p style="font-weight:700"><a href="/leistungen/simulation/">Mehr erfahren →</a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"kx-card","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group kx-card">
				<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
				<figure class="wp-block-image size-large"><img src="<?php echo esc_url( $img ); ?>design.jpg" alt=""/></figure>
				<!-- /wp:image -->
				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"default"}} -->
				<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
					<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.35rem"}}} -->
					<h3 class="wp-block-heading" style="font-size:1.35rem">Industriedesign</h3>
					<!-- /wp:heading -->
					<!-- wp:paragraph {"textColor":"accent-4"} -->
					<p class="has-accent-4-color has-text-color">Form, Ergonomie und Bedienung. Vom Moodboard zum Prototyp in wenigen Wochen – mit fotorealistischen Visualisierungen.</p>
					<!-- /wp:paragraph -->
					<!-- wp:paragraph {"style":{"typography":{"fontWeight":"700"}}} -->
					<p style="font-weight:700"><a href="/leistungen/industriedesign/">Mehr erfahren →</a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
