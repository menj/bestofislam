<?php
/**
 * Build the static error pages in errors/ for the web server's ErrorDocument
 * lines, from the same template and stylesheet as the drop-ins
 * (assets/error-pages/page.html and page.css, inlined).
 * Run: php bin/build-error-pages.php (add --check to fail when a page is stale).
 *
 * @package BestOfIslam
 */

$boi_root  = dirname( __DIR__ );
$boi_check = in_array( '--check', $argv, true );
$boi_pages = array(
	'403' => array( 'Access denied', 'Access denied', 'This address is closed to visitors.', 'Every page of the site is open from the home page.', 'Go to the home page' ),
	'500' => array( 'Something went wrong', 'Something went wrong', 'An error stopped this page from loading.', 'Please try again shortly.', 'Try again' ),
	'502' => array( 'Bad gateway', 'Bad gateway', 'A server between you and the site gave a wrong answer.', 'Please try again shortly.', 'Try again' ),
	'504' => array( 'Gateway timeout', 'Gateway timeout', 'A server between you and the site took too long to answer.', 'Please try again shortly.', 'Try again' ),
	'503' => array( 'Temporarily unavailable', 'Back shortly', 'The site is busy or offline for a short while.', 'Please try again in a few minutes.', 'Try again' ),
);
$boi_tpl   = (string) file_get_contents( $boi_root . '/assets/error-pages/page.html' );
$boi_css   = (string) file_get_contents( $boi_root . '/assets/error-pages/page.css' );
$boi_stale = 0;
if ( ! $boi_check && ! is_dir( $boi_root . '/errors' ) ) {
	mkdir( $boi_root . '/errors' );
}
foreach ( $boi_pages as $boi_code => $boi_p ) {
	$boi_e    = static fn( $s ) => htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' );
	$boi_html = strtr(
		$boi_tpl,
		array(
			'{{LANG}}'    => 'en',
			'{{TITLE}}'   => $boi_e( $boi_p[0] ),
			'{{NAME}}'    => 'Best of Islam',
			'{{CSS}}'     => str_replace( '</', '<\/', $boi_css ),
			'{{HEADING}}' => $boi_e( $boi_p[1] ),
			'{{LINE1}}'   => $boi_e( $boi_p[2] ),
			'{{LINE2}}'   => $boi_e( $boi_p[3] ),
			'{{AGAIN}}'   => $boi_e( $boi_p[4] ),
			'{{HOME}}'    => '/',
		)
	);
	$boi_path = $boi_root . '/errors/' . $boi_code . '.html';
	if ( $boi_check ) {
		if ( ! is_readable( $boi_path ) || file_get_contents( $boi_path ) !== $boi_html ) {
			fwrite( STDERR, "stale: errors/$boi_code.html\n" );
			++$boi_stale;
		}
		continue;
	}
	if ( false === file_put_contents( $boi_path, $boi_html ) ) {
		fwrite( STDERR, "cannot write errors/$boi_code.html\n" );
		exit( 1 );
	}
	echo "wrote errors/$boi_code.html\n";
}
exit( $boi_stale ? 1 : 0 );
