<?php
/**
 * Unlisted items: kept out of listing queries, adjacent links and page
 * lists, left alone on their own address and in the admin, and marked
 * noindex.
 *
 * Run: php tests/unlist-test.php
 *
 * @package BestOfIslam
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );

$GLOBALS['t_options'] = array();
$GLOBALS['t_admin']   = false;
$GLOBALS['t_single']  = false;
$GLOBALS['t_queried'] = 0;
$GLOBALS['t_filters'] = array();

class WP_Post { public $ID; public function __construct( $id ) { $this->ID = $id; } }
class WP_Query {
	public $vars = array();
	public $singular = false;
	public function __construct( $vars = array(), $singular = false ) { $this->vars = $vars; $this->singular = $singular; }
	public function get( $k ) { return isset( $this->vars[ $k ] ) ? $this->vars[ $k ] : ''; }
	public function set( $k, $v ) { $this->vars[ $k ] = $v; }
	public function is_singular() { return $this->singular; }
	public function is_main_query() { return true; }
}

function add_action() {}
function add_filter( $hook, $cb ) { $GLOBALS['t_filters'][] = $hook; }
function add_shortcode() {}
function apply_filters( $hook, $value ) { return $value; }
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['t_options'] ) ? $GLOBALS['t_options'][ $k ] : $d; }
function update_option( $k, $v ) { $GLOBALS['t_options'][ $k ] = $v; return true; }
function is_admin() { return $GLOBALS['t_admin']; }
function wp_doing_ajax() { return false; }
function current_user_can() { return false; }
function is_singular() { return $GLOBALS['t_single']; }
function get_queried_object_id() { return $GLOBALS['t_queried']; }
function get_post_types() { return array( 'post' => 'post', 'page' => 'page' ); }

require BOI_DIR . '/inc/unlist.php';

$fail = 0;
function check( $ok, $msg ) {
	global $fail;
	if ( ! $ok ) {
		echo "FAIL: $msg\n";
		++$fail;
	}
}

// Import from the plugin's option on first use.
$GLOBALS['t_options']['unlist_posts'] = array( '7', 9 );
check( array( 7, 9 ) === boi_unlisted_ids(), 'imports the plugin list' );
check( boi_is_unlisted( 7 ) && boi_is_unlisted( new WP_Post( 9 ) ) && ! boi_is_unlisted( 8 ), 'boi_is_unlisted' );

// A listing query (home, archive, feed, search, sitemap, REST list) loses them.
$q = new WP_Query( array( 'post__not_in' => array( 3 ) ) );
boi_unlist_filter_query( $q );
check( array( 3, 7, 9 ) === $q->get( 'post__not_in' ), 'listing query excludes unlisted ids' );

// A query that names its posts keeps only the listed ones.
$q = new WP_Query( array( 'post__in' => array( 7, 8 ) ) );
boi_unlist_filter_query( $q );
check( array( 8 ) === $q->get( 'post__in' ), 'post__in drops unlisted ids' );

$q = new WP_Query( array( 'post__in' => array( 7 ) ) );
boi_unlist_filter_query( $q );
check( array( 0 ) === $q->get( 'post__in' ), 'post__in of only unlisted ids matches nothing' );

// The item's own address is untouched.
$q = new WP_Query( array( 'p' => 7 ), true );
boi_unlist_filter_query( $q );
check( '' === $q->get( 'post__not_in' ), 'singular query left alone' );

// The admin sees everything.
$GLOBALS['t_admin'] = true;
$q = new WP_Query();
boi_unlist_filter_query( $q );
check( '' === $q->get( 'post__not_in' ), 'admin query left alone' );
$GLOBALS['t_admin'] = false;

// Previous and next links, and page lists.
check( ' AND p.ID NOT IN (7,9)' === boi_unlist_adjacent( '' ), 'adjacent links exclude unlisted ids' );
check( array( 1, 7, 9 ) === boi_unlist_page_lists( array( 1 ) ), 'page lists exclude unlisted ids' );

// Robots: noindex on the item, nothing elsewhere.
$GLOBALS['t_single']  = true;
$GLOBALS['t_queried'] = 7;
$r = boi_unlist_robots( array( 'index' => true, 'max-image-preview' => 'large' ) );
check( ! empty( $r['noindex'] ) && ! isset( $r['index'] ), 'unlisted item is noindex' );
check( in_array( 'wpseo_robots_array', $GLOBALS['t_filters'], true ), 'Yoast robots emptied' );
$GLOBALS['t_queried'] = 8;
$r = boi_unlist_robots( array( 'index' => true ) );
check( empty( $r['noindex'] ), 'listed item stays indexable' );

// The plugin standing in turns the theme's version off.
eval( 'class Unlist_Posts {}' ); // phpcs:ignore
check( ! boi_unlist_enabled() && ! boi_is_unlisted( 7 ), 'stands aside for the plugin' );
check( '' === boi_unlist_adjacent( '' ), 'no adjacent filter while the plugin is active' );

echo $fail ? "$fail failure(s)\n" : "unlist: all checks passed\n";
exit( $fail ? 1 : 0 );
