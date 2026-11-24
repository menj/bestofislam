<?php
/**
 * boi_stagger_seed_dates() in inc/reading.php: a new site dates its seeded
 * articles by the reading order once and then freezes them; a site staggered
 * by an earlier version keeps its dates except the recent articles, which
 * move to today in the order they were created; later releases and later
 * changes to the reading order redate nothing; edited articles keep their
 * dates.
 *
 * Run: php tests/reading-dates-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'DAY_IN_SECONDS', 86400 );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'OBJECT', 'OBJECT' );

$GLOBALS['t_o']     = array();
$GLOBALS['t_posts'] = array();
$GLOBALS['t_set']   = array();
function __( $s ) { return $s; }
function add_action() {}
function add_filter() {}
function register_block_type() {}
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['t_o'] ) ? $GLOBALS['t_o'][ $k ] : $d; }
function update_option( $k, $v ) { $GLOBALS['t_o'][ $k ] = $v; return true; }
function delete_option( $k ) { unset( $GLOBALS['t_o'][ $k ] ); return true; }
function get_page_by_path( $slug ) { return $GLOBALS['t_posts'][ $slug ] ?? null; }
function boi_content_fingerprint( $c ) { return md5( $c ); }
function get_date_from_gmt( $g ) { return $g; }
function boi_update_post( $arr ) {
	$GLOBALS['t_set'][ $arr['ID'] ] = strtotime( $arr['post_date_gmt'] . ' UTC' );
	foreach ( $GLOBALS['t_posts'] as $p ) {
		if ( $p->ID === $arr['ID'] ) {
			$p->date = $GLOBALS['t_set'][ $arr['ID'] ];
		}
	}
}
require dirname( __DIR__ ) . '/inc/reading.php';

$fail = 0;
function check( $ok, $label ) {
	global $fail;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . $label . "\n";
	$fail += $ok ? 0 : 1;
}

/**
 * A site with every article in the reading order, created in that order,
 * plus the recent articles created last (highest IDs), all unedited.
 */
function t_site( $edited = array() ) {
	$GLOBALS['t_o']     = array();
	$GLOBALS['t_posts'] = array();
	$GLOBALS['t_set']   = array();
	$hashes             = array();
	$id                 = 0;
	$recent             = boi_dates_recent_slugs();
	$order              = array_keys( boi_reading_positions() );
	foreach ( array_merge( array_diff( $order, $recent ), $recent ) as $slug ) {
		$p = (object) array( 'ID' => ++$id, 'post_status' => 'publish', 'post_content' => "body of $slug", 'date' => 1000000000 + $id );
		$GLOBALS['t_posts'][ $slug ] = $p;
		$hashes[ $slug ]             = in_array( $slug, $edited, true ) ? 'edited' : md5( $p->post_content );
	}
	$GLOBALS['t_o']['boi_article_hashes'] = $hashes;
	return $order;
}
function t_newest( $n ) {
	$all = $GLOBALS['t_posts'];
	uasort( $all, function ( $a, $b ) { return $b->date <=> $a->date ?: $b->ID <=> $a->ID; } );
	return array_slice( array_keys( $all ), 0, $n );
}

// 1. A new site: the reading order, one day apart, ending now; then frozen.
$order = t_site();
boi_stagger_seed_dates();
$last  = end( $order );
check( count( $GLOBALS['t_set'] ) === count( $order ) && array( $last ) === t_newest( 1 ) && abs( $GLOBALS['t_posts'][ $last ]->date - time() ) < 5, 'a new site dates every article by the reading order, the last one now' );
check( DAY_IN_SECONDS === $GLOBALS['t_posts'][ $order[1] ]->date - $GLOBALS['t_posts'][ $order[0] ]->date, 'one day apart' );
check( false !== get_option( 'boi_dates_frozen' ), 'and the dates are frozen' );

// 2. A later release adds an article: it keeps its own date, and nothing else moves.
$GLOBALS['t_set'] = array();
$GLOBALS['t_posts']['a-new-article'] = (object) array( 'ID' => 9999, 'post_status' => 'publish', 'post_content' => 'x', 'date' => time() + 10 );
boi_stagger_seed_dates();
check( array() === $GLOBALS['t_set'] && array( 'a-new-article' ) === t_newest( 1 ), 'a later article keeps its creation date and leads; nothing is redated' );

// 3. A site staggered by an earlier version: the recent articles move to
// today in the order they were created, everything else keeps its date.
t_site();
$GLOBALS['t_o']['boi_dates_signature'] = 'old';
$before = array_map( function ( $p ) { return $p->date; }, $GLOBALS['t_posts'] );
boi_stagger_seed_dates();
$recent = boi_dates_recent_slugs();
check( array_reverse( $recent ) === t_newest( count( $recent ) ), 'an upgraded site: the recent articles lead, newest created first' );
$kept  = true;
foreach ( $GLOBALS['t_posts'] as $slug => $p ) {
	if ( ! in_array( $slug, $recent, true ) && $p->date !== $before[ $slug ] ) {
		$kept = false;
	}
}
check( $kept && count( $GLOBALS['t_set'] ) === count( $recent ), 'every other article keeps the date it had' );
check( false === get_option( 'boi_dates_signature' ) && false !== get_option( 'boi_dates_frozen' ), 'the old signature is gone and the dates are frozen' );

// 4. An edited recent article keeps its date.
t_site( array( 'scrolls-of-abraham' ) );
$GLOBALS['t_o']['boi_dates_signature'] = 'old';
$was = $GLOBALS['t_posts']['scrolls-of-abraham']->date;
boi_stagger_seed_dates();
check( $was === $GLOBALS['t_posts']['scrolls-of-abraham']->date && count( $GLOBALS['t_set'] ) === count( $recent ) - 1, 'an edited article keeps its date' );

// 5. Run again: nothing changes.
$GLOBALS['t_set'] = array();
boi_stagger_seed_dates();
check( array() === $GLOBALS['t_set'], 'a second run redates nothing' );

exit( $fail ? 1 : 0 );
