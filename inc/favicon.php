<?php
/**
 * Fallback favicon.
 *
 * WordPress prints icon links only when a Site Icon is set under Appearance,
 * Customize, Site Identity. Until one is, the bundled mark is used, so a new
 * install never shows a blank tab. A Site Icon set by the owner always wins.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints the bundled icon links when no Site Icon is set.
 *
 * @return void
 */
function boi_fallback_favicon() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$base = BOI_URI . '/assets/images/';

	printf(
		"<link rel=\"icon\" href=\"%1\$s\" type=\"image/svg+xml\" />\n<link rel=\"icon\" href=\"%2\$s\" sizes=\"32x32\" type=\"image/png\" />\n<link rel=\"apple-touch-icon\" href=\"%3\$s\" />\n",
		esc_url( $base . 'favicon.svg' ),
		esc_url( $base . 'favicon-32.png' ),
		esc_url( $base . 'apple-touch-icon.png' )
	);
}
add_action( 'wp_head', 'boi_fallback_favicon', 99 );
add_action( 'login_head', 'boi_fallback_favicon', 99 );
add_action( 'admin_head', 'boi_fallback_favicon', 99 );
