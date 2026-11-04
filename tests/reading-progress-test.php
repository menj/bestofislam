<?php
/**
 * Reading progress bar: the plugin's options carry over until the bar is
 * saved here, post types accept strings only, a static front page counts as
 * a page, and the bar re-measures when the page grows.
 *
 * Run: php tests/reading-progress-test.php
 *
 * @package BestOfIslam
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );

$GLOBALS['t_options'] = array();
function add_action() {}
function add_filter() {}
function __( $s ) { return $s; }
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['t_options'] ) ? $GLOBALS['t_options'][ $k ] : $d; }
function wp_parse_args( $a, $d ) { return array_merge( $d, $a ); }
function sanitize_hex_color( $c ) { return preg_match( '/^#[0-9a-f]{6}$/i', (string) $c ) ? $c : null; }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }

// boi_get_options() from inc/settings.php, lifted out without the rest.
$src = (string) file_get_contents( BOI_DIR . '/inc/settings.php' );
preg_match( '/^function boi_get_options\(.*?^}$/ms', $src, $m );
eval( "const BOI_OPTION_KEY = 'boi_settings';\n" . $m[0] ); // phpcs:ignore
require BOI_DIR . '/inc/reading-progress.php';

$fail = 0;
function check( $ok, $msg ) {
	global $fail;
	if ( ! $ok ) {
		echo "FAIL: $msg\n";
		++$fail;
	}
}

$GLOBALS['t_options']['rp_settings'] = array(
	'rp_field_height'    => '6',
	'rp_field_fg_color'  => '#aa0000',
	'rp_field_position'  => 'custom',
	'rp_field_templates' => array( 'single' => '1' ),
	'rp_field_posttypes' => array( 'post' => '1' ),
);
$o = boi_get_options();
check( 1 === $o['progress_enabled'] && 6 === $o['progress_height'] && '#aa0000' === $o['progress_color'] && 'top' === $o['progress_position'] && array( 'post' ) === $o['progress_types'], 'plugin options carry over' );
$GLOBALS['t_options']['boi_settings'] = array( 'progress_enabled' => 0 );
check( 0 === boi_get_options()['progress_enabled'], 'saved theme setting wins' );

$GLOBALS['t_options']['boi_settings'] = array( 'progress_types' => array( array( 'post' ), 7, 'page' ) );
check( array( 'page' ) === boi_progress_post_types(), 'only strings are post types' );

preg_match( '/function boi_progress_applies\(\) \{.*?\n\}/s', (string) file_get_contents( BOI_DIR . '/inc/reading-progress.php' ), $m );
check( isset( $m[0] ) && false === strpos( $m[0], 'is_front_page()' ), 'a static front page is a page' );
check( false !== strpos( (string) file_get_contents( BOI_DIR . '/assets/js/reading-progress.js' ), 'ResizeObserver' ), 're-measures on resize of the page' );

echo $fail ? "$fail failure(s)\n" : "reading-progress: all checks passed\n";
exit( $fail ? 1 : 0 );
