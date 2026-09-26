<?php
/**
 * Readable search addresses.
 *
 * Turns /?s=Paul into /search/Paul/, and lets the base word be changed from
 * the Search engines tab. WordPress already routes /search/term/; the theme
 * adds the redirect and the configurable base.
 *
 * Adapted from the Pretty Search Permalinks plugin by Angel Costa (GPLv2 or
 * later). The plugin's redirect dropped every query argument but the term;
 * the theme's search page filters by kind and section through query
 * arguments, so this version carries them across, along with the page
 * number.
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
	$options = boi_get_options();
	$base    = isset( $options['search_base'] ) ? sanitize_title( $options['search_base'] ) : '';

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
 * Redirects /?s=term to /base/term/, keeping every other query argument and
 * the page number, so filtered searches survive the move.
 *
 * @return void
 */
function boi_redirect_search() {
	global $wp_rewrite;

	if ( ! boi_pretty_search_enabled() || ! is_search() || is_admin() || ! $wp_rewrite->using_permalinks() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect of a public search.
	if ( ! isset( $_GET['s'] ) ) {
		return;
	}

	$term = get_query_var( 's' );

	if ( '' === trim( (string) $term ) ) {
		return;
	}

	$url = home_url( user_trailingslashit( boi_search_base() . '/' . rawurlencode( $term ) ) );

	$paged = (int) get_query_var( 'paged' );

	if ( $paged > 1 ) {
		$url = home_url( user_trailingslashit( boi_search_base() . '/' . rawurlencode( $term ) . '/page/' . $paged ) );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$args = wp_unslash( $_GET );
	unset( $args['s'], $args['paged'] );

	if ( ! empty( $args ) ) {
		$url = add_query_arg( array_map( 'rawurlencode', array_map( 'strval', array_filter( $args, 'is_scalar' ) ) ), $url );
	}

	wp_safe_redirect( $url, 301 );
	exit;
}
add_action( 'template_redirect', 'boi_redirect_search', 1 );

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
