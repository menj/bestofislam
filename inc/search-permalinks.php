<?php
/**
 * Readable search addresses.
 *
 * Lets the base word of /search/term/ be changed from the Search engines
 * tab, and keeps robots.txt in step with it. WordPress already routes
 * /search/term/ itself. The theme does not redirect /?s= addresses, or any
 * other address: redirects are not the theme's job.
 *
 * The configurable base is adapted from the Pretty Search Permalinks plugin
 * by Angel Costa (GPLv2 or later).
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether readable search addresses are on. They stand aside while the
 * original plugin is active.
 *
 * @return bool
 */
function boi_pretty_search_enabled() {
	if ( function_exists( 'wpseosearch_base' ) ) {
		return false;
	}

	$options = boi_get_options();

	return ! empty( $options['search_pretty'] );
}

/**
 * The search base word, importing the plugin's saved base on first use.
 *
 * @return string
 */
function boi_search_base() {
	// boi_get_options() always fills in a default, so read what is stored to
	// tell "never set" from "set": only then is the plugin's base imported.
	$stored = get_option( BOI_OPTION_KEY, array() );
	$base   = is_array( $stored ) && isset( $stored['search_base'] ) ? sanitize_title( $stored['search_base'] ) : '';

	if ( '' === $base ) {
		$base = sanitize_title( (string) get_option( 'wpseosearch_base', 'search' ) );
	}

	return '' !== $base ? $base : 'search';
}

/**
 * Applies the base to the rewrite rules.
 *
 * @return void
 */
function boi_apply_search_base() {
	global $wp_rewrite;

	if ( boi_pretty_search_enabled() && $wp_rewrite instanceof WP_Rewrite ) {
		$wp_rewrite->search_base = boi_search_base();
	}
}
add_action( 'init', 'boi_apply_search_base', 1 );

/**
 * Keeps the robots rules in step with a changed base.
 *
 * @param string $output Robots.txt output.
 * @return string
 */
function boi_search_base_robots( $output ) {
	$base = boi_search_base();

	if ( boi_pretty_search_enabled() && 'search' !== $base && false === strpos( $output, "Disallow: /{$base}/" ) ) {
		$output .= "Disallow: /{$base}/\n";
	}

	return $output;
}
add_filter( 'robots_txt', 'boi_search_base_robots', 20 );
