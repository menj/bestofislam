<?php
/**
 * Small shared helpers.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Runs a callback while holding a named database lock, so a read-modify-write
 * on an option or counter cannot interleave with another request. If the lock
 * cannot be taken within three seconds, or the database does not offer
 * locks, the callback still runs: the lock narrows a race, it does not gate
 * the feature.
 *
 * @param string   $name     Lock name.
 * @param callable $callback Work to do.
 * @return mixed The callback's result.
 */
function boi_with_lock( $name, $callback ) {
	global $wpdb;

	$lock = 'boi_' . md5( $wpdb->prefix . $name );
	$got  = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK( %s, 3 )', $lock ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

	try {
		return call_user_func( $callback );
	} finally {
		if ( $got ) {
			$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK( %s )', $lock ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
	}
}

/**
 * JSON for a JSON-LD script element. Angle brackets, ampersands and quotes
 * are escaped so a title containing a closing script tag cannot end the block.
 *
 * @param mixed $data Data to encode.
 * @return string
 */
function boi_json_ld( $data ) {
	return (string) wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
}
