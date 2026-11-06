<?php
/**
 * Glossary links — articles link to the Glossary.
 *
 * Inside the paragraphs of a single article, the first mention of a term the
 * Glossary defines becomes an ordinary link to its page. Terms are the titles
 * and other names of published Glossary entries; common English words are
 * never linked. The reverse direction (term to articles) is each entry's
 * "Read more" list.
 *
 * @package bestofislam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Most links added to one article (filterable). */
function boi_gl_max() {
	return (int) apply_filters( 'boi_gl_max', 12 );
}

/**
 * Entries linked at their first mention in every article, even when the
 * article has used up its budget of links: the scholars the site relies on.
 *
 * @return string[] Entry slugs.
 */
function boi_gl_always() {
	return (array) apply_filters( 'boi_gl_always', array( 'ismail-raji-al-faruqi' ) );
}

/**
 * Words too common to link, whatever entry they belong to.
 *
 * @return string[] Lowercase.
 */
function boi_gl_stoplist() {
	$words = array(
		'abu', 'the prophet', 'prophet', 'animals', 'belief', 'believers', 'blind', 'book', 'burial', 'children', 'concealment', 'crystal', 'daughters',
		'devil', 'devils', 'disability', 'disbelief', 'divorce', 'drugs', 'eden', 'education', 'equality', 'faith', 'family',
		'fear', 'finance', 'friday', 'gold', 'greetings', 'heaven', 'hell', 'heir', 'ill', 'images', 'inheritance', 'innovation',
		'inspiration', 'intellect', 'iron', 'judgement', 'judging', 'justice', 'killing', 'kindness', 'kinship', 'knowledge',
		'learning', 'literacy', 'mercy', 'messiah', 'murder', 'myths', 'pictures', 'rain', 'reason', 'revelation', 'ruling',
		'satan', 'study', 'submission', 'surrender', 'water', 'wills', 'wine', 'worship', 'fasting', 'ramadan', 'christ', 'jesus',
		'gospel', 'gospels', 'fables', 'cartoons', 'handicapped', 'fairness', 'compassion', 'bequests', 'id', 'eid',
	);

	return array_map( 'strtolower', (array) apply_filters( 'boi_gl_stoplist', $words ) );
}

/**
 * Phrase to link, longest first: array of array( phrase, url, slug ).
 * Built from published Glossary pages and kept for a while.
 *
 * @return array
 */
function boi_gl_map() {
	$key = 'boi_gl_map_' . BOI_GLOSSARY_VERSION;
	$map = get_transient( $key );

	if ( is_array( $map ) ) {
		return $map;
	}

	$map    = array();
	$parent = function_exists( 'boi_glossary_parent' ) ? boi_glossary_parent() : null;

	if ( $parent ) {
		$stop  = boi_gl_stoplist();
		$seen  = array();
		$pages = get_pages( array( 'child_of' => $parent->ID, 'post_status' => 'publish' ) );

		foreach ( $pages as $page ) {
			$names = array_merge( array( $page->post_title ), (array) get_post_meta( $page->ID, '_boi_term_aliases', true ) );

			foreach ( $names as $name ) {
				$name = trim( (string) $name );
				$low  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $name ) : strtolower( $name );

				if ( strlen( $name ) < 4 || in_array( $low, $stop, true ) || isset( $seen[ $low ] ) ) {
					continue;
				}

				$seen[ $low ] = true;
				$map[]        = array(
					'phrase'  => $name,
					'url'     => get_permalink( $page ),
					'id'      => $page->ID,
					'always'  => in_array( $page->post_name, boi_gl_always(), true ),
					'epithet' => function_exists( 'boi_glossary_epithet_html' ) ? boi_glossary_epithet_html( boi_glossary_term_by_slug( $page->post_name ) ) : '',
				);
			}
		}

		usort( $map, function ( $a, $b ) {
			return strlen( $b['phrase'] ) - strlen( $a['phrase'] );
		} );
	}

	set_transient( $key, $map, 6 * HOUR_IN_SECONDS );

	return $map;
}

/** Forget the map when a Glossary page changes. */
function boi_gl_flush( $post_id = 0 ) {
	delete_transient( 'boi_gl_map_' . BOI_GLOSSARY_VERSION );
}
add_action( 'save_post_page', 'boi_gl_flush' );
add_action( 'trashed_post', 'boi_gl_flush' );
add_action( 'boi_glossary_continue', 'boi_gl_flush' );

/**
 * The map with each phrase's pattern compiled once, and a plain-text key for
 * a cheap first test. Held for the request, keyed by the map's own hash.
 *
 * The key is the longest run of the phrase that the pattern matches only
 * literally (ASCII, with no &, apostrophe or angle bracket). Under the /iu
 * flags an ASCII letter matches only its two ASCII cases, except k and s,
 * which also match the Kelvin sign and the long s; boi_gl_apply() tests every
 * phrase on text that holds either of those. A token without the key cannot
 * hold the phrase, so the full pattern is skipped. The items are also indexed
 * by the first three letters of their key, so a token is only walked against
 * the items whose three letters it contains, in the map's own order.
 *
 * @param array $items Map from boi_gl_map().
 * @return array Items with 'rx' and 'key' added.
 */
function boi_gl_compiled( $items ) {
	static $cache = array();

	$hash = md5( serialize( $items ) );

	if ( isset( $cache[ $hash ] ) ) {
		return $cache[ $hash ];
	}

	foreach ( $items as $n => $item ) {
		// The text is already escaped and texturized: match & as &amp;, and an apostrophe in any form.
		$needle = preg_quote( htmlspecialchars( $item['phrase'], ENT_NOQUOTES, 'UTF-8' ), '/' );
		$needle = str_replace( '&amp;', '(?:&amp;|&)', $needle );
		$needle = str_replace( "'", "(?:'|&#8217;|&#039;|\xE2\x80\x99)", $needle );

		$key = '';
		if ( preg_match_all( '/[\x20-\x25\x28-\x3B\x3D\x3F-\x7E]+/', (string) $item['phrase'], $runs ) ) {
			foreach ( $runs[0] as $run ) {
				if ( strlen( $run ) > strlen( $key ) ) {
					$key = $run;
				}
			}
		}

		$items[ $n ]['rx']  = '/(?<![\p{L}\p{N}])(' . $needle . ')(?![\p{L}\p{N}])/iu';
		$items[ $n ]['key'] = strtolower( $key );
	}

	$grams = array();
	$open  = array();
	foreach ( $items as $n => $item ) {
		if ( strlen( $item['key'] ) < 3 ) {
			$open[] = $n;
		} else {
			$grams[ substr( $item['key'], 0, 3 ) ][] = $n;
		}
	}

	$cache[ $hash ] = array( 'hash' => $hash, 'items' => $items, 'grams' => $grams, 'open' => $open );

	return $cache[ $hash ];
}

/**
 * Link first mentions inside paragraphs of single articles.
 *
 * @param string $content Post content.
 * @return string
 */
function boi_gl_filter( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() || ! function_exists( 'boi_glossary_parent' ) ) {
		return $content;
	}

	// Embeds, feeds and REST requests never take the links.
	if ( is_embed() || is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $content;
	}

	$items = boi_gl_map();

	if ( ! $items ) {
		return $content;
	}

	$compiled = boi_gl_compiled( $items );
	$budget   = boi_gl_max();
	$post_id  = (int) get_the_ID();
	// The same input, map and budget always give the same output, so the
	// result is kept on the post and repeat views do no work.
	$key    = md5( $compiled['hash'] . '|' . $budget . '|' . $content );
	$cached = $post_id ? get_post_meta( $post_id, '_boi_gl_cache', true ) : '';

	if ( is_array( $cached ) && isset( $cached['key'], $cached['html'] ) && $cached['key'] === $key ) {
		return $cached['html'];
	}

	$html = boi_gl_apply( $content, $compiled, $budget );

	if ( $post_id ) {
		$source = get_post( $post_id );

		update_post_meta(
			$post_id,
			'_boi_gl_cache',
			array(
				'key'    => $key,
				'html'   => $html,
				'map'    => $compiled['hash'],
				'budget' => $budget,
				'src'    => md5( $source ? (string) $source->post_content : '' ),
			)
		);
	}

	return $html;
}
add_filter( 'the_content', 'boi_gl_filter', 24 );

/**
 * Adds the links to one body of content.
 *
 * @param string $content Post content.
 * @param array  $compiled From boi_gl_compiled().
 * @param int    $budget  Most links, less the always-linked entries.
 * @return string
 */
function boi_gl_apply( $content, $compiled, $budget ) {
	$all    = $compiled['items'];
	$skip   = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'code', 'pre', 'script', 'style', 'button', 'figcaption', 'blockquote', 'cite' );
	$depth  = array_fill_keys( $skip, 0 );
	$used   = array();
	$in_p   = false;
	$lanah  = 0; // Open spans inside the laʿnat Allāh mark, which takes no link.
	// A tag ends at the first ">" outside a quoted attribute value, so an alt
	// text such as "a > b" never reads as running text.
	$tokens = preg_split( '/(<(?:[^>"\']|"[^"]*"|\'[^\']*\')*>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );

	// preg_split() gives false when PCRE stops (backtrack or JIT stack limit
	// on a long page). implode() below would then be a fatal TypeError, so
	// the page keeps its text without the links.
	if ( ! is_array( $tokens ) ) {
		return $content;
	}

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
		// The Kelvin sign and the long s fold to k and s, so the plain-text key
		// cannot be trusted on text that holds them.
		$prefilter = false === strpos( $tok, "\xE2\x84\xAA" ) && false === strpos( $tok, "\xC5\xBF" );
		$lower     = strtolower( $tok );
		$items     = $all;

		if ( $prefilter ) {
			$pick = $compiled['open'];
			$seen = array();
			$len  = strlen( $lower ) - 2;
			for ( $j = 0; $j < $len; $j++ ) {
				$gram = substr( $lower, $j, 3 );
				if ( ! isset( $seen[ $gram ] ) ) {
					$seen[ $gram ] = true;
					if ( isset( $compiled['grams'][ $gram ] ) ) {
						array_push( $pick, ...$compiled['grams'][ $gram ] );
					}
				}
			}
			sort( $pick );
			$items = array();
			foreach ( $pick as $n ) {
				$items[] = $all[ $n ];
			}
		}

		foreach ( $items as $item ) {
			// One link per page per paragraph: a title and an alias of the same
			// entry must not both be linked (and carry the epithet) at once.
			if ( isset( $used[ $item['id'] ] ) || isset( $found_ids[ $item['id'] ] ) || ( $budget < 1 && empty( $item['always'] ) ) ) {
				continue;
			}

			if ( $prefilter && '' !== $item['key'] && false === strpos( $lower, $item['key'] ) ) {
				continue;
			}

			if ( preg_match( $item['rx'], $tok, $mm, PREG_OFFSET_CAPTURE ) ) {
				$start = $mm[1][1];
				$end   = $start + strlen( $mm[1][0] );

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
			// The epithet rides on the first mention only, with the link.
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
