<?php
/**
 * The login page, in the theme's identity.
 *
 * The whole page is the design: the navy background with the Birmingham
 * manuscript under the hero's overlay covers the screen at every size, and a
 * single centred column carries the logo, a line saying who the page is for,
 * the form in a navy card with a gold top edge, the links, and the image
 * credit. The page is navy in either light or dark mode. The pop-up login
 * shown when a session expires keeps a plain single column. Every login page WordPress serves takes this design: the
 * sign-in form, lost password, reset, registration, and the custom login
 * address from inc/login.php.
 *
 * The design is permanent: the header always shows the bars and the
 * wordmark. WordPress's own logo slot is hidden, and its link and text point
 * to the site and carry its name for any tool that reads them.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads the login stylesheet.
 *
 * @return void
 */
function boi_login_styles() {
	wp_enqueue_style( 'boi-login', BOI_URI . '/assets/css/login.css', array( 'login' ), BOI_VERSION );

	$css = '';

	// The background image, when the site has one for the hero.
	$hero = function_exists( 'boi_hero_image' ) ? boi_hero_image() : null;

	if ( $hero ) {
		$css .= ':root{--boi-login-img:url(' . esc_url_raw( $hero['large'] ) . ')}';
	}

	if ( '' !== $css ) {
		wp_add_inline_style( 'boi-login', $css );
	}
}
add_action( 'login_enqueue_scripts', 'boi_login_styles' );

/**
 * The logo links to the site.
 *
 * @return string
 */
function boi_login_header_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'boi_login_header_url' );

/**
 * The logo carries the site's name.
 *
 * @return string
 */
function boi_login_header_text() {
	return get_bloginfo( 'name', 'display' );
}
add_filter( 'login_headertext', 'boi_login_header_text' );

/**
 * Marks the page for the theme's styles, and marks the brand panel's
 * absence in the pop-up login.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function boi_login_body_class( $classes ) {
	$classes[] = 'boi-login';

	if ( function_exists( 'boi_hero_image' ) && boi_hero_image() ) {
		$classes[] = 'boi-login-has-image';
	}

	return $classes;
}
add_filter( 'login_body_class', 'boi_login_body_class' );

/**
 * The header above the form: the bars and the wordmark, then a line saying
 * who the page is for. The design is fixed; nothing replaces it.
 *
 * @return void
 */
function boi_login_brand_panel() {
	// The pop-up login inside the dashboard has no room for a header.
	if ( isset( $_REQUEST['interim-login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$logo = sprintf(
		'<div class="boi-login-brand__bars" aria-hidden="true"><span></span><span></span><span></span></div><p class="boi-login-brand__wordmark"><a href="%1$s">%2$s</a></p>',
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);

	printf(
		'<header class="boi-login-brand">%1$s<p class="boi-login-brand__line">%2$s</p></header>',
		$logo, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		esc_html__( 'Editorial access only.', 'bestofislam' )
	);
}
add_action( 'login_header', 'boi_login_brand_panel' );

/**
 * The background image's credit, at the foot of the page.
 *
 * @return void
 */
function boi_login_credit() {
	if ( isset( $_REQUEST['interim-login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$hero = function_exists( 'boi_hero_image' ) ? boi_hero_image() : null;

	if ( ! $hero || '' === $hero['source'] ) {
		return;
	}

	printf(
		'<p class="boi-login-credit">%1$s <a href="%2$s" rel="noopener">%3$s</a></p>',
		esc_html( sprintf( /* translators: 1: artist, 2: licence. */ __( 'Background: %1$s. %2$s,', 'bestofislam' ), $hero['artist'], $hero['licence'] ) ),
		esc_url( $hero['source'] ),
		esc_html__( 'Wikimedia Commons', 'bestofislam' )
	);
}
add_action( 'login_footer', 'boi_login_credit' );
