<?php
/**
 * Fallback favicon.
 *
 * WordPress prints icon links only when a Site Icon is set under Appearance,
 * Customize, Site Identity. Until one is, the bundled mark is used, so a new
 * install never shows a blank tab: the icon links, the web app manifest,
 * the theme colour and /favicon.ico. A Site Icon set by the owner always wins.
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
		"<link rel=\"icon\" href=\"%1\$s\" type=\"image/svg+xml\" />\n<link rel=\"icon\" href=\"%2\$s\" sizes=\"32x32\" type=\"image/png\" />\n<link rel=\"apple-touch-icon\" href=\"%3\$s\" />\n<link rel=\"manifest\" href=\"%4\$s\" />\n<meta name=\"theme-color\" content=\"#0D2B4F\" />\n",
		esc_url( $base . 'favicon.svg' ),
		esc_url( $base . 'favicon-32.png' ),
		esc_url( $base . 'apple-touch-icon.png' ),
		esc_url( $base . 'site.webmanifest' )
	);
}
add_action( 'wp_head', 'boi_fallback_favicon', 99 );
add_action( 'login_head', 'boi_fallback_favicon', 99 );
add_action( 'admin_head', 'boi_fallback_favicon', 99 );

/**
 * Answers /favicon.ico with the bundled icon when no Site Icon is set.
 *
 * Browsers and crawlers ask for it on their own. It has a fixed address, so
 * it carries a day's cache and an ETag; a matching If-None-Match gets a 304.
 * With a Site Icon, or while the file is missing, WordPress redirects as it
 * would without the theme.
 *
 * @return void
 */
function boi_serve_favicon() {
	$file = BOI_DIR . '/assets/images/favicon.ico';
	if ( has_site_icon() || ! is_readable( $file ) ) {
		return;
	}
	$etag = '"' . md5_file( $file ) . '"';
	header( 'Content-Type: image/x-icon' );
	header( 'Cache-Control: public, max-age=86400' );
	header( 'ETag: ' . $etag );
	// If-None-Match may list several validators, weak ones (W/"...") or *.
	$sent = array_map(
		static function ( $v ) {
			return preg_replace( '#^W/#', '', trim( $v ) );
		},
		explode( ',', wp_unslash( (string) ( $_SERVER['HTTP_IF_NONE_MATCH'] ?? '' ) ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	);
	if ( in_array( '*', $sent, true ) || in_array( $etag, $sent, true ) ) {
		status_header( 304 );
		exit;
	}
	status_header( 200 );
	header( 'Content-Length: ' . (string) filesize( $file ) );
	// A HEAD request gets the headers alone.
	if ( 'HEAD' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	exit;
}
add_action( 'do_faviconico', 'boi_serve_favicon', 5 );

/**
 * WordPress ends a HEAD request before do_favicon, with an empty HTML answer.
 * template_redirect runs first, so a HEAD request gets the icon's headers too.
 *
 * @return void
 */
function boi_favicon_head_request() {
	if ( is_favicon() ) {
		boi_serve_favicon();
	}
}
add_action( 'template_redirect', 'boi_favicon_head_request', 0 );
