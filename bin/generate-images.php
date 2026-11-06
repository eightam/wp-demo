<?php
/**
 * Erzeugt abstrakte Platzhalterbilder (Blaupausen-Stil) für das Demo-Theme.
 * php bin/generate-images.php <zielordner>
 */
$dir = rtrim( $argv[1] ?? __DIR__ . '/../app/public/wp-content/themes/konstrukta/assets/img', '/' );
@mkdir( $dir, 0777, true );

$sets = array(
	'blaupause'   => array( array( 31, 78, 107 ), array( 20, 33, 43 ), 'grid' ),
	'bahnhof'     => array( array( 20, 33, 43 ), array( 31, 78, 107 ), 'perspective' ),
	'turbine'     => array( array( 31, 78, 107 ), array( 12, 20, 28 ), 'rings' ),
	'werkstatt'   => array( array( 226, 84, 27 ), array( 20, 33, 43 ), 'grid' ),
	'simulation'  => array( array( 20, 33, 43 ), array( 122, 46, 79 ), 'mesh' ),
	'design'      => array( array( 230, 237, 242 ), array( 169, 200, 216 ), 'rings' ),
	'villa'       => array( array( 246, 244, 239 ), array( 169, 200, 216 ), 'house' ),
	'bruecke'     => array( array( 31, 78, 107 ), array( 226, 84, 27 ), 'bridge' ),
	'roboter'     => array( array( 20, 33, 43 ), array( 91, 103, 112 ), 'mesh' ),
	'tueren'      => array( array( 230, 237, 242 ), array( 31, 78, 107 ), 'perspective' ),
	'team-1'      => array( array( 91, 103, 112 ), array( 20, 33, 43 ), 'person' ),
	'team-2'      => array( array( 169, 200, 216 ), array( 31, 78, 107 ), 'person' ),
	'team-3'      => array( array( 226, 84, 27 ), array( 20, 33, 43 ), 'person' ),
	'team-4'      => array( array( 246, 244, 239 ), array( 91, 103, 112 ), 'person' ),
);

mt_srand( 42 );
foreach ( $sets as $name => list( $a, $b, $motif ) ) {
	$square = 0 === strpos( $name, 'team' );
	$w = $square ? 800 : 1600;
	$h = $square ? 800 : 1000;
	$im = imagecreatetruecolor( $w, $h );
	imageantialias( $im, true );
	for ( $y = 0; $y < $h; $y++ ) {
		$t = $y / $h;
		$c = imagecolorallocate( $im, (int) ( $a[0] + ( $b[0] - $a[0] ) * $t ), (int) ( $a[1] + ( $b[1] - $a[1] ) * $t ), (int) ( $a[2] + ( $b[2] - $a[2] ) * $t ) );
		imageline( $im, 0, $y, $w, $y, $c );
	}
	$light = array_sum( $a ) > 500;
	$line  = $light ? imagecolorallocatealpha( $im, 20, 33, 43, 95 ) : imagecolorallocatealpha( $im, 255, 255, 255, 100 );
	$bold  = $light ? imagecolorallocatealpha( $im, 20, 33, 43, 40 ) : imagecolorallocatealpha( $im, 255, 255, 255, 50 );
	$hot   = imagecolorallocatealpha( $im, 226, 84, 27, 20 );
	imagesetthickness( $im, 1 );
	for ( $x = 0; $x < $w; $x += 40 ) { imageline( $im, $x, 0, $x, $h, $line ); }
	for ( $y = 0; $y < $h; $y += 40 ) { imageline( $im, 0, $y, $w, $y, $line ); }
	imagesetthickness( $im, 3 );
	switch ( $motif ) {
		case 'grid':
			for ( $i = 0; $i < 7; $i++ ) {
				$x = mt_rand( 100, $w - 400 ); $y = mt_rand( 100, $h - 300 );
				imagerectangle( $im, $x, $y, $x + mt_rand( 120, 380 ), $y + mt_rand( 80, 260 ), $bold );
			}
			imageellipse( $im, (int) ( $w * .7 ), (int) ( $h * .45 ), 360, 360, $hot );
			break;
		case 'perspective':
			$vx = $w / 2; $vy = $h * .42;
			for ( $i = -10; $i <= 10; $i++ ) { imageline( $im, (int) $vx, (int) $vy, (int) ( $vx + $i * 220 ), $h, $bold ); }
			for ( $k = 1; $k < 9; $k++ ) { $yy = (int) ( $vy + ( $h - $vy ) * pow( $k / 9, 1.8 ) ); imageline( $im, 0, $yy, $w, $yy, $line ); }
			imagefilledrectangle( $im, (int) ( $vx - 30 ), (int) ( $vy - 120 ), (int) ( $vx + 30 ), (int) $vy, $hot );
			break;
		case 'rings':
			$cx = (int) ( $w * .6 ); $cy = (int) ( $h * .5 );
			for ( $r = 60; $r < $w; $r += 70 ) { imageellipse( $im, $cx, $cy, $r * 2, $r * 2, $r % 3 ? $bold : $line ); }
			for ( $d = 0; $d < 360; $d += 30 ) { imageline( $im, $cx, $cy, (int) ( $cx + cos( deg2rad( $d ) ) * 420 ), (int) ( $cy + sin( deg2rad( $d ) ) * 420 ), $bold ); }
			imagefilledellipse( $im, $cx, $cy, 60, 60, $hot );
			break;
		case 'mesh':
			$pts = array();
			for ( $i = 0; $i < 40; $i++ ) { $pts[] = array( mt_rand( 0, $w ), mt_rand( 0, $h ) ); }
			foreach ( $pts as $i => $p ) {
				foreach ( $pts as $j => $q ) {
					if ( $j > $i && hypot( $p[0] - $q[0], $p[1] - $q[1] ) < 330 ) { imageline( $im, $p[0], $p[1], $q[0], $q[1], $bold ); }
				}
				imagefilledellipse( $im, $p[0], $p[1], 10, 10, $i % 7 ? $bold : $hot );
			}
			break;
		case 'house':
			$x = (int) ( $w * .25 ); $y = (int) ( $h * .35 );
			imagerectangle( $im, $x, $y, $x + 800, $y + 500, $bold );
			imageline( $im, $x - 40, $y, $x + 400, $y - 260, $bold ); imageline( $im, $x + 400, $y - 260, $x + 840, $y, $bold );
			for ( $i = 0; $i < 4; $i++ ) { imagerectangle( $im, $x + 70 + $i * 180, $y + 80, $x + 170 + $i * 180, $y + 230, $bold ); imagerectangle( $im, $x + 70 + $i * 180, $y + 300, $x + 170 + $i * 180, $y + 450, $bold ); }
			imagefilledrectangle( $im, $x + 360, $y + 330, $x + 440, $y + 500, $hot );
			break;
		case 'bridge':
			$y0 = (int) ( $h * .62 );
			imageline( $im, 0, $y0, $w, $y0, $bold );
			for ( $x = 0; $x <= $w; $x += 20 ) { $yy = (int) ( $y0 - 260 * sin( M_PI * $x / $w ) ); imagesetpixel( $im, $x, $yy, $bold ); if ( 0 === $x % 80 ) { imageline( $im, $x, $yy, $x, $y0, $line ); } }
			imagearc( $im, (int) ( $w / 2 ), $y0, $w, 520, 180, 360, $hot );
			break;
		case 'person':
			$cx = (int) ( $w / 2 );
			imagefilledellipse( $im, $cx, (int) ( $h * .38 ), 260, 300, $bold );
			imagefilledellipse( $im, $cx, (int) ( $h * 1.02 ), 620, 560, $bold );
			break;
	}
	imagejpeg( $im, "$dir/$name.jpg", 78 );

	echo "$name.jpg\n";
}
