<?php
/**
 * Build the static error pages in errors/ for the web server's ErrorDocument
 * lines, from the same template and stylesheet as the drop-ins
 * (assets/error-pages/page.html and page.css, inlined) through
 * inc/error-page-static.php. These copies assume a site at the domain root;
 * the theme writes a copy for the site's own address into
 * wp-content/bestofislam-errors/.
 * Run: php bin/build-error-pages.php (add --check to fail when a page is stale).
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

$boi_root = dirname( __DIR__ );
require $boi_root . '/inc/error-page-static.php';

$boi_check = in_array( '--check', $argv, true );
$boi_site  = array(
	'name' => 'Best of Islam',
	'home' => '/',
	'lang' => 'en',
);
$boi_stale = 0;
if ( ! $boi_check && ! is_dir( $boi_root . '/errors' ) ) {
	mkdir( $boi_root . '/errors' );
}
foreach ( array_keys( boi_static_error_pages() ) as $boi_code ) {
	$boi_html = boi_static_error_html( $boi_root, (string) $boi_code, $boi_site );
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
