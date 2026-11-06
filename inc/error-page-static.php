<?php
/**
 * The static error pages, as plain PHP with no WordPress calls.
 *
 * bin/build-error-pages.php uses it to build errors/ for a site at the domain
 * root, and inc/error-pages.php uses it to write a copy of each page into
 * wp-content/bestofislam-errors/ with the site's own name and home address,
 * so the pages also work when WordPress lives in a subfolder.
 *
 * @package BestOfIslam
 */

if ( ! defined( 'ABSPATH' ) && 'cli' !== PHP_SAPI ) {
	exit;
}

/**
 * The text of each static page: title, heading, 2 lines and the link label.
 *
 * @return array<string,string[]>
 */
function boi_static_error_pages() {
	return array(
		'403' => array( 'Access denied', 'Access denied', 'This address is closed to visitors.', 'Every page of the site is open from the home page.', 'Go to the home page' ),
		'500' => array( 'Something went wrong', 'Something went wrong', 'An error stopped this page from loading.', 'Please try again shortly.', 'Try again' ),
		'502' => array( 'Bad gateway', 'Bad gateway', 'A server between you and the site gave a wrong answer.', 'Please try again shortly.', 'Try again' ),
		'504' => array( 'Gateway timeout', 'Gateway timeout', 'A server between you and the site took too long to answer.', 'Please try again shortly.', 'Try again' ),
		'503' => array( 'Temporarily unavailable', 'Back shortly', 'The site is busy or offline for a short while.', 'Please try again in a few minutes.', 'Try again' ),
	);
}

/**
 * The 32px icon as a link with a data address. An error page answers at any
 * path, in a subfolder install too, and while WordPress or PHP is down, so it
 * carries the icon inside the page and asks the server for nothing.
 *
 * @param string $root Theme folder on disk.
 * @return string The link tag, or '' when the file is missing.
 */
function boi_error_page_icon( $root ) {
	$png = @file_get_contents( $root . '/assets/images/favicon-32.png' ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
	return $png ? '<link rel="icon" href="data:image/png;base64,' . base64_encode( $png ) . '" sizes="32x32" type="image/png">' : ''; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
}

/**
 * One static page, filled from assets/error-pages/page.html and page.css.
 *
 * @param string $root Theme folder on disk.
 * @param string $code Status code, a key of boi_static_error_pages().
 * @param array  $site name, home (the href of the home link) and lang.
 * @return string
 */
function boi_static_error_html( $root, $code, $site ) {
	$p   = boi_static_error_pages()[ $code ];
	$e   = static function ( $s ) {
		return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
	};
	$tpl = (string) file_get_contents( $root . '/assets/error-pages/page.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$css = (string) file_get_contents( $root . '/assets/error-pages/page.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return strtr(
		$tpl,
		array(
			'{{LANG}}'    => $e( $site['lang'] ),
			'{{TITLE}}'   => $e( $p[0] ),
			'{{NAME}}'    => $e( $site['name'] ),
			'{{CSS}}'     => str_replace( '</', '<\/', $css ),
			'{{HEADING}}' => $e( $p[1] ),
			'{{LINE1}}'   => $e( $p[2] ),
			'{{LINE2}}'   => $e( $p[3] ),
			'{{AGAIN}}'   => $e( $p[4] ),
			'{{HOME}}'    => $e( $site['home'] ),
			'{{ICON}}'    => boi_error_page_icon( $root ),
		)
	);
}
