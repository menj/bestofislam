<?php
/**
 * Bible links (.claude/VOICE.md section 7): the Bible site's domain may appear
 * only as a link destination, never as visible text in anything the theme
 * publishes or ships, and every link to it has a well-formed address. This
 * is the mandatory compliance check to run before finalising content.
 *
 * Run: php tests/bible-links-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

$root   = dirname( __DIR__ );
$domain = 'thebible.exposed';
$fails  = 0;
$links  = 0;
$count  = 0;

/**
 * Records one failure.
 *
 * @param string $where File and place.
 * @param string $what  Problem.
 * @return void
 */
function fail( $where, $what ) {
	global $fails;
	++$fails;
	echo "FAIL: $where: $what\n";
}

/**
 * Checks one piece of publishable text.
 *
 * @param string $where Label.
 * @param string $text  HTML or plain text.
 * @return void
 */
function check_text( $where, $text ) {
	global $domain, $links, $count;
	++$count;
	// Each link to the site must have a well-formed address.
	if ( preg_match_all( '#href="(https?://[^"]*' . preg_quote( $domain, '#' ) . '[^"]*)"#i', $text, $m ) ) {
		foreach ( $m[1] as $url ) {
			++$links;
			if ( ! preg_match( '#^https://thebible\.exposed/(bible/[a-z0-9-]+/\d+/(\#v\d+)?|commentary/[a-z0-9-]+/|glossary/[a-z0-9-]+/|section/[a-z0-9-]+/)$#', $url ) ) {
				fail( $where, "malformed link $url" );
			}
		}
	}
	// With every href removed, the domain must not remain anywhere: not as
	// visible text, anchor text, a source description or another attribute.
	$visible = preg_replace( '#\shref="[^"]*"#i', '', $text );
	if ( false !== stripos( $visible, $domain ) ) {
		$at = stripos( $visible, $domain );
		fail( $where, 'domain visible near "' . trim( substr( strip_tags( substr( $visible, max( 0, $at - 80 ), 160 ) ), 0, 160 ) ) . '"' );
	}
}

foreach ( glob( $root . '/content/*.json' ) as $file ) {
	$data = json_decode( file_get_contents( $file ), true );
	check_text( 'content/' . basename( $file ), (string) $data['content'] );
}

foreach ( glob( $root . '/inc/glossary-data/*.json' ) as $file ) {
	$text = '';
	$data = json_decode( file_get_contents( $file ), true );
	array_walk_recursive(
		$data,
		function ( $v ) use ( &$text ) {
			$text .= is_string( $v ) ? $v . "\n" : '';
		}
	);
	check_text( 'inc/glossary-data/' . basename( $file ), $text );
}

// Everything the theme ships, except the private .claude folder and the
// development folders, as plain text: titles, excerpts, abstracts, pages,
// patterns, templates, the readme and the changelog.
$skip = array( '.git', '.claude', 'tests', 'bin', 'node_modules', 'content', 'glossary-data' );
$it   = new RecursiveIteratorIterator(
	new RecursiveCallbackFilterIterator(
		new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
		function ( $f ) use ( $skip ) {
			return ! ( $f->isDir() && in_array( $f->getFilename(), $skip, true ) );
		}
	)
);
foreach ( $it as $f ) {
	if ( preg_match( '/\.(json|txt|md|php|html|js|css)$/', $f->getFilename() ) ) {
		$raw  = file_get_contents( $f->getPathname() );
		$json = '.json' === substr( $f->getFilename(), -5 ) ? json_decode( $raw, true ) : null;
		if ( is_array( $json ) ) {
			// Decoded, so an escaped href=\"...\" reads as the link it is.
			$raw = '';
			array_walk_recursive(
				$json,
				function ( $v ) use ( &$raw ) {
					$raw .= is_string( $v ) ? $v . "\n" : '';
				}
			);
		}
		check_text( substr( $f->getPathname(), strlen( $root ) + 1 ), $raw );
	}
}

echo $fails ? "$fails problem(s) in $count files\n" : "OK: $count files, $links links to the Bible site, the domain visible nowhere\n";
exit( $fails ? 1 : 0 );
