<?php
/**
 * Redirects from the site's earlier date-based permalink structure.
 *
 * The site ran with WordPress's default /%year%/%monthnum%/%day%/%postname%/
 * structure before moving to plain /%postname%/. Search engines, backlinks
 * and bookmarks still carry the old addresses; the site's own logs show them
 * 404ing with no redirect (19 distinct old-style article URLs, 100% miss
 * rate, 20-24 September 2026). This sends them on to the post's current
 * permalink instead of losing the traffic and the indexed SEO value.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Matches /YYYY/MM/DD/slug/ against a published post by that slug and
 * redirects to its current permalink.
 *
 * @return void
 */
function boi_redirect_legacy_permalink() {
	if ( is_admin() || ! is_404() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) strtok( $_SERVER['REQUEST_URI'], '?' ) : '';

	if ( ! preg_match( '#^/\d{4}/\d{2}/\d{2}/([^/]+)/?$#', $path, $m ) ) {
		return;
	}

	$post = get_page_by_path( sanitize_title( $m[1] ), OBJECT, 'post' );

	if ( ! $post || 'publish' !== $post->post_status ) {
		return;
	}

	wp_safe_redirect( get_permalink( $post ), 301 );
	exit;
}
add_action( 'template_redirect', 'boi_redirect_legacy_permalink' );

/**
 * Sends a retired address to its current one: a Glossary entry whose slug
 * changed (WordPress's own old-slug redirect does not cover child pages),
 * or a seeded article renamed before the site has run the rename.
 *
 * @return void
 */
function boi_redirect_retired_slug() {
	if ( is_admin() || ! is_404() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) strtok( $_SERVER['REQUEST_URI'], '?' ) : '';
	$post = null;

	if ( function_exists( 'boi_glossary_retired_slugs' ) && preg_match( '#^/glossary/([^/]+)/?$#', $path, $m ) ) {
		$map  = boi_glossary_retired_slugs();
		$post = isset( $map[ $m[1] ] ) ? get_page_by_path( 'glossary/' . $map[ $m[1] ], OBJECT, 'page' ) : null;
	} elseif ( function_exists( 'boi_seed_renames' ) && preg_match( '#^/([^/]+)/?$#', $path, $m ) ) {
		$map  = boi_seed_renames();
		$post = isset( $map[ $m[1] ] ) ? get_page_by_path( $map[ $m[1] ], OBJECT, 'post' ) : null;
	}

	if ( ! $post || 'publish' !== $post->post_status ) {
		return;
	}

	wp_safe_redirect( get_permalink( $post ), 301 );
	exit;
}
add_action( 'template_redirect', 'boi_redirect_retired_slug' );
