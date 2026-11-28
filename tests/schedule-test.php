<?php
/**
 * The publishing schedule (SSOT D71): boi_seed_post_timing() in
 * inc/schedule.php creates an article with a future `publish_on` as Scheduled
 * on that date, publishes one whose date has passed with that date, and
 * leaves an article without one to its own status and today's date. The
 * seed data itself is checked by bin/check-content.py. WordPress is
 * replaced by stubs.
 *
 * Run: php tests/schedule-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );

$GLOBALS['t_seed'] = array();
function boi_seed_listicles() { return $GLOBALS['t_seed']; }
function get_date_from_gmt( $g ) { return $g; }

require BOI_DIR . '/inc/schedule.php';

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

$now               = strtotime( '2026-11-26T12:00:00Z' );
$GLOBALS['t_seed'] = array(
	array( 'slug' => 'future', 'publish_on' => '2026-12-01T01:00:00Z' ),
	array( 'slug' => 'past', 'publish_on' => '2026-11-01T01:00:00Z' ),
	array( 'slug' => 'plain' ),
	array( 'slug' => 'draft', 'status' => 'draft' ),
	array( 'slug' => 'broken', 'publish_on' => 'not a date' ),
);

$s = boi_seed_schedule();
check( array( 'future', 'past' ) === array_keys( $s ), 'the schedule holds the articles with a valid publish_on' );

$t = boi_seed_post_timing( $GLOBALS['t_seed'][0], $now );
check( 'future' === $t['post_status'] && '2026-12-01 01:00:00' === $t['post_date_gmt'], 'a future date: Scheduled, on that date' );
$t = boi_seed_post_timing( $GLOBALS['t_seed'][1], $now );
check( 'publish' === $t['post_status'] && '2026-11-01 01:00:00' === $t['post_date_gmt'], 'a past date: published with that date' );
$t = boi_seed_post_timing( $GLOBALS['t_seed'][2], $now );
check( array( 'post_status' => 'publish' ) === $t, 'no date: published now' );
$t = boi_seed_post_timing( $GLOBALS['t_seed'][3], $now );
check( array( 'post_status' => 'draft' ) === $t, 'a draft stays a draft' );
$t = boi_seed_post_timing( $GLOBALS['t_seed'][4], $now );
check( array( 'post_status' => 'publish' ) === $t, 'an unreadable date is ignored' );

// The bundled seed: every scheduled time is UTC on the hour, in order of the slots.
$GLOBALS['t_seed'] = json_decode( file_get_contents( BOI_DIR . '/inc/data/seed.json' ), true )['listicles'];
$bundled           = boi_seed_schedule();
check( count( $bundled ) >= 10, 'the bundled seed schedules the new articles' );
foreach ( $bundled as $slug => $time ) {
	check( 0 === $time % 3600, "$slug: on the hour" );
}

echo $fails ? "$fails of $count checks failed\n" : "All $count checks passed\n";
exit( $fails ? 1 : 0 );
