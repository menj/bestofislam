<?php
/**
 * Custom login address.
 *
 * Serves the login page from an address of the owner's choosing and answers
 * wp-login.php, wp-register.php and wp-admin with the site's 404 page for
 * visitors who are not logged in. Every link WordPress generates to
 * wp-login.php, including those in password-reset and privacy emails, is
 * rewritten to the new address.
 *
 * Adapted from the WPS Hide Login plugin by WPServeur, NicolasKulka and
 * wpformation (GPLv2 or later). The plugin intercepts requests at
 * plugins_loaded, which fires before a theme loads; WordPress loads the theme
 * for every request, wp-login.php included, so this version intercepts at
 * wp_loaded, which still runs before the login page does any work.
 *
 * Kept from the plugin, because each one breaks something if lost:
 *  - password-protected posts still submit to wp-login.php?action=postpass;
 *  - admin-post.php and admin-ajax.php stay open, since the contact form and
 *    the front end use them;
 *  - a logged-in visitor to the login address goes to the dashboard.
 *
 * Safeguards against lockout:
 *  - off until the owner turns it on and chooses an address;
 *  - the address is checked against WordPress's own paths and against every
 *    existing page, post and section;
 *  - define( 'BOI_HIDE_LOGIN', false ); in wp-config.php turns it off at once;
 *  - it stands aside while the WPS Hide Login plugin is active;
 *  - it belongs to the theme, so activating another theme restores
 *    wp-login.php rather than hiding it for good.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the custom login address is in force.
 *
 * @return bool
 */
function boi_login_enabled() {
	if ( defined( 'BOI_HIDE_LOGIN' ) && ! BOI_HIDE_LOGIN ) {
		return false;
	}

	if ( defined( 'WPS_HIDE_LOGIN_VERSION' ) || is_multisite() ) {
		return false;
	}

	$options = boi_get_options();

	return ! empty( $options['login_enabled'] ) && '' !== boi_login_slug();
}

/**
 * The chosen login slug, importing WPS Hide Login's saved slug when the
 * theme has none yet.
 *
 * @return string
 */
function boi_login_slug() {
	$options = boi_get_options();
	$slug    = isset( $options['login_slug'] ) ? sanitize_title( $options['login_slug'] ) : '';

	if ( '' === $slug && ! isset( $options['login_slug'] ) ) {
		$slug = sanitize_title( (string) get_option( 'whl_page', '' ) );
	}

	return $slug;
}

/**
 * The full login address.
 *
 * @param string|null $scheme URL scheme.
 * @return string
 */
function boi_login_url_base( $scheme = null ) {
	return user_trailingslashit( home_url( '/', $scheme ) . boi_login_slug() );
}

/**
 * Slugs that may not be used: WordPress's own paths, and anything that
 * already names a page, post, section or other public address.
 *
 * @param string $slug Candidate slug.
 * @return string Empty when allowed, otherwise the reason.
 */
function boi_login_slug_problem( $slug ) {
	$reserved = array( 'wp-admin', 'wp-login', 'wp-login-php', 'admin', 'login', 'dashboard', 'wp-content', 'wp-includes', 'wp-json', 'feed', 'search', 'page', 'comments', 'embed', 'topic', 'author', 'reflections', 'site-map' );

	if ( '' === $slug ) {
		return __( 'Choose an address.', 'bestofislam' );
	}

	if ( strlen( $slug ) < 6 ) {
		return __( 'Use at least six characters, so the address is not easily guessed.', 'bestofislam' );
	}

	if ( in_array( $slug, $reserved, true ) ) {
		return __( 'That address is used by WordPress or the theme.', 'bestofislam' );
	}

	if ( get_page_by_path( $slug, OBJECT, array( 'post', 'page', 'attachment' ) ) || term_exists( $slug ) ) {
		return __( 'A page, post or section already uses that address.', 'bestofislam' );
	}

	return '';
}

/**
 * The path of the current request, decoded and without its query.
 *
 * @return string
 */
function boi_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? rawurldecode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );

	return untrailingslashit( $path );
}

/**
 * Answers a request with the theme's 404 page and stops.
 *
 * @return void
 */
function boi_login_not_found() {
	global $wp_query, $pagenow;

	$pagenow = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	if ( ! defined( 'WP_USE_THEMES' ) ) {
		define( 'WP_USE_THEMES', true );
	}

	$_SERVER['REQUEST_URI'] = user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

	wp();
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();

	require_once ABSPATH . WPINC . '/template-loader.php';
	exit;
}

/**
 * Routes login requests.
 *
 * @return void
 */
function boi_login_route() {
	global $pagenow;

	if ( ! boi_login_enabled() || ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() ) {
		return;
	}

	$path       = boi_request_path();
	$home_path  = untrailingslashit( (string) wp_parse_url( home_url(), PHP_URL_PATH ) );
	$login_path = $home_path . '/' . boi_login_slug();

	// Password-protected posts submit to wp-login.php?action=postpass.
	// phpcs:ignore WordPress.Security.NonceVerification
	$postpass = isset( $_GET['action'] ) && 'postpass' === $_GET['action'] && isset( $_POST['post_password'] );

	// The chosen address serves the login page.
	if ( $path === $login_path ) {
		if ( is_user_logged_in() && ! isset( $_REQUEST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( admin_url() );
			exit;
		}

		$pagenow = 'wp-login.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		require_once ABSPATH . 'wp-login.php';
		exit;
	}

	if ( $postpass || is_user_logged_in() ) {
		return;
	}

	// The old addresses answer with a 404 for anyone not logged in.
	$base = basename( $path );

	if ( 'wp-login.php' === $base || 'wp-register.php' === $base || $home_path . '/wp-login' === $path ) {
		boi_login_not_found();
	}

	// The dashboard, except the endpoints the front end relies on. The admin
	// context cannot render the theme's 404, so this goes to the front end's
	// ordinary not-found address, which reveals nothing about the login.
	if ( is_admin() && ! wp_doing_ajax() && 'admin-post.php' !== $pagenow ) {
		wp_safe_redirect( home_url( user_trailingslashit( '404' ) ) );
		exit;
	}
}
add_action( 'wp_loaded', 'boi_login_route', 1 );

/**
 * Rewrites a generated wp-login.php address to the chosen address, keeping
 * its query. Password-protected post submissions keep the original.
 *
 * @param string      $url    Address.
 * @param string|null $scheme URL scheme.
 * @return string
 */
function boi_filter_login_address( $url, $scheme = null ) {
	if ( ! boi_login_enabled() || false === strpos( $url, 'wp-login.php' ) || false !== strpos( $url, 'action=postpass' ) ) {
		return $url;
	}

	if ( is_ssl() ) {
		$scheme = 'https';
	}

	$parts = explode( '?', $url, 2 );
	$new   = boi_login_url_base( $scheme );

	if ( isset( $parts[1] ) ) {
		parse_str( $parts[1], $args );

		if ( isset( $args['login'] ) ) {
			$args['login'] = rawurlencode( $args['login'] );
		}

		$new = add_query_arg( $args, $new );
	}

	return $new;
}

/**
 * Filter adapter for site_url.
 *
 * @param string      $url     Address.
 * @param string      $path    Path requested.
 * @param string|null $scheme  URL scheme.
 * @return string
 */
function boi_login_site_url( $url, $path, $scheme ) {
	return boi_filter_login_address( $url, $scheme );
}
add_filter( 'site_url', 'boi_login_site_url', 10, 3 );
add_filter( 'network_site_url', 'boi_login_site_url', 10, 3 );

/**
 * Filter adapter for redirects and the login URL.
 *
 * @param string $url Address.
 * @return string
 */
function boi_login_redirect_url( $url ) {
	return boi_filter_login_address( $url );
}
add_filter( 'wp_redirect', 'boi_login_redirect_url' );
add_filter( 'login_url', 'boi_login_redirect_url' );
add_filter( 'logout_url', 'boi_login_redirect_url' );
add_filter( 'lostpassword_url', 'boi_login_redirect_url' );
add_filter( 'register_url', 'boi_login_redirect_url' );

/**
 * Rewrites wp-login.php in privacy-request emails, which WordPress builds
 * from a stored string rather than site_url().
 *
 * @param string $content Email content.
 * @return string
 */
function boi_login_email_content( $content ) {
	if ( ! boi_login_enabled() ) {
		return $content;
	}

	// site_url() is filtered above, so build the original address from the
	// raw option to find it in the stored text.
	$original = untrailingslashit( (string) get_option( 'siteurl' ) ) . '/wp-login.php';

	return str_replace( array( $original, set_url_scheme( $original, 'https' ), set_url_scheme( $original, 'http' ) ), boi_login_url_base(), $content );
}
add_filter( 'user_request_action_email_content', 'boi_login_email_content', 999 );
