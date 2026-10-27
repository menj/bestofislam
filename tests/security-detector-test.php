<?php
/**
 * Self-test for inc/security-detector.php. Run: php tests/security-detector-test.php
 *
 * Scores everything the theme seeds (the articles in content/, the seeded
 * article titles, excerpts and slugs, the seeded pages, the section terms and
 * every Glossary entry) and expects a score of zero on each, then expects
 * every attack sample to be flagged. Exit code 0 on success.
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'BOI_SECURITY_TEST', true );
define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );

// Just enough of WordPress for the seed definitions to load.
function __( $s ) { return $s; } // phpcs:ignore
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); } // phpcs:ignore
function esc_html__( $s ) { return esc_html( $s ); } // phpcs:ignore
function esc_attr( $s ) { return esc_html( $s ); } // phpcs:ignore
function esc_url( $s ) { return (string) $s; } // phpcs:ignore
function add_action() {} // phpcs:ignore
function add_filter() {} // phpcs:ignore
function add_shortcode() {} // phpcs:ignore
function apply_filters( $tag, $value ) { return $value; } // phpcs:ignore
function home_url( $p = '' ) { return 'https://example.org' . $p; } // phpcs:ignore
function sanitize_title( $s ) { return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( (string) $s ) ), '-' ); } // phpcs:ignore
function sanitize_file_name( $s ) { return (string) $s; } // phpcs:ignore

require BOI_DIR . '/inc/security-detector.php';
require BOI_DIR . '/inc/setup.php';
require BOI_DIR . '/inc/glossary-data.php';

$fail = 0;
$n    = 0;

/**
 * Score one seeded item and report any score above zero.
 *
 * @param string $key     Label.
 * @param string $title   Title.
 * @param string $slug    Slug.
 * @param string $content Content.
 */
function boi_test_clean( $key, $title, $slug, $content ) {
	global $fail, $n;
	++$n;
	$r = boi_spam_score( $title, $slug, $content );
	if ( $r['score'] > 0 ) {
		++$fail;
		echo 'FALSE POSITIVE (score ' . $r['score'] . '): ' . $key . ' ' . implode( '; ', $r['reasons'] ) . "\n";
	}
}

foreach ( boi_seed_listicles() as $item ) {
	$file = BOI_DIR . '/content/' . $item['slug'] . '.json';
	$body = is_file( $file ) ? json_decode( file_get_contents( $file ), true ) : array();
	boi_test_clean( 'article ' . $item['slug'], $item['title'], $item['slug'], ( isset( $body['content'] ) ? $body['content'] : '' ) . ' ' . $item['excerpt'] );
}
foreach ( glob( BOI_DIR . '/content/*.json' ) as $file ) {
	$body = json_decode( file_get_contents( $file ), true );
	boi_test_clean( 'content file ' . basename( $file ), '', basename( $file, '.json' ), isset( $body['content'] ) ? $body['content'] : '' );
}
foreach ( boi_seed_pages() as $key => $page ) {
	boi_test_clean( 'page ' . $key, $page['title'], isset( $page['slug'] ) ? $page['slug'] : sanitize_title( $page['title'] ), (string) $page['content'] );
}
foreach ( boi_seed_topics() as $slug => $topic ) {
	boi_test_clean( 'section ' . $slug, isset( $topic['name'] ) ? $topic['name'] : '', $slug, isset( $topic['description'] ) ? $topic['description'] : '' );
}
foreach ( boi_glossary_terms() as $t ) {
	$text = array();
	array_walk_recursive(
		$t,
		function ( $v, $k ) use ( &$text ) {
			if ( is_string( $v ) && ! in_array( $k, array( 'slug', 'title' ), true ) ) {
				$text[] = $v;
			}
		}
	);
	boi_test_clean( 'glossary ' . $t['slug'], $t['title'], $t['slug'], implode( ' ', $text ) );
}
echo "Seeded items checked: $n\n";

$clean = array(
	'Bethlehem and Bethel in Genesis',
	'The Hebrew alphabet: aleph, bet, gimel',
	'Bet and Beth in the Hebrew letters',
	'Between the Testaments',
	'A better reading of Isaiah',
	'Beth Shemesh and Bethany',
	'What is at stake in the Council of Nicaea',
	'Stake, cross or tree: the word stauros',
	'A review of the evidence',
	'Bonus: a further answer, from injil.help',
	'A time slot for the five prayers',
	'The crown of the Dome of the Rock',
	'Elizabeth and Bethsaida',
	'Judith in the Apocrypha',
	'Pin up the notice at the mosque door',
	'Kumar and the Vedas',
);
foreach ( $clean as $t ) {
	$r = boi_spam_score( $t, '', '' );
	if ( $r['spam'] ) {
		++$fail;
		echo "FALSE POSITIVE sample: $t\n";
	}
}

$spam = array(
	array( 'Bet Big Win Big with Bodog Sports Edge', '' ),
	array( 'Exclusive Coolbet Bonus Code Unlocks Big Wins', '' ),
	array( 'Скачать Пинап КЗ: Официальный сайт', '' ),
	array( 'Krok po kroku: Kompleksowy przewodnik po Pelican Casino', '' ),
	array( 'Live-Roulette bei Mr Pacho', '' ),
	array( 'Crash-Games als neuer Trend', '' ),
	array( 'Persoonlijke ervaringen met top 10 online casino’s in Nederland 2026', '' ),
	array( 'Panduan Lengkap 1xBet Download', '' ),
	array( 'Pusulabet 2026 Giriş Adresi', '' ),
	array( 'Sweepstakes Casinos Explained', '' ),
	array( 'Stake Casino Australia', '' ),
	array( 'Sportwetten-Analyse vor dem Spiel', '' ),
	array( 'Connection check jk5g5zw7', '' ),
	array( 'cl-probe', '' ),
	array( 'Mostbet giriş', '' ),
	array( 'Surfplay review', '' ),
	array( 'Crownplay bonus', '' ),
	array( 'Trino Casino', '' ),
	array( 'Syndicate Casino login', '' ),
	array( 'Stellar Spins', '' ),
	array( 'Starda официальный сайт', '' ),
	array( 'Slotspalace', '' ),
	array( 'Sportaza', '' ),
	array( 'Spybet', '' ),
	array( 'Squeenaud', '' ),
	array( 'Spinjo', '' ),
	array( 'Spellwin', '' ),
	array( 'Spinsup', '' ),
	array( 'Spinch', '' ),
	array( 'Spin Samurai', '' ),
	array( 'Slots Gallery', '' ),
	array( 'Najboljše igralne avtomate v Sloveniji', '' ),
	array( 'Casino Oyunlarında Bankroll Yönetimi', '' ),
	array( 'Glücksspiel in der Schweiz', '' ),
	array( 'Ausländische Online Casinos', '' ),
	array( 'Untitled', 'casinos-online-mas-seguros-espana-2026' ),
	array( 'Untitled', 'zahranicne-stavkove-kancelarie-na-slovensku-3' ),
	array( 'Untitled', 'pin-ap-kazino-oficialnyj-sajt' ),
	array( 'Untitled', 'kto-brasil-cassino-online-e-apostas' ),
	array( 'Vavada зеркало', '' ),
	array( 'Melbet app', '' ),
	array( 'Betify login', '' ),
	array( 'Casinozer avis', '' ),
	array( 'AureonPlay', '' ),
	array( 'PlatinCasino', '' ),
	array( 'True Blue Casino', '' ),
	array( 'SydSpin', '' ),
	array( 'TCL99', '' ),
	array( 'Vegas Stars', '' ),
	array( 'Surge Casino', '' ),
	array( '1win', '' ),
);
foreach ( $spam as $s ) {
	$r = boi_spam_score( $s[0], $s[1], '' );
	if ( ! $r['spam'] ) {
		++$fail;
		echo "MISSED: {$s[0]} {$s[1]} (score {$r['score']})\n";
	}
}
$r = boi_spam_score( 'A guide', '', '<p>See <a href="https://best-casino.example/">here</a></p>' );
if ( ! $r['spam'] ) {
	++$fail;
	echo "MISSED: gambling link\n";
}
echo 'Spam samples checked: ' . ( count( $spam ) + 1 ) . "\n";
echo $fail ? "FAILED: $fail\n" : "PASS\n";
exit( $fail ? 1 : 0 );
