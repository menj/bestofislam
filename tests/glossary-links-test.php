<?php
/**
 * Glossary links: the fast filter gives the same output as the old one.
 *
 * Builds the link map from inc/glossary-data, runs the 1.89.0 filter (kept
 * below as boi_gl_apply_old()) and boi_gl_apply() on seeded article bodies,
 * and fails on any difference. Prints the time each took.
 *
 * Run: php tests/glossary-links-test.php [number of articles, default all]
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );
define( 'BOI_URI', 'https://bestofislam.org/wp-content/themes/bestofislam' );
define( 'HOUR_IN_SECONDS', 3600 );

function add_action() {}
function add_filter() {}
function apply_filters( $hook, $value ) { return $value; }
function esc_url( $u ) { return htmlspecialchars( (string) $u, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }

require BOI_DIR . '/inc/glossary-data.php';
require BOI_DIR . '/inc/glossary-links.php';

// boi_glossary_epithet_html() and boi_glossary_term_by_slug() from inc/glossary.php,
// lifted out so the rest of that file (which needs WordPress) is not loaded.
$src = (string) file_get_contents( BOI_DIR . '/inc/glossary.php' );
foreach ( array( 'boi_glossary_term_by_slug', 'boi_glossary_epithet_html' ) as $fn ) {
	if ( ! preg_match( '/^function ' . $fn . '\(.*?^}$/ms', $src, $m ) ) {
		fwrite( STDERR, "Cannot find $fn\n" );
		exit( 1 );
	}
	eval( $m[0] ); // phpcs:ignore
}

/** The map as boi_gl_map() builds it, from the data instead of the pages. */
function test_map() {
	$stop = boi_gl_stoplist();
	$seen = array();
	$map  = array();
	$id   = 0;
	foreach ( boi_glossary_terms() as $t ) {
		++$id;
		$names = array_merge( array( $t['title'] ), isset( $t['aliases'] ) ? (array) $t['aliases'] : array() );
		foreach ( $names as $name ) {
			$name = trim( (string) $name );
			$low  = mb_strtolower( $name );
			if ( strlen( $name ) < 4 || in_array( $low, $stop, true ) || isset( $seen[ $low ] ) ) {
				continue;
			}
			$seen[ $low ] = true;
			$map[]        = array(
				'phrase'  => $name,
				'url'     => 'https://bestofislam.org/glossary/' . $t['slug'] . '/',
				'id'      => $id,
				'always'  => in_array( $t['slug'], boi_gl_always(), true ),
				'name'    => boi_gl_is_name( $t['slug'] ),
				'epithet' => in_array( $low, boi_gl_no_epithet(), true ) ? '' : boi_glossary_epithet_html( boi_glossary_term_by_slug( $t['slug'] ) ),
			);
		}
	}
	usort( $map, function ( $a, $b ) {
		return strlen( $b['phrase'] ) - strlen( $a['phrase'] );
	} );
	return $map;
}

/** The loop of boi_gl_filter() in 1.89.0, unchanged. */
function boi_gl_apply_old( $content, $items, $budget ) {
	$skip   = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'code', 'pre', 'script', 'style', 'button', 'figcaption', 'blockquote', 'cite' );
	$depth  = array_fill_keys( $skip, 0 );
	$used   = array();
	$in_p   = false;
	$lanah  = 0;
	$tokens = preg_split( '/(<(?:[^>"\']|"[^"]*"|\'[^\']*\')*>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );

	foreach ( $tokens as $i => $tok ) {
		if ( '' !== $tok && '<' === $tok[0] ) {
			if ( preg_match( '#^<(/?)([a-z0-9]+)#i', $tok, $m ) ) {
				$tag = strtolower( $m[2] );
				$end = '/' === $m[1];
				if ( 'span' === $tag && ( $lanah || ( ! $end && preg_match( '/\\bclass="[^"]*\\bboi-lanah\\b/', $tok ) ) ) ) {
					$lanah = max( 0, $lanah + ( $end ? -1 : 1 ) );
				}
				if ( 'p' === $tag || 'li' === $tag ) {
					$in_p = ! $end;
				} elseif ( isset( $depth[ $tag ] ) ) {
					$depth[ $tag ] = max( 0, $depth[ $tag ] + ( $end ? -1 : 1 ) );
				}
			}
			continue;
		}
		if ( ! $in_p || $lanah || array_sum( $depth ) || '' === trim( $tok ) ) {
			continue;
		}
		$found     = array();
		$found_ids = array();
		foreach ( $items as $item ) {
			if ( isset( $used[ $item['id'] ] ) || isset( $found_ids[ $item['id'] ] ) || ( $budget < 1 && empty( $item['always'] ) ) ) {
				continue;
			}
			$needle = preg_quote( htmlspecialchars( $item['phrase'], ENT_NOQUOTES, 'UTF-8' ), '/' );
			$needle = str_replace( '&amp;', '(?:&amp;|&)', $needle );
			$needle = str_replace( "'", "(?:'|&#8217;|&#039;|\xE2\x80\x99)", $needle );
			$mm = boi_gl_first_match( boi_gl_rx( $needle ), $tok, ! empty( $item['name'] ) );
			if ( $mm ) {
				list( $start, $end ) = $mm;
				foreach ( $found as $f ) {
					if ( $start < $f['end'] && $end > $f['start'] ) {
						continue 2;
					}
				}
				$found[]                  = array( 'start' => $start, 'end' => $end, 'item' => $item );
				$found_ids[ $item['id'] ] = true;
				if ( count( $found ) >= 3 || ( empty( $item['always'] ) && count( $found ) >= $budget ) ) {
					break;
				}
			}
		}
		if ( ! $found ) {
			continue;
		}
		usort( $found, function ( $x, $y ) {
			return $y['start'] - $x['start'];
		} );
		foreach ( $found as $f ) {
			$link = '<a href="' . esc_url( $f['item']['url'] ) . '" class="boi-gl-link">' . substr( $tok, $f['start'], $f['end'] - $f['start'] ) . '</a>' . ( isset( $f['item']['epithet'] ) ? $f['item']['epithet'] : '' );
			$tok  = substr_replace( $tok, $link, $f['start'], $f['end'] - $f['start'] );
			$used[ $f['item']['id'] ] = true;
			if ( empty( $f['item']['always'] ) ) {
				--$budget;
			}
		}
		$tokens[ $i ] = $tok;
	}
	return implode( '', $tokens );
}

$map   = test_map();
$files = glob( BOI_DIR . '/content/*.json' );
sort( $files );
if ( isset( $argv[1] ) ) {
	$files = array_slice( $files, 0, (int) $argv[1] );
}

// Each body as written, and texturized the way WordPress shows it
// (curly apostrophes, &amp;), so both forms of the patterns are exercised.
$bodies = array();
foreach ( $files as $file ) {
	$data = json_decode( (string) file_get_contents( $file ), true );
	if ( empty( $data['content'] ) ) {
		continue;
	}
	$bodies[ basename( $file ) ] = $data['content'];
	$bodies[ basename( $file ) . ' (texturized)' ] = str_replace( array( "'", ' & ' ), array( '&#8217;', ' &amp; ' ), $data['content'] );
}

$fail  = 0;
$links = 0;
$t_old = 0.0;
$t_new = 0.0;
foreach ( array( 12, 0, 100 ) as $budget ) {
	foreach ( $bodies as $name => $body ) {
		$t      = microtime( true );
		$old    = boi_gl_apply_old( $body, $map, $budget );
		$t_old += microtime( true ) - $t;

		$t        = microtime( true );
		$compiled = boi_gl_compiled( $map );
		$new      = boi_gl_apply( $body, $compiled, $budget );
		$t_new   += microtime( true ) - $t;

		if ( $old !== $new ) {
			++$fail;
			echo "DIFF: $name (budget $budget)\n";
		}
		$links += substr_count( $new, 'boi-gl-link' );
	}
}

// Fixed cases, checked against stated expectations rather than against the
// reference loop, so a guard that suppresses a real link fails here even
// though both loops agree. Each: text, then the marked-up form expected,
// where [phrase→slug] is a link and {} is the epithet after it.
$cases = array(
	'Joseph Smith and David Wood and Raymond Ibrahim spoke.' => 'Joseph Smith and David Wood and Raymond Ibrahim spoke.',
	'Edward Isma&#8217;il spoke.'                            => 'Edward Isma&#8217;il spoke.',
	'Isma&#8217;il al Faruqi wrote it.'                      => '[Isma&#8217;il al Faruqi→ismail-raji-al-faruqi] wrote it.',
	'Then the prophet Isma&#8217;il built it.'               => 'Then the prophet [Isma&#8217;il→ishmael]{} built it.',
	'Joseph Smith read of the prophet Joseph.'               => 'Joseph Smith read of the prophet [Joseph→joseph]{}.',
	'The Musnad of Ahmad has it.'                            => 'The [Musnad of Ahmad→musnad-al] has it.',
	'It is in Sahih al-Bukhari.'                             => 'It is in [Sahih al-Bukhari→sahih].',
	'Imam al-Bukhari reports it.'                            => '[Imam al-Bukhari→bukhari-al] reports it.',
	'The Holy Spirit came.'                                  => 'The [Holy Spirit→gabriel] came.',
	'The early Arabs knew its history.'                      => 'The early Arabs knew its history.',
	'It is in Sahih Muslim.'                                 => 'It is in [Sahih Muslim→sahih].',
	'Imam Muslim reports it.'                                => '[Imam Muslim→muslim-ibn-al-hajjaj] reports it.',
	'Al-Bukhari and Muslim report it.'                       => '[Al-Bukhari→bukhari-al] and Muslim report it.',
);
$compiled = boi_gl_compiled( $map );
foreach ( $cases as $in => $want ) {
	$got = boi_gl_apply( '<p>' . $in . '</p>', $compiled, 100 );
	$got = preg_replace( '#<a href="[^"]*/glossary/([^/]*)/" class="boi-gl-link">(.*?)</a>#', '[$2→$1]', $got );
	$got = preg_replace( '#&nbsp;<span class="boi-epithet[^"]*"[^>]*>[^<]*</span>#', '{}', $got );
	$got = substr( $got, 3, -4 );
	if ( $got !== $want ) {
		++$fail;
		echo "CASE: $in\n  want: $want\n  got:  $got\n";
	}
}

$n = count( $bodies ) * 3;
printf( "%d runs over %d bodies, %d map phrases, %d links added\n", $n, count( $bodies ), count( $map ), $links );
printf( "old: %.1f ms total, %.2f ms per body\n", $t_old * 1000, $t_old * 1000 / $n );
printf( "new: %.1f ms total, %.2f ms per body\n", $t_new * 1000, $t_new * 1000 / $n );
echo $fail ? "FAIL: $fail differ\n" : "OK: identical output, " . count( $cases ) . " fixed cases\n";
exit( $fail ? 1 : 0 );
