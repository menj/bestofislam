<?php
/**
 * Rank Math titles and resized image sizes, with WordPress stubbed.
 *
 * Checks that a custom Rank Math title keeps its wording as the page part,
 * that date archives get a page part of their own, and that a resized media
 * library URL is measured as its size.
 *
 * Run: php tests/seo-title-image-size-test.php
 *
 * @package BestOfIslam
 */

define( 'BOI_DIR', dirname( __DIR__ ) );

$GLOBALS['ctx'] = array();
function ctx( $k ) { return ! empty( $GLOBALS['ctx'][ $k ] ); }
function __( $s ) { return $s; }
function apply_filters( $h, $v ) { return $v; }
function wp_strip_all_tags( $s ) { return strip_tags( (string) $s ); }
function get_bloginfo() { return 'Best of Islam'; }
function is_front_page() { return false; }
function is_home() { return false; }
function is_tax() { return false; }
function is_category() { return false; }
function is_tag() { return false; }
function is_post_type_archive() { return false; }
function is_author() { return false; }
function is_search() { return false; }
function is_404() { return false; }
function is_singular( $t = '' ) { return ctx( 'singular' ); }
function is_day() { return ctx( 'day' ); }
function is_month() { return ctx( 'month' ); }
function is_year() { return ctx( 'year' ); }
function get_the_date( $f = '' ) { return 'F Y' === $f ? 'March 2024' : ( 'Y' === $f ? '2024' : 'March 5, 2024' ); }
function get_query_var() { return 0; }
function get_queried_object() { return $GLOBALS['ctx']['post'] ?? null; }
function get_post_meta() { return $GLOBALS['ctx']['custom'] ?? ''; }
function single_post_title() { return 'Seeded post title'; }
function boi_seed_seo_title() { return 'Seed short title'; }
function boi_title_separator() { return '|'; }
function boi_title_max_length() { return 60; }
function boi_fit_title( $t, $b ) { return mb_substr( $t, 0, $b ); }
function wp_basename( $p ) { return basename( $p ); }
function wp_parse_url( $u, $c ) { return parse_url( $u, $c ); }
function attachment_url_to_postid( $u ) { return '/up/photo.jpg' === parse_url( $u, PHP_URL_PATH ) ? 7 : 0; }
function wp_get_attachment_metadata() {
	return array( 'width' => 2000, 'height' => 1000, 'file' => '2024/photo.jpg', 'sizes' => array( 'medium' => array( 'file' => 'photo-300x150.jpg', 'width' => 300, 'height' => 150 ) ) );
}
class WP_Post { public $ID = 1; }

foreach ( array( 'inc/seo.php' => array( 'boi_title_parts' ), 'inc/rank-math.php' => array( 'boi_rank_math_title' ), 'inc/blocks.php' => array( 'boi_attachment_image_size' ) ) as $file => $fns ) {
	$src = (string) file_get_contents( BOI_DIR . '/' . $file );
	foreach ( $fns as $fn ) {
		if ( ! preg_match( '/^function ' . $fn . '\(.*?^}$/ms', $src, $m ) ) {
			fwrite( STDERR, "Cannot find $fn\n" );
			exit( 1 );
		}
		eval( $m[0] ); // phpcs:ignore
	}
}

$fail = 0;
function check( $name, $got, $want ) {
	global $fail;
	if ( $got !== $want ) {
		$fail++;
		echo "FAIL $name: got " . var_export( $got, true ) . ', want ' . var_export( $want, true ) . "\n";
	} else {
		echo "ok   $name\n";
	}
}

$GLOBALS['ctx'] = array( 'singular' => true, 'post' => new WP_Post(), 'custom' => '%title%' );
check( 'custom Rank Math title wins', boi_rank_math_title( 'Owner wording | Best of Islam' ), 'Owner wording | Best of Islam' );
$GLOBALS['ctx'] = array( 'singular' => true, 'post' => new WP_Post() );
check( 'seeded post without custom title', boi_rank_math_title( 'x' ), 'Seed short title | Best of Islam' );
$GLOBALS['ctx'] = array( 'year' => true );
check( 'year archive', boi_rank_math_title( 'x' ), '2024 | Best of Islam' );
$GLOBALS['ctx'] = array( 'month' => true );
check( 'month archive', boi_rank_math_title( 'x' ), 'March 2024 | Best of Islam' );
$GLOBALS['ctx'] = array( 'day' => true );
check( 'day archive', boi_rank_math_title( 'x' ), 'March 5, 2024 | Best of Islam' );

check( 'original size', boi_attachment_image_size( 'https://e.org/up/photo.jpg' ), array( 2000, 1000 ) );
check( 'resized size', boi_attachment_image_size( 'https://e.org/up/photo-300x150.jpg' ), array( 300, 150 ) );
check( 'unknown size variant', boi_attachment_image_size( 'https://e.org/up/photo-999x999.jpg' ), null );
check( 'not in library', boi_attachment_image_size( 'https://e.org/other.jpg' ), null );

exit( $fail ? 1 : 0 );
