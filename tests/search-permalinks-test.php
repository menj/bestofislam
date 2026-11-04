<?php
/**
 * Readable search addresses: /?s=term redirects with a 301 to /base/term/,
 * keeping the page number and filters; an empty search is not redirected;
 * the base comes from the settings or, on first use, the plugin's option.
 *
 * Run: php tests/search-permalinks-test.php
 *
 * @package BestOfIslam
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );
const BOI_OPTION_KEY = 'boi_settings';

class WP_Rewrite {
	public $search_base = 'search';
	public function using_permalinks() { return true; }
}
class Redirected extends Exception {
	public $url; public $status;
	public function __construct( $url, $status ) { $this->url = $url; $this->status = $status; parent::__construct( 'redirect' ); }
}

$GLOBALS['wp_rewrite'] = new WP_Rewrite();
$GLOBALS['t_options']  = array();
$GLOBALS['t_vars']     = array();

function add_action() {}
function add_filter() {}
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['t_options'] ) ? $GLOBALS['t_options'][ $k ] : $d; }
function boi_get_options() { $s = get_option( BOI_OPTION_KEY, array() ); return array_merge( array( 'search_pretty' => 1, 'search_base' => 'search' ), $s ); }
function sanitize_title( $s ) { return strtolower( preg_replace( '/[^A-Za-z0-9\-]+/', '-', trim( (string) $s ) ) ); }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
function __( $s ) { return $s; }
function is_search() { return true; }
function is_admin() { return false; }
function get_query_var( $k ) { return isset( $GLOBALS['t_vars'][ $k ] ) ? $GLOBALS['t_vars'][ $k ] : ''; }
function home_url( $p = '' ) { return 'https://example.org/' . ltrim( $p, '/' ); }
function user_trailingslashit( $p ) { return rtrim( $p, '/' ) . '/'; }
function wp_unslash( $v ) { return $v; }
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query( $args ); }
function wp_safe_redirect( $url, $status = 302 ) { throw new Redirected( $url, $status ); }

require BOI_DIR . '/inc/search-permalinks.php';

$fail = 0;
function check( $ok, $msg ) {
	global $fail;
	if ( ! $ok ) {
		echo "FAIL: $msg\n";
		++$fail;
	}
}

function run_redirect( $get, $vars ) {
	$_GET              = $get;
	$GLOBALS['t_vars'] = $vars;
	try {
		boi_redirect_search();
	} catch ( Redirected $r ) {
		return array( $r->url, $r->status );
	}
	return null;
}

// Base: the plugin's option is imported when the theme has none.
$GLOBALS['t_options']['wpseosearch_base'] = 'find';
check( 'find' === boi_search_base(), 'imports the plugin base' );
$GLOBALS['t_options'][ BOI_OPTION_KEY ] = array( 'search_base' => 'Zoek' );
check( 'zoek' === boi_search_base(), 'theme base wins and is sanitised' );
$GLOBALS['t_options'] = array();
check( 'search' === boi_search_base(), 'default base' );

boi_apply_search_base();
check( 'search' === $GLOBALS['wp_rewrite']->search_base, 'base applied to rewrite rules' );

$r = run_redirect( array( 's' => 'Paul' ), array( 's' => 'Paul' ) );
check( $r && 'https://example.org/search/Paul/' === $r[0] && 301 === $r[1], '/?s=Paul gives a 301 to /search/Paul/' );

$r = run_redirect( array( 's' => 'Paul', 'paged' => '2' ), array( 's' => 'Paul', 'paged' => 2 ) );
check( $r && 'https://example.org/search/Paul/page/2/' === $r[0], 'page 2 keeps its page' );

$r = run_redirect( array( 's' => 'two words', 'kind' => 'glossary' ), array( 's' => 'two words' ) );
check( $r && 'https://example.org/search/two%20words/?kind=glossary' === $r[0], 'filters and encoding kept' );

$r = run_redirect( array( 's' => 'Paul', 'post_type' => array( 'post', 'page' ), 'bad' => array( array() ) ), array( 's' => 'Paul' ) );
check( $r && 'https://example.org/search/Paul/?post_type%5B0%5D=post&post_type%5B1%5D=page' === $r[0], 'array arguments kept intact' );

// Bases the site already routes are refused; the cleaner is sanitize_key.
check( '' !== boi_search_base_problem( 'topic' ) && '' !== boi_search_base_problem( 'glossary' ), 'reserved bases refused' );
check( '' === boi_search_base_problem( 'zoek' ), 'free base accepted' );
$GLOBALS['t_options'][ BOI_OPTION_KEY ] = array( 'search_base' => 'Zoek Hier!' );
check( 'zoekhier' === boi_search_base(), 'base cleaned with sanitize_key' );
$GLOBALS['t_options'] = array();

check( null === run_redirect( array( 's' => '' ), array( 's' => '' ) ), 'empty search not redirected' );
check( null === run_redirect( array( 's' => '   ' ), array( 's' => '   ' ) ), 'blank search not redirected' );
check( null === run_redirect( array(), array( 's' => 'Paul' ) ), 'already readable address not redirected' );

$GLOBALS['t_options'][ BOI_OPTION_KEY ] = array( 'search_pretty' => 0 );
check( null === run_redirect( array( 's' => 'Paul' ), array( 's' => 'Paul' ) ), 'switched off: no redirect' );
$GLOBALS['t_options'] = array();

// The flush marker clears only once the rules hold the search rule.
$src = (string) file_get_contents( BOI_DIR . '/inc/settings.php' );
preg_match( '/^function boi_maybe_flush_rewrites\(.*?^}$/ms', $src, $m1 );
preg_match( '/^function boi_search_rules_ready\(.*?^}$/ms', $src, $m2 );
eval( $m1[0] . $m2[0] ); // phpcs:ignore
function flush_rewrite_rules() { $GLOBALS['t_flushes'] = ( $GLOBALS['t_flushes'] ?? 0 ) + 1; }
function delete_option( $k ) { unset( $GLOBALS['t_options'][ $k ] ); }
$GLOBALS['t_options'] = array( 'boi_flush_rewrites' => 1, 'rewrite_rules' => array( 'topic/(.+)/?$' => 'x' ) );
boi_maybe_flush_rewrites();
check( 1 === $GLOBALS['t_flushes'] && ! empty( $GLOBALS['t_options']['boi_flush_rewrites'] ), 'marker kept while the search rule is missing' );
$GLOBALS['t_options']['rewrite_rules']['search/(.+)/?$'] = 'index.php?s=$matches[1]';
boi_maybe_flush_rewrites();
check( empty( $GLOBALS['t_options']['boi_flush_rewrites'] ), 'marker cleared once the rule is there' );
boi_maybe_flush_rewrites();
check( 2 === $GLOBALS['t_flushes'], 'no flush without the marker' );
$GLOBALS['t_options'] = array();

eval( "function wpseosearch_base() {}" ); // phpcs:ignore -- declared late on purpose: a top-level declaration is hoisted.
check( ! boi_pretty_search_enabled(), 'stands aside for the plugin' );

echo $fail ? "$fail failure(s)\n" : "search-permalinks: all checks passed\n";
exit( $fail ? 1 : 0 );
