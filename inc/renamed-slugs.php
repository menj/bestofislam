<?php
/**
 * Permanent redirects for renamed addresses.
 *
 * Addresses carry the subject, never a verse number, a count, a year or a
 * collision suffix. When a seeded article or Glossary entry is renamed, its
 * old address answers with a 301 to the new one. The map lives here, apart
 * from the rename routines in inc/setup.php and inc/glossary.php: those
 * remove WordPress's old-slug note on every run, and this file does not read
 * that note, so the redirect holds through every later update.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Article slugs that have changed, old => current.
 *
 * @return array
 */
function boi_post_slug_renames() {
	return array(
		'quran-4-34'                       => 'does-the-quran-allow-wife-beating',
		'deuteronomy-18-and-the-paraclete' => 'deuteronomy-prophecy-and-the-paraclete',
		'crusades-5-facts'                 => 'crusades-facts',
		'muslims-by-the-numbers-2020'      => 'muslims-by-the-numbers',
		'taqiyya-5-myths'                  => 'taqiyya-myths',
		'islamophobia-5-facts'             => 'islamophobia-facts',
		'end-of-the-world-1110-claim'      => 'end-of-the-world-prediction',
	);
}

/**
 * Every renamed path, old => new, without slashes at either end.
 *
 * @return array
 */
function boi_renamed_paths() {
	$paths = array();

	$posts = function_exists( 'boi_seed_renames' ) ? boi_seed_renames() : boi_post_slug_renames();
	foreach ( $posts as $old => $new ) {
		$paths[ $old ] = $new;
	}

	if ( function_exists( 'boi_glossary_retired_slugs' ) ) {
		foreach ( boi_glossary_retired_slugs() as $old => $new ) {
			$paths[ 'glossary/' . $old ] = 'glossary/' . $new;
		}
	}

	return (array) apply_filters( 'boi_renamed_paths', $paths );
}

/**
 * The new path for a request path, or '' when it was never renamed.
 *
 * @param string $path Request path.
 * @return string
 */
function boi_renamed_path_for( $path ) {
	$path  = strtolower( trim( (string) $path, '/' ) );
	$paths = boi_renamed_paths();

	return isset( $paths[ $path ] ) ? $paths[ $path ] : '';
}

/**
 * The post slug in an address from the old dated permalinks,
 * YYYY/MM/DD/<slug>, or '' for any other path. A renamed slug gives its
 * current one.
 *
 * @param string $path Request path.
 * @return string
 */
function boi_dated_slug_for( $path ) {
	if ( ! preg_match( '#^(\d{4})/(\d{2})/(\d{2})/([a-z0-9][a-z0-9_-]*)$#', strtolower( trim( (string) $path, '/' ) ), $m ) ) {
		return '';
	}

	$renamed = boi_post_slug_renames();

	return isset( $renamed[ $m[4] ] ) ? $renamed[ $m[4] ] : $m[4];
}

/**
 * Sends the 301 for a renamed address, and for the old dated address of a
 * published post. Runs only on a 404, so a page that now sits at an old
 * address keeps it.
 *
 * @return void
 */
function boi_redirect_renamed_slugs() {
	if ( ! is_404() || empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$uri  = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );

	if ( '' !== trim( $home, '/' ) && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}

	// Decode %-escapes and fold case so an encoded or capitalised old address still matches.
	$path = strtolower( rawurldecode( $path ) );

	$new = boi_renamed_path_for( $path );
	$url = '' === $new ? '' : home_url( user_trailingslashit( $new ) );

	// The old dated address of a post, /YYYY/MM/DD/<slug>/, goes to the
	// post's own address when that post is published.
	if ( '' === $url ) {
		$slug = boi_dated_slug_for( $path );
		$post = '' === $slug ? null : get_page_by_path( $slug, OBJECT, 'post' );
		$url  = ( $post && 'publish' === $post->post_status ) ? (string) get_permalink( $post ) : '';
	}

	if ( '' === $url ) {
		return;
	}

	$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );

	if ( '' !== $query ) {
		$url .= '?' . $query;
	}

	wp_safe_redirect( $url, 301 );
	exit;
}
add_action( 'template_redirect', 'boi_redirect_renamed_slugs', 2 );
