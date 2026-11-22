<?php
/**
 * The preferred-source invitation (inc/features.php): the Google deeplink is
 * built from the site's domain, "www." is dropped, and a site installed in a
 * subfolder or on a bare host gets no link. WordPress is replaced by stubs.
 *
 * Run: php tests/preferred-source-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['t_home'] = 'https://bestofislam.org/';
function add_action() {}
function add_filter() {}
function add_shortcode() {}
function register_block_type() {}
function __( $s ) { return $s; }
function home_url() { return $GLOBALS['t_home']; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }

require dirname( __DIR__ ) . '/inc/features.php';

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

check( 'https://www.google.com/preferences/source?q=bestofislam.org' === boi_preferred_source_url(), 'the domain at the root' );
$GLOBALS['t_home'] = 'https://www.bestofislam.org';
check( 'https://www.google.com/preferences/source?q=bestofislam.org' === boi_preferred_source_url(), 'www. is dropped' );
$GLOBALS['t_home'] = 'https://read.bestofislam.org/';
check( 'https://www.google.com/preferences/source?q=read.bestofislam.org' === boi_preferred_source_url(), 'a subdomain is eligible' );
$GLOBALS['t_home'] = 'https://example.org/blog/';
check( '' === boi_preferred_source_url(), 'a subfolder is not eligible' );
$GLOBALS['t_home'] = 'http://localhost/';
check( '' === boi_preferred_source_url(), 'a bare host gets no link' );
$GLOBALS['t_home'] = 'http://192.168.1.10/';
check( '' === boi_preferred_source_url(), 'an IPv4 address gets no link' );
$GLOBALS['t_home'] = 'http://[2001:db8::1]/';
check( '' === boi_preferred_source_url(), 'an IPv6 address gets no link' );

echo $fails ? "$fails of $count checks failed\n" : "All $count checks passed\n";
exit( $fails ? 1 : 0 );
