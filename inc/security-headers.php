<?php
/**
 * Security headers on every front-end response.
 *
 * Theme Options, Access. On by default:
 * - frame-ancestors 'self' and X-Frame-Options: no other site may show the
 *   pages in a frame (clickjacking); the site itself still may, as the
 *   Customizer does. The policy carries frame-ancestors only, so no script,
 *   style or embed is restricted.
 * - Cross-Origin-Opener-Policy: a page opened from the site, or that opens
 *   it, gets no handle on its window; share pop-ups still open.
 * - X-Content-Type-Options and Referrer-Policy: no guessing of file types,
 *   and only the site's address, never the page, sent to other sites.
 *
 * Off by default, because it cannot be undone from here once browsers have
 * seen it: Strict-Transport-Security, sent only over HTTPS, for a year.
 *
 * A full Content-Security-Policy is not sent: WordPress and its plugins print
 * inline scripts and styles that an enforcing policy would block.
 *
 * @package bestofislam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The headers to send, from the saved options.
 *
 * @param array $opts Options from boi_get_options().
 * @param bool  $ssl  Whether the request is over HTTPS.
 * @return array<string,string>
 */
function boi_security_headers( $opts, $ssl ) {
	$headers = array();

	if ( ! empty( $opts['security_headers'] ) ) {
		$headers = array(
			'Content-Security-Policy'    => "frame-ancestors 'self'",
			'X-Frame-Options'            => 'SAMEORIGIN',
			'Cross-Origin-Opener-Policy' => 'same-origin-allow-popups',
			'X-Content-Type-Options'     => 'nosniff',
			'Referrer-Policy'            => 'strict-origin-when-cross-origin',
		);
	}

	if ( ! empty( $opts['security_hsts'] ) && $ssl ) {
		$headers['Strict-Transport-Security'] = 'max-age=31536000';
	}

	return (array) apply_filters( 'boi_security_headers', $headers );
}

/**
 * Sends the headers.
 *
 * @return void
 */
function boi_send_security_headers() {
	if ( is_admin() || headers_sent() ) {
		return;
	}

	foreach ( boi_security_headers( boi_get_options(), is_ssl() ) as $name => $value ) {
		header( $name . ': ' . $value );
	}
}
add_action( 'send_headers', 'boi_send_security_headers' );
