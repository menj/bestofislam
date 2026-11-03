<?php
/**
 * Renamed addresses: every old path resolves to its new one, and every new
 * article slug is seeded.
 *
 * Run: php tests/renamed-slugs-test.php
 *
 * @package BestOfIslam
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );

function add_action() {}
function apply_filters( $hook, $value ) { return $value; }

require BOI_DIR . '/inc/renamed-slugs.php';

// boi_glossary_retired_slugs() from inc/glossary.php, lifted out so the rest
// of that file (which needs WordPress) is not loaded.
$src = (string) file_get_contents( BOI_DIR . '/inc/glossary.php' );
if ( ! preg_match( '/^function boi_glossary_retired_slugs\(.*?^}$/ms', $src, $m ) ) {
	fwrite( STDERR, "Cannot find boi_glossary_retired_slugs\n" );
	exit( 1 );
}
eval( $m[0] ); // phpcs:ignore

$expect = array(
	'/deuteronomy-18-and-the-paraclete/' => 'deuteronomy-prophecy-and-the-paraclete',
	'/crusades-5-facts/'                 => 'crusades-facts',
	'/muslims-by-the-numbers-2020/'      => 'muslims-by-the-numbers',
	'/taqiyya-5-myths/'                  => 'taqiyya-myths',
	'/islamophobia-5-facts/'             => 'islamophobia-facts',
	'/end-of-the-world-1110-claim/'      => 'end-of-the-world-prediction',
	'/quran-4-34/'                       => 'does-the-quran-allow-wife-beating',
	'Crusades-5-Facts'                   => 'crusades-facts',
	'/glossary/abd-2/'                   => 'glossary/abd',
	'/glossary/domestic-discipline-quran-4-34/' => 'glossary/domestic-discipline',
	'/crusades-facts/'                   => '',
	'/glossary/abd/'                     => '',
);

$seed  = json_decode( (string) file_get_contents( BOI_DIR . '/inc/data/seed.json' ), true );
$seeds = array_column( $seed['listicles'], 'slug' );
$fail  = 0;

foreach ( $expect as $from => $to ) {
	$got = boi_renamed_path_for( $from );
	if ( $got !== $to ) {
		echo "FAIL: $from gave '$got', expected '$to'\n";
		++$fail;
	}
}

foreach ( boi_post_slug_renames() as $old => $new ) {
	if ( ! in_array( $new, $seeds, true ) || ! is_readable( BOI_DIR . '/content/' . $new . '.json' ) ) {
		echo "FAIL: $old renames to $new, which is not seeded\n";
		++$fail;
	}
	if ( in_array( $old, $seeds, true ) ) {
		echo "FAIL: $old is still seeded\n";
		++$fail;
	}
}

echo $fail ? "$fail failure(s)\n" : "OK: renamed addresses resolve\n";
exit( $fail ? 1 : 0 );
