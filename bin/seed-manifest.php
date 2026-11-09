<?php
/**
 * Writes inc/data/seed-manifest.json: the md5 of every bundled article (content/*.json), the seed list and the
 * supersedes map, and one checksum over them. The content sync runs when that checksum changes and stops
 * removals when a file no longer matches it (inc/content-sync.php).
 *
 * Run after every edit of content/, inc/data/seed.json or inc/data/supersedes.json:  php bin/seed-manifest.php
 * bin/check-content.py fails when the manifest is stale.
 */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

$root  = dirname( __DIR__ );
$files = array();
$list  = glob( $root . '/content/*.json' );
sort( $list );
foreach ( $list as $file ) {
	$files[ 'content/' . basename( $file ) ] = md5_file( $file );
}
foreach ( array( 'seed', 'supersedes' ) as $name ) {
	$files[ 'inc/data/' . $name . '.json' ] = md5_file( $root . '/inc/data/' . $name . '.json' );
}
$out = array(
	'files'    => $files,
	'checksum' => md5( json_encode( $files ) ),
);
file_put_contents( $root . '/inc/data/seed-manifest.json', json_encode( $out, JSON_PRETTY_PRINT ) . "\n" );
echo 'Wrote inc/data/seed-manifest.json (' . count( $files ) . " files)\n";
