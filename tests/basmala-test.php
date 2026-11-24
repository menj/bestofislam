<?php
/**
 * The Basmala (inc/basmala.php): it opens the main text of every article,
 * above the abstract, never on a page, once, and not when switched off. The
 * image is WebP. WordPress is replaced by stubs.
 *
 * Run: php tests/basmala-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );
define( 'BOI_URI', '' );

$GLOBALS['t_single']  = true;
$GLOBALS['t_options'] = array();

function add_filter() {}
function add_action() {}
function esc_url( $s ) { return $s; }
function esc_attr__( $s ) { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function is_singular( $t = '' ) { return $GLOBALS['t_single']; }
function boi_anatomy_is_main() { return true; }
function boi_get_options() { return array_merge( array( 'basmala' => 1 ), $GLOBALS['t_options'] ); }

require BOI_DIR . '/inc/basmala.php';

$fails = 0;
$count = 0;
function check( $ok, $label ) {
	global $fails, $count;
	++$count;
	if ( ! $ok ) {
		++$fails;
		echo "FAIL: $label\n";
	}
}

$file = BOI_DIR . '/assets/images/basmala.webp';
check( is_readable( $file ) && 'RIFF' === substr( file_get_contents( $file ), 0, 4 ) && 'WEBP' === substr( file_get_contents( $file ), 8, 4 ), 'the image is a WebP file' );

$abstract = '<aside class="boi-abstract boi-abstract--ruled" id="abstract"><p>Abstract.</p></aside><p>Opening.</p>';
$out      = boi_basmala_filter( $abstract );
check( 0 === strpos( $out, '<div class="boi-basmala">' ), 'the Basmala opens the main text, above the abstract' );
check( false !== strpos( $out, 'basmala.webp' ) && false !== strpos( $out, 'alt="In the name of God' ), 'the WebP image with its meaning as alt text' );
check( $out === boi_basmala_filter( $out ), 'a second pass adds nothing' );
check( false === strpos( $out, '<p class="boi-basmala' ), 'no paragraph, so the drop cap stays on the opening' );

$GLOBALS['t_options'] = array( 'basmala' => 0 );
check( $abstract === boi_basmala_filter( $abstract ), 'nothing when switched off' );
$GLOBALS['t_options'] = array();
$GLOBALS['t_single']  = false;
check( $abstract === boi_basmala_filter( $abstract ), 'nothing on a page' );

echo $fails ? "$fails of $count checks failed\n" : "All $count checks passed\n";
exit( $fails ? 1 : 0 );
