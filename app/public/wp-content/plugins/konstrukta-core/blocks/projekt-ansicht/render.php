<?php
/**
 * @var array    $attributes
 * @var WP_Block $block
 */
$post_id = $block->context['postId'] ?? get_the_ID();
if ( ! $post_id || 'kx_projekt' !== get_post_type( $post_id ) ) {
	return;
}
$d    = kx_data( 'projekt', $post_id );
$teil = $attributes['teil'];
$wrap = fn( $class, $style = '' ) => get_block_wrapper_attributes( array( 'class' => $class, 'style' => $style ) );

switch ( $teil ) {
	case 'kopf':
		?>
		<header <?php echo $wrap( 'kx-project-hero', kx_gradient_style( $d['stimmung'] ) ); ?>>
			<?php echo $d['ansicht'] ? wp_get_attachment_image( (int) $d['ansicht'], 'full' ) : ''; ?>
			<div style="max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:0 var(--wp--style--root--padding-right) 0 var(--wp--style--root--padding-left)">
				<p class="kx-eyebrow" style="color:#fff;opacity:.85"><?php echo esc_html( kx_option_label( 'projekt', 'branche', $d['branche'] ) ); ?> · <?php echo esc_html( $d['kunde'] ); ?></p>
				<h1 style="color:#fff;font-size:clamp(2.2rem,5vw,4rem);margin:.3em 0 0;max-width:20ch"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
			</div>
		</header>
		<?php
		break;

	case 'fakten':
		?>
		<dl <?php echo $wrap( 'kx-facts' ); ?>>
			<div><dt>Kunde</dt><dd><?php echo esc_html( $d['kunde'] ?: '–' ); ?></dd></div>
			<div><dt>Branche</dt><dd><?php echo esc_html( kx_option_label( 'projekt', 'branche', $d['branche'] ) ); ?></dd></div>
			<div><dt>Standort</dt><dd><?php echo esc_html( $d['standort'] ?: '–' ); ?></dd></div>
			<div><dt>Abschluss</dt><dd><?php echo $d['jahr'] ? (int) $d['jahr'] : '–'; ?></dd></div>
		</dl>
		<?php
		break;

	case 'kennzahlen':
		if ( ! $d['kennzahlen'] ) {
			return;
		}
		?>
		<div <?php echo $wrap( 'kx-stats' ); ?>>
			<?php foreach ( $d['kennzahlen'] as $row ) : ?>
				<div class="kx-stat">
					<div class="kx-stat__value"><?php echo esc_html( $row['wert'] ); ?><span class="kx-stat__unit"><?php echo esc_html( $row['einheit'] ); ?></span></div>
					<div class="kx-stat__label"><?php echo esc_html( $row['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		break;

	case 'leistungen':
		if ( ! $d['leistungen'] ) {
			return;
		}
		?>
		<ul <?php echo $wrap( 'kx-services' ); ?>>
			<?php foreach ( $d['leistungen'] as $row ) : ?>
				<li><strong><?php echo esc_html( $row['titel'] ); ?></strong><br><?php echo esc_html( $row['text'] ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php
		break;

	case 'galerie':
		if ( ! $d['galerie'] ) {
			return;
		}
		?>
		<div <?php echo $wrap( 'kx-gallery' ); ?>>
			<?php foreach ( $d['galerie'] as $row ) : ?>
				<figure style="<?php echo $row['flaeche'] ? esc_attr( 'background:' . $row['flaeche'] . ';padding:.75rem' ) : ''; ?>">
					<?php echo wp_get_attachment_image( (int) $row['bild'], 'large' ); ?>
					<?php if ( $row['legende'] ) : ?>
						<figcaption style="<?php echo $row['flaeche'] ? 'color:#fff' : ''; ?>"><?php echo esc_html( $row['legende'] ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
		<?php
		break;
}
