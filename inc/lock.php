<?php
/**
 * Best of Islam: a small lock across requests, kept in wp_options (the main
 * site's, on a network).
 *
 * Taking it is one INSERT IGNORE of a unique option name, which only one
 * request can win. (add_option() is no lock: it reads first and then
 * inserts with ON DUPLICATE KEY UPDATE, so two requests can both succeed.)
 * Each holder writes its own token, so a stale lock is taken over with an
 * UPDATE that only succeeds while the old token is still there, and a
 * release deletes the row only while it still holds the releaser's token. Used by the dev-file cleanup and the .htaccess sync.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The table the locks live in: the options table of the network's main
 * site, so on a multisite network every site that runs the theme shares
 * one lock, as they share the theme files and .htaccess the lock guards.
 *
 * @return string
 */
function boi_lock_table() {
	global $wpdb;
	if ( function_exists( 'is_multisite' ) && is_multisite() && function_exists( 'get_main_site_id' ) ) {
		return $wpdb->get_blog_prefix( get_main_site_id() ) . 'options';
	}
	return $wpdb->options;
}

/**
 * Takes a lock.
 *
 * @param string $name Option name of the lock.
 * @param int    $ttl  Seconds after which a held lock counts as stale.
 * @return string The holder's token, or '' when another request holds it.
 */
function boi_lock_take( $name, $ttl ) {
	global $wpdb;
	$table = boi_lock_table();
	$token = time() . '|' . wp_generate_password( 16, false );

	if ( boi_lock_insert( $name, $token ) ) {
		return $token;
	}

	$held = (string) $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$table} WHERE option_name = %s", $name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( '' === $held ) {
		// Released between the two statements: one more try.
		return boi_lock_insert( $name, $token ) ? $token : '';
	}
	if ( time() - (int) strtok( $held, '|' ) < $ttl ) {
		return '';
	}

	// Stale: take it over only if no one else has meanwhile.
	$won = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET option_value = %s WHERE option_name = %s AND option_value = %s", $token, $name, $held ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	wp_cache_delete( $name, 'options' );
	return 1 === (int) $won ? $token : '';
}

/**
 * Releases a lock, if it is still this holder's.
 *
 * @param string $name  Option name of the lock.
 * @param string $token Token from boi_lock_take().
 */
function boi_lock_release( $name, $token ) {
	global $wpdb;
	$table = boi_lock_table();
	if ( '' === (string) $token ) {
		return;
	}
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE option_name = %s AND option_value = %s", $name, $token ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	wp_cache_delete( $name, 'options' );
	wp_cache_delete( 'notoptions', 'options' );
}

/**
 * Inserts the lock row unless it exists.
 *
 * @param string $name  Option name.
 * @param string $token Value.
 * @return bool Whether this call inserted it.
 */
function boi_lock_insert( $name, $token ) {
	global $wpdb;
	$table = boi_lock_table();
	$rows = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$table} (option_name, option_value, autoload) VALUES (%s, %s, 'no')", $name, $token ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	wp_cache_delete( $name, 'options' );
	wp_cache_delete( 'notoptions', 'options' );
	return 1 === (int) $rows;
}
