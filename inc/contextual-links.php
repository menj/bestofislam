<?php
/**
 * Contextual links — in-text links to the inner pages that treat a topic.
 *
 * In the running text of a single article or page, the first mention of a
 * known phrase links to the article on this site, or the inner page on one of
 * the six sister sites, that treats it at length. Phrases come from three
 * places, the first to name a phrase winning: the post's own list (meta box),
 * the site-wide list (Appearance → Contextual Links → Phrases), and the shared
 * map in inc/data/network-links.json, a file kept identical in all seven
 * themes of the network. Nothing is labelled or styled differently from the
 * theme's normal links.
 *
 * Runs at 22, before the Glossary links (24), so an article link takes the
 * words first; the Glossary pass never links inside an existing link. A
 * paragraph receives at most one link from this pass, and the Glossary pass
 * keeps its own rule (at most three per paragraph); a later Glossary link on
 * the same words this pass linked is unwrapped at 26, so one phrase never
 * leads to two places in one article.
 *
 * @package bestofislam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BOI_CL_OPTION', 'boi_cl' );
define( 'BOI_CL_META', '_' . 'boi_cl_phrases' );
define( 'BOI_CL_SITE', 'bestofislam' );

/** Post types that receive links (filterable). */
function boi_cl_post_types() {
	return (array) apply_filters( 'boi_cl_post_types', array( 'post', 'page' ) );
}

function boi_cl_options() {
	$saved = get_option( BOI_CL_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();
	// Settings saved before 1.75.0 had one switch and one cap, both for links to other sites.
	if ( isset( $saved['enabled'] ) && ! isset( $saved['sister'] ) ) {
		$saved['sister'] = $saved['enabled'];
	}
	if ( isset( $saved['max'] ) && ! isset( $saved['max_sister'] ) ) {
		$saved['max_sister'] = min( 10, (int) $saved['max'] );
	}
	return array_merge(
		array(
			'own'        => 1,
			'sister'     => 1,
			'max_own'    => 4,
			'max_sister' => 3,
			'domains'    => '',
			'phrases'    => '',
			'exclude'    => '',
			'new_tab'    => 0,
		),
		$saved
	);
}

/** Normalised host: lowercase, no www. */
function boi_cl_host( $url ) {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	return preg_replace( '/^www\./', '', $host );
}

/** Lowercase, entity-free, straight-apostrophe, single-space form of a phrase or matched text. */
function boi_cl_key( $text ) {
	$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = str_replace( array( "\xE2\x80\x99", "\xE2\x80\x98", "\xCA\xBC" ), "'", $text );
	// A non-breaking space (the editor writes one for a doubled space) reads as a space.
	$text = preg_replace( '/\s+/', ' ', str_replace( "\xC2\xA0", ' ', $text ) );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
}

/**
 * Whether lowered plain text contains a phrase key. Without mbstring only
 * ASCII is lowered, so a non-ASCII key is looked for case-insensitively.
 */
function boi_cl_contains( $plain, $key ) {
	if ( function_exists( 'mb_strtolower' ) || ! preg_match( '/[^\x00-\x7F]/', $key ) ) {
		return false !== strpos( $plain, $key );
	}
	return 1 === preg_match( '/' . preg_quote( $key, '/' ) . '/iu', $plain );
}

/**
 * The entry in a key-indexed map for a key, or null. Without mbstring a
 * non-ASCII key keeps its capitals, so it is compared case-insensitively.
 */
function boi_cl_lookup( array $map, $key ) {
	if ( isset( $map[ $key ] ) ) {
		return $key;
	}
	if ( function_exists( 'mb_strtolower' ) || ! preg_match( '/[^\x00-\x7F]/', $key ) ) {
		return null;
	}
	foreach ( array_keys( $map ) as $candidate ) {
		if ( 1 === preg_match( '/^' . preg_quote( (string) $candidate, '/' ) . '$/iu', $key ) ) {
			return $candidate;
		}
	}
	return null;
}

/** URL without query, fragment or trailing slash, for comparing pages. */
function boi_cl_norm_url( $url ) {
	$url = preg_replace( '/[?#].*$/', '', (string) $url );
	return strtolower( untrailingslashit( $url ) );
}

/**
 * The shared map, compiled: every phrase with its address and whether the
 * address is on this site. Own-site paths go through home_url() so the links
 * hold on a staging copy.
 *
 * @return array{items:array<int,array{phrase:string,url:string,own:bool}>,hosts:array<string,bool>}
 */
function boi_cl_network() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array( 'items' => array(), 'hosts' => array() );
	$file  = get_theme_file_path( 'inc/data/network-links.json' );
	$data  = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$data  = apply_filters( 'boi_contextual_links_map', is_array( $data ) ? $data : array() );
	$sites = isset( $data['sites'] ) && is_array( $data['sites'] ) ? $data['sites'] : array();
	foreach ( $sites as $key => $base ) {
		if ( BOI_CL_SITE !== $key && boi_cl_host( $base ) ) {
			$cache['hosts'][ boi_cl_host( $base ) ] = true;
		}
	}
	foreach ( isset( $data['links'] ) && is_array( $data['links'] ) ? $data['links'] : array() as $link ) {
		if ( empty( $link['site'] ) || empty( $link['path'] ) || empty( $link['phrases'] ) || ! isset( $sites[ $link['site'] ] ) ) {
			continue;
		}
		$own = BOI_CL_SITE === $link['site'];
		$url = $own ? home_url( $link['path'] ) : untrailingslashit( $sites[ $link['site'] ] ) . $link['path'];
		foreach ( (array) $link['phrases'] as $phrase ) {
			$cache['items'][] = array( 'phrase' => (string) $phrase, 'url' => $url, 'own' => $own );
		}
	}
	return $cache;
}

/**
 * Parse "phrase | address" lines. An address is a path on this site
 * ("/slug/"), a full address on this site, or a full address on a sister site
 * or an approved domain; anything else is dropped.
 */
function boi_cl_parse( $text, $hosts ) {
	$own_host = boi_cl_host( home_url() );
	$out      = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 !== count( $parts ) || '' === $parts[0] || '' === $parts[1] ) {
			continue;
		}
		if ( '/' === $parts[1][0] && '/' !== substr( $parts[1], 1, 1 ) ) {
			$out[] = array( 'phrase' => $parts[0], 'url' => home_url( $parts[1] ), 'own' => true );
			continue;
		}
		$url  = esc_url_raw( $parts[1], array( 'http', 'https' ) );
		$host = $url ? boi_cl_host( $url ) : '';
		if ( $host && $host === $own_host ) {
			$out[] = array( 'phrase' => $parts[0], 'url' => $url, 'own' => true );
		} elseif ( $host && isset( $hosts[ $host ] ) ) {
			$out[] = array( 'phrase' => $parts[0], 'url' => $url, 'own' => false );
		}
	}
	return $out;
}

/**
 * Names that contain a mapped phrase but mean something else. They are
 * matched, longest first, and never linked, so "Ismail" in "Ismail al Faruqi"
 * is not taken for the prophet.
 *
 * @return string[]
 */
function boi_cl_guards() {
	return (array) apply_filters( 'boi_cl_guards', array( 'Ismail al Faruqi', 'Ismail al-Faruqi', 'Ismail R. al Faruqi', 'Ismail Raji al Faruqi', 'Ismail Raji al-Faruqi' ) );
}

/** Hosts a link may lead to besides this site: the sister sites and the Domains tab. */
function boi_cl_allowed_hosts( $opts ) {
	$own   = boi_cl_host( home_url() );
	$hosts = boi_cl_network()['hosts'];
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $opts['domains'] ) as $line ) {
		$line = trim( $line );
		$h    = boi_cl_host( false === strpos( $line, '//' ) ? 'https://' . $line : $line );
		if ( $h && $h !== $own ) {
			$hosts[ $h ] = true;
		}
	}
	return $hosts;
}

/** Whether this view gets links. */
function boi_cl_applies() {
	if ( is_admin() || is_feed() || is_front_page() || ! is_singular( boi_cl_post_types() ) || ! is_main_query() || doing_filter( 'get_the_excerpt' ) ) {
		return false;
	}
	// Only the page's own text: never a card or excerpt rendered inside it.
	if ( (int) get_the_ID() !== (int) get_queried_object_id() ) {
		return false;
	}
	$post    = get_post();
	$exclude = array_filter( array_map( 'trim', preg_split( '/[\s,]+/', (string) boi_cl_options()['exclude'] ) ) );
	if ( $post && in_array( $post->post_name, $exclude, true ) ) {
		return false;
	}
	return (bool) apply_filters( 'boi_contextual_links_enabled', true, $post );
}

/** Keys of the words linked on this request, for the Glossary unwrap at 26. */
function boi_cl_linked( $add = null ) {
	static $keys = array();
	if ( null !== $add ) {
		$keys[ $add ] = true;
	}
	return $keys;
}

/**
 * Link the first mention of each phrase in the running text.
 *
 * @param string $content Rendered content.
 * @return string
 */
function boi_cl_filter( $content ) {
	if ( false === strpos( $content, '<' ) || ! boi_cl_applies() ) {
		return $content;
	}
	$opts = boi_cl_options();
	$caps = array(
		'own'    => empty( $opts['own'] ) ? 0 : max( 0, min( 15, (int) $opts['max_own'] ) ),
		'sister' => empty( $opts['sister'] ) ? 0 : max( 0, min( 10, (int) $opts['max_sister'] ) ),
	);
	if ( ! $caps['own'] && ! $caps['sister'] ) {
		return $content;
	}
	$hosts = boi_cl_allowed_hosts( $opts );
	$all   = array_merge(
		boi_cl_parse( get_post_meta( get_the_ID(), BOI_CL_META, true ), $hosts ),
		boi_cl_parse( $opts['phrases'], $hosts ),
		boi_cl_network()['items']
	);

	// Keep the first line for each phrase, and only phrases this text contains.
	$plain = boi_cl_key( wp_strip_all_tags( $content ) );
	$here  = boi_cl_norm_url( get_permalink() );
	$items = array();
	foreach ( $all as $item ) {
		$key = boi_cl_key( $item['phrase'] );
		if ( isset( $items[ $key ] ) || '' === $key || ! $caps[ $item['own'] ? 'own' : 'sister' ] || boi_cl_norm_url( $item['url'] ) === $here || ! boi_cl_contains( $plain, $key ) ) {
			continue;
		}
		$items[ $key ] = $item;
	}
	if ( ! $items ) {
		return $content;
	}
	$guards = array();
	foreach ( boi_cl_guards() as $guard ) {
		$key = boi_cl_key( $guard );
		if ( '' !== $key && ! isset( $items[ $key ] ) && boi_cl_contains( $plain, $key ) ) {
			$guards[ $key ] = array( 'phrase' => $guard );
		}
	}

	// One pattern, longest phrase first. The text is escaped and texturized,
	// so & may appear as &amp; and an apostrophe in any of its forms.
	$terms = $items + $guards;
	$keys  = array_keys( $terms );
	usort(
		$keys,
		static function ( $a, $b ) {
			return strlen( $b ) <=> strlen( $a );
		}
	);
	$alts = array();
	foreach ( $keys as $key ) {
		// Curly apostrophes become straight, so every form of the apostrophe matches.
		$phrase = str_replace( array( "\xE2\x80\x99", "\xE2\x80\x98", "\xCA\xBC" ), "'", $terms[ $key ]['phrase'] );
		$needle = preg_quote( htmlspecialchars( $phrase, ENT_NOQUOTES, 'UTF-8' ), '/' );
		$needle = str_replace( '&amp;', '(?:&amp;|&)', $needle );
		$needle = str_replace( "'", "(?:'|&#8217;|&#8216;|&#0?39;|&rsquo;|&lsquo;|&apos;|\xE2\x80\x99|\xE2\x80\x98|\xCA\xBC)", $needle );
		$alts[] = str_replace( ' ', "(?:\\s|&nbsp;|&#160;|\xC2\xA0)+", $needle );
	}
	$pattern = '/(?<![\p{L}\p{N}])(?:' . implode( '|', $alts ) . ')(?![\p{L}\p{N}])/iu';
	// A pattern past PCRE's size limit (admin phrases can grow it) would fail
	// on every paragraph; the text is then left unlinked rather than warned on.
	if ( false === @preg_match( $pattern, '' ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return $content;
	}
	$epithets = boi_cl_epithet_index();

	$skip_tags  = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'q', 'figure', 'figcaption', 'cite', 'code', 'pre', 'sup', 'sub', 'nav', 'table', 'button', 'label', 'select', 'textarea', 'summary', 'script', 'style' );
	$skip_attr  = '/\b(?:lang="(?:ar|he|el|syr)[^"]*"|dir="rtl"|class="[^"]*\b(?:boi-jump|boi-entry__claim|boi-entry__cta|boi-entry__rank|screen-reader-text|wp-block-footnotes|footnotes|arabic|quran|hadith|verse|toc|boi-glossary__list)\b[^"]*")/i';
	$void       = array( 'br', 'img', 'hr', 'wbr', 'input', 'source', 'meta', 'link', 'col', 'area', 'embed', 'track' );
	$notes_head = '/^(?:notes?|sources?|references?|bibliography|further reading|endnotes)$/i';
	$rel        = ' rel="noopener"' . ( ! empty( $opts['new_tab'] ) ? ' target="_blank"' : '' );

	// A tag ends at the first ">" outside a quoted attribute value, so an alt
	// text such as "a > b" never reads as running text.
	$tokens   = preg_split( '/(<(?:[^>"\']|"[^"]*"|\'[^\']*\')*>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	$open     = array(); // Every open element: array( tag, forbids links ).
	$skipping = 0;       // Open elements that forbid links.
	$blocks   = array(); // Indexes in $open of the open p and li elements.
	$linked   = array(); // Indexes in $open of the p and li that received a link.
	$heading  = null;    // Text of the heading being read.
	$in_notes = false;   // Below a Notes or Sources heading, until the next h2.
	$done     = array();
	$made     = array( 'own' => 0, 'sister' => 0 );
	if ( false === $tokens ) {
		return $content;
	}
	foreach ( $tokens as $i => $tok ) {
		if ( '' === $tok ) {
			continue;
		}
		if ( '<' === $tok[0] ) {
			if ( ! preg_match( '#^<(/?)([a-zA-Z][a-zA-Z0-9]*)#', $tok, $m ) ) {
				continue;
			}
			$tag = strtolower( $m[2] );
			if ( '/' === $m[1] ) {
				// Close the innermost element of this name and anything left open inside it.
				for ( $j = count( $open ) - 1; $j >= 0; $j-- ) {
					if ( $open[ $j ][0] !== $tag ) {
						continue;
					}
					while ( count( $open ) > $j ) {
						$k     = count( $open ) - 1;
						$entry = array_pop( $open );
						$skipping -= $entry[1] ? 1 : 0;
						if ( $blocks && end( $blocks ) === $k ) {
							array_pop( $blocks );
							unset( $linked[ $k ] );
						}
					}
					break;
				}
				if ( preg_match( '/^h[1-6]$/', $tag ) && null !== $heading ) {
					$text = trim( html_entity_decode( $heading, ENT_QUOTES, 'UTF-8' ) );
					if ( preg_match( $notes_head, $text ) ) {
						$in_notes = true;
					} elseif ( 'h2' === $tag ) {
						$in_notes = false;
					}
					$heading = null;
				}
				continue;
			}
			if ( in_array( $tag, $void, true ) || '/' === substr( $tok, -2, 1 ) ) {
				continue;
			}
			$forbid = in_array( $tag, $skip_tags, true ) || preg_match( $skip_attr, $tok );
			$open[] = array( $tag, $forbid );
			$skipping += $forbid ? 1 : 0;
			if ( 'p' === $tag || 'li' === $tag ) {
				$blocks[] = count( $open ) - 1;
			}
			if ( preg_match( '/^h[1-6]$/', $tag ) ) {
				$heading = '';
			}
			continue;
		}
		if ( null !== $heading ) {
			$heading .= $tok;
		}
		if ( $skipping || $in_notes || ! $blocks || isset( $linked[ end( $blocks ) ] ) || '' === trim( $tok ) ) {
			continue;
		}
		// Scan forward one match at a time. A match passed over (a guard, a page
		// already linked, a full cap) resumes the scan one character on, so a
		// phrase running past its end ("Paul of Tarsus" after "Peter and Paul")
		// can still link; one wholly inside it ("Barnabas" in "Gospel of
		// Barnabas") stays part of that name.
		$pos    = 0;
		$shadow = 0;
		$len    = strlen( $tok );
		while ( $pos < $len && preg_match( $pattern, $tok, $found, PREG_OFFSET_CAPTURE, $pos ) ) {
			$hit  = $found[0];
			$end  = $hit[1] + strlen( $hit[0] );
			$key  = boi_cl_lookup( $items, boi_cl_key( $hit[0] ) );
			$item = null !== $key ? $items[ $key ] : null;
			$kind = $item && $item['own'] ? 'own' : 'sister';
			$norm = $item ? boi_cl_norm_url( $item['url'] ) : '';
			if ( $end <= $shadow || ! $item || isset( $done[ $norm ] ) || $made[ $kind ] >= $caps[ $kind ] ) {
				$shadow = max( $shadow, $end );
				$byte   = ord( $tok[ $hit[1] ] );
				$pos    = $hit[1] + ( $byte >= 0xF0 ? 4 : ( $byte >= 0xE0 ? 3 : ( $byte >= 0xC0 ? 2 : 1 ) ) );
				continue;
			}
			$class = 'boi-ctx-link' . ( $item['own'] ? '' : ' boi-ctx-link--sister' );
			$link  = '<a class="' . $class . '" href="' . esc_url( $item['url'] ) . '"' . ( $item['own'] ? '' : $rel ) . '>' . $hit[0] . '</a>';
			// The Glossary gives a name its epithet at its first mention, with
			// its own link; this link now takes that mention, so it carries the
			// epithet, and the Glossary link that would follow is unwrapped
			// with its epithet (boi_cl_unwrap_glossary()).
			$gl = null !== boi_cl_lookup( $epithets, $key ) ? $epithets[ boi_cl_lookup( $epithets, $key ) ] : null;
			if ( $gl ) {
				$link .= $gl['epithet'];
				boi_cl_epithet_urls( $gl['url'] );
			}
			$tokens[ $i ]  = substr_replace( $tok, $link, $hit[1], strlen( $hit[0] ) );
			$done[ $norm ] = true;
			++$made[ $kind ];
			$linked[ end( $blocks ) ] = true;
			boi_cl_linked( $key );
			break;
		}
		if ( $made['own'] >= $caps['own'] && $made['sister'] >= $caps['sister'] ) {
			break;
		}
	}
	return implode( '', $tokens );
}
add_filter( 'the_content', 'boi_cl_filter', 22 );

/**
 * Glossary names that carry an epithet, by phrase key, where the Glossary
 * pass runs (single articles): array( key => array( epithet, url ) ).
 *
 * @return array<string,array{epithet:string,url:string}>
 */
function boi_cl_epithet_index() {
	if ( ! function_exists( 'boi_gl_map' ) || ! function_exists( 'boi_glossary_parent' ) || ! is_singular( 'post' ) || ! in_the_loop() ) {
		return array();
	}
	$index = array();
	foreach ( (array) boi_gl_map() as $item ) {
		$key = boi_cl_key( isset( $item['phrase'] ) ? $item['phrase'] : '' );
		if ( '' !== $key && ! empty( $item['epithet'] ) && ! isset( $index[ $key ] ) ) {
			$index[ $key ] = array( 'epithet' => $item['epithet'], 'url' => $item['url'] );
		}
	}
	return $index;
}

/**
 * Glossary pages whose epithet a contextual link already carries on this
 * request. With an address, records it.
 *
 * @param string|null $url Glossary page address.
 * @return array<string,bool>
 */
function boi_cl_epithet_urls( $url = null ) {
	static $urls = array();
	if ( null !== $url ) {
		$urls[ esc_url( $url ) ] = true;
	}
	return $urls;
}

/**
 * Words this pass linked keep that one link: a later Glossary link on the
 * same words is taken back to plain text (the epithet stays). A Glossary link
 * to an entry whose epithet a contextual link already carries is taken back
 * to plain text with its epithet, so the epithet appears once.
 */
function boi_cl_unwrap_glossary( $content ) {
	$keys = boi_cl_linked();
	$urls = boi_cl_epithet_urls();
	if ( ( ! $keys && ! $urls ) || false === strpos( $content, 'boi-gl-link' ) || ! boi_cl_applies() ) {
		return $content;
	}
	$out = preg_replace_callback(
		'#<a href="([^"]*)" class="boi-gl-link">(.*?)</a>((?:&nbsp;|\s)*<span class="boi-epithet[^"]*"[^>]*>.*?</span>)?#s',
		static function ( $m ) use ( $keys, $urls ) {
			$epithet = isset( $m[3] ) ? $m[3] : '';
			if ( isset( $urls[ $m[1] ] ) ) {
				return $m[2];
			}
			return null !== boi_cl_lookup( $keys, boi_cl_key( wp_strip_all_tags( $m[2] ) ) ) ? $m[2] . $epithet : $m[0];
		},
		$content
	);
	return null === $out ? $content : $out;
}
add_filter( 'the_content', 'boi_cl_unwrap_glossary', 26 );

/* ── Per-post phrases ──────────────────────────────────────────────── */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'boi_cl', __( 'Contextual links', 'bestofislam' ), function ( $post ) {
		wp_nonce_field( 'boi_cl_meta', 'boi_cl_nonce' );
		echo '<p class="description">' . esc_html__( 'One per line: phrase | address. The address is a path on this site (/slug/) or a page on a sister site or an approved domain. These win over the site-wide list and the shared map.', 'bestofislam' ) . '</p>';
		printf( '<textarea class="widefat" rows="5" name="boi_cl_phrases">%s</textarea>', esc_textarea( (string) get_post_meta( $post->ID, BOI_CL_META, true ) ) );
	}, boi_cl_post_types(), 'side' );
} );

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['boi_cl_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['boi_cl_nonce'] ) ), 'boi_cl_meta' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, BOI_CL_META, sanitize_textarea_field( wp_unslash( isset( $_POST['boi_cl_phrases'] ) ? $_POST['boi_cl_phrases'] : '' ) ) );
} );

/* ── Settings page (tabbed) ────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_theme_page( __( 'Contextual Links', 'bestofislam' ), __( 'Contextual Links', 'bestofislam' ), 'manage_options', 'boi_cl', 'boi_cl_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'boi_cl', BOI_CL_OPTION, array( 'sanitize_callback' => 'boi_cl_sanitize' ) );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'appearance_page_boi_cl' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'boi_cl-admin', get_theme_file_uri( 'assets/css/contextual-links-admin.css' ), array(), filemtime( get_theme_file_path( 'assets/css/contextual-links-admin.css' ) ) );
	wp_enqueue_script( 'boi_cl-admin', get_theme_file_uri( 'assets/js/contextual-links-admin.js' ), array(), filemtime( get_theme_file_path( 'assets/js/contextual-links-admin.js' ) ), true );
} );

function boi_cl_sanitize( $in ) {
	$in = is_array( $in ) ? $in : array();
	return array(
		'own'        => empty( $in['own'] ) ? 0 : 1,
		'sister'     => empty( $in['sister'] ) ? 0 : 1,
		'max_own'    => isset( $in['max_own'] ) ? min( 15, max( 0, (int) $in['max_own'] ) ) : 4,
		'max_sister' => isset( $in['max_sister'] ) ? min( 10, max( 0, (int) $in['max_sister'] ) ) : 3,
		'domains'    => sanitize_textarea_field( isset( $in['domains'] ) ? $in['domains'] : '' ),
		'phrases'    => sanitize_textarea_field( isset( $in['phrases'] ) ? $in['phrases'] : '' ),
		'exclude'    => sanitize_textarea_field( isset( $in['exclude'] ) ? $in['exclude'] : '' ),
		'new_tab'    => empty( $in['new_tab'] ) ? 0 : 1,
	);
}

function boi_cl_page() {
	$o   = boi_cl_options();
	$n   = BOI_CL_OPTION;
	$map = boi_cl_network();
	$own = count( array_filter( wp_list_pluck( $map['items'], 'own' ) ) );
	?>
	<div class="wrap cl-admin">
		<h1><?php esc_html_e( 'Contextual Links', 'bestofislam' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'boi_cl' ); ?>
			<nav class="cl-admin__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Contextual Links settings', 'bestofislam' ); ?>">
				<button type="button" class="cl-admin__tab" role="tab" data-tab="domains"><?php esc_html_e( 'Domains', 'bestofislam' ); ?></button>
				<button type="button" class="cl-admin__tab" role="tab" data-tab="phrases"><?php esc_html_e( 'Phrases', 'bestofislam' ); ?></button>
				<button type="button" class="cl-admin__tab" role="tab" data-tab="rules"><?php esc_html_e( 'Rules', 'bestofislam' ); ?></button>
			</nav>

			<section class="cl-admin__panel" data-panel="domains">
				<p class="description"><?php esc_html_e( 'The six sister sites in the shared map are always approved. Add any other domain links may lead to, one per line.', 'bestofislam' ); ?></p>
				<p class="cl-admin__hosts"><?php echo esc_html( implode( ', ', array_keys( $map['hosts'] ) ) ); ?></p>
				<textarea class="large-text code" rows="8" name="<?php echo esc_attr( $n ); ?>[domains]"><?php echo esc_textarea( $o['domains'] ); ?></textarea>
			</section>

			<section class="cl-admin__panel" data-panel="phrases">
				<p class="description">
					<?php
					/* translators: 1: phrases to this site, 2: phrases to sister sites. */
					echo esc_html( sprintf( __( 'The shared map (inc/data/network-links.json) links %1$d phrases to articles here and %2$d to inner pages on the sister sites. Lines below come first and win: phrase | /path/ for this site, or phrase | https://… for a sister site or approved domain.', 'bestofislam' ), $own, count( $map['items'] ) - $own ) );
					?>
				</p>
				<textarea class="large-text code" rows="14" name="<?php echo esc_attr( $n ); ?>[phrases]"><?php echo esc_textarea( $o['phrases'] ); ?></textarea>
			</section>

			<section class="cl-admin__panel" data-panel="rules">
				<div class="cl-admin__row"><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[own]" value="1" <?php checked( $o['own'] ); ?>> <?php esc_html_e( 'Link to articles on this site', 'bestofislam' ); ?></label></div>
				<div class="cl-admin__row"><label><?php esc_html_e( 'At most, per page', 'bestofislam' ); ?> <input type="number" min="0" max="15" name="<?php echo esc_attr( $n ); ?>[max_own]" value="<?php echo esc_attr( $o['max_own'] ); ?>"></label></div>
				<div class="cl-admin__row"><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[sister]" value="1" <?php checked( $o['sister'] ); ?>> <?php esc_html_e( 'Link to inner pages on the sister sites', 'bestofislam' ); ?></label></div>
				<div class="cl-admin__row"><label><?php esc_html_e( 'At most, per page', 'bestofislam' ); ?> <input type="number" min="0" max="10" name="<?php echo esc_attr( $n ); ?>[max_sister]" value="<?php echo esc_attr( $o['max_sister'] ); ?>"></label></div>
				<div class="cl-admin__row"><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[new_tab]" value="1" <?php checked( $o['new_tab'] ); ?>> <?php esc_html_e( 'Open links to other sites in a new tab', 'bestofislam' ); ?></label></div>
				<div class="cl-admin__row cl-admin__row--stack">
					<label for="boi-cl-exclude"><?php esc_html_e( 'Never add links on these pages (slugs, one per line)', 'bestofislam' ); ?></label>
					<textarea id="boi-cl-exclude" class="large-text code" rows="4" name="<?php echo esc_attr( $n ); ?>[exclude]"><?php echo esc_textarea( $o['exclude'] ); ?></textarea>
				</div>
				<p class="description"><?php esc_html_e( 'Links go in paragraphs and list items only, at the first mention, one per paragraph, never in headings, quotations, captions, notes or an existing link, and never to the page itself.', 'bestofislam' ); ?></p>
			</section>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
