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
		$url = add_query_arg( array_map( 'strval', array_filter( $args, 'is_scalar' ) ), $url );
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

/**
 * Saves the base word from Settings, Permalinks, as the plugin did. The
 * value is the same one the Search engines tab edits. WordPress flushes the
 * rewrite rules itself when that screen saves, so the base is applied first.
 *
 * @return void
 */
function boi_search_base_permalink_screen() {
	if ( function_exists( 'wpseosearch_base' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['boi_search_base'] ) ) {
		check_admin_referer( 'update-permalink' );

		$base = sanitize_title( wp_unslash( $_POST['boi_search_base'] ) );
		$base = '' !== $base ? $base : 'search';

		// boi_sanitize_options() merges this into the saved settings and
		// changes nothing else.
		$value = array(
			'_tab'        => 'permalink',
			'search_base' => $base,
		);

		if ( ! has_filter( 'sanitize_option_' . BOI_OPTION_KEY ) ) {
			$stored = get_option( BOI_OPTION_KEY, array() );
			$value  = array_merge( is_array( $stored ) ? $stored : array(), array( 'search_base' => $base ) );
		}

		update_option( BOI_OPTION_KEY, $value );

		global $wp_rewrite;

		if ( boi_pretty_search_enabled() && $wp_rewrite instanceof WP_Rewrite ) {
			$wp_rewrite->search_base = $base;
		}
	}

	add_settings_field( 'boi_search_base', __( 'Search base', 'bestofislam' ), 'boi_search_base_permalink_field', 'permalink', 'optional', array( 'label_for' => 'boi_search_base' ) );
}
add_action( 'load-options-permalink.php', 'boi_search_base_permalink_screen' );

/**
 * The Search base field on Settings, Permalinks.
 *
 * @return void
 */
function boi_search_base_permalink_field() {
	printf(
		'<input type="text" name="boi_search_base" id="boi_search_base" class="regular-text code" value="%1$s" /><p class="description">%2$s</p>',
		esc_attr( boi_search_base() ),
		esc_html__( 'Searches open at this address, such as /search/Paul/. The switch for readable search addresses is in Theme Options, Search engines.', 'bestofislam' )
	);
}
