<?php
/**
 * The publishing schedule (SSOT D71).
 *
 * A seeded article may carry `publish_on` in inc/data/seed.json, an ISO 8601
 * time in UTC. When the theme creates the article before that time, it is
 * saved as Scheduled and WordPress publishes it on the day; the date stagger
 * in inc/reading.php leaves it alone. bin/check-content.py checks the dates.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The publishing schedule: slug to Unix time, from the `publish_on` field
 * (an ISO 8601 time in UTC) of the seeded articles that carry one (SSOT D71).
 *
 * @return int[]
 */
function boi_seed_schedule() {
	$schedule = array();
	foreach ( boi_seed_listicles() as $listicle ) {
		if ( ! empty( $listicle['publish_on'] ) ) {
			$time = strtotime( $listicle['publish_on'] );
			if ( false !== $time ) {
				$schedule[ $listicle['slug'] ] = $time;
			}
		}
	}
	return $schedule;
}

/**
 * Status and dates for a new seeded article. An article with a future
 * `publish_on` is created as Scheduled and WordPress publishes it on the day;
 * one whose date has passed is published with that date; one without a date
 * keeps its own status and takes the date it is created.
 *
 * @param array $listicle Seed entry.
 * @param int   $now      Current Unix time.
 * @return array Post fields.
 */
function boi_seed_post_timing( $listicle, $now ) {
	$status = isset( $listicle['status'] ) ? $listicle['status'] : 'publish';
	$time   = empty( $listicle['publish_on'] ) ? false : strtotime( $listicle['publish_on'] );
	if ( false === $time ) {
		return array( 'post_status' => $status );
	}
	$gmt = gmdate( 'Y-m-d H:i:s', $time );
	return array(
		'post_status'   => $time > $now ? 'future' : $status,
		'post_date'     => get_date_from_gmt( $gmt ),
		'post_date_gmt' => $gmt,
	);
}
