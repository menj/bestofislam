<?php
/**
 * The page graph: one JSON-LD graph per page, with or without Rank Math.
 *
 * Run: php tests/schema-graph-test.php
 *
 * WordPress is stubbed. Sample pages are rendered from the theme's own data:
 * the front page, a listicle and a Reflection from content/, the Glossary
 * index and an entry from inc/glossary-data/, the About and Contact pages, a
 * section archive, search results, an author archive and a 404. Each page is
 * printed by the theme (one script element, JSON that decodes) and merged
 * into a stand-in Rank Math graph (nothing printed, Rank Math's nodes kept).
 * In both, every @id must be defined once, every reference must name a node
 * of the graph, every node must carry a type the theme is known to use, and
 * each kind of node must carry its required properties. Every type the theme
 * marked before the single graph must still appear. Exit code 0 on success.
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );
define( 'BOI_URI', 'https://bestofislam.test/wp-content/themes/bestofislam' );
define( 'BOI_LISTICLE_META', '_boi_listicle' );
define( 'RANK_MATH_VERSION', 'stub' );
define( 'OBJECT', 'OBJECT' );
define( 'HOUR_IN_SECONDS', 3600 );

$GLOBALS['t'] = array(
	'view'  => 'front',
	'obj'   => null,
	'rm'    => false,
	'posts' => array(),
	'meta'  => array(),
	'terms' => array(),
);

eval( 'namespace RankMath; class Helper { public static function is_module_active( $m ) { return ! empty( $GLOBALS["t"]["rm"] ); } public static function is_breadcrumbs_enabled() { return ! empty( $GLOBALS["t"]["rm"] ); } }' ); // phpcs:ignore

class WP_Post {
	public $ID;
	public $post_name;
	public $post_type    = 'post';
	public $post_parent  = 0;
	public $post_title   = '';
	public $post_content = '';
	public $post_excerpt = '';
	public $post_status  = 'publish';
	public $post_author  = 1;
	public function __construct( $props ) {
		foreach ( $props as $k => $v ) {
			$this->$k = $v;
		}
	}
}
class WP_Term {
	public $term_id;
	public $name;
	public $slug;
	public $taxonomy;
	public $parent = 0;
	public $count  = 3;
	public function __construct( $props ) {
		foreach ( $props as $k => $v ) {
			$this->$k = $v;
		}
	}
}
class WP_User {
	public $ID           = 1;
	public $display_name = 'Site Author';
	public $user_url     = '';
}
class WP_Error {}

// ---------- WordPress stubs ----------
function add_action() {}
function add_filter() {}
function remove_action() {}
function remove_filter() {}
function add_shortcode() {}
function register_activation_hook() {}
function do_action() {}
function apply_filters( $tag, $value ) {
	return $value;
}
function __( $s ) {
	return $s;
}
function esc_html__( $s ) {
	return $s;
}
function esc_attr__( $s ) {
	return $s;
}
function _n( $a ) {
	return $a;
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_attr( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_url( $s ) {
	return (string) $s;
}
function esc_url_raw( $s ) {
	return (string) $s;
}
function wp_strip_all_tags( $s ) {
	return trim( strip_tags( preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $s ) ) );
}
function strip_shortcodes( $s ) {
	return $s;
}
function wp_json_encode( $d, $f = 0 ) {
	return json_encode( $d, $f );
}
function wp_parse_url( $u, $c = -1 ) {
	return parse_url( $u, $c );
}
function set_url_scheme( $u ) {
	return $u;
}
function trailingslashit( $s ) {
	return rtrim( $s, '/' ) . '/';
}
function untrailingslashit( $s ) {
	return rtrim( $s, '/' );
}
function sanitize_title( $s ) {
	$s = strtolower( remove_accents( wp_strip_all_tags( (string) $s ) ) );
	return trim( preg_replace( '/[^a-z0-9]+/', '-', $s ), '-' );
}
function remove_accents( $s ) {
	$t = @iconv( 'UTF-8', 'ASCII//TRANSLIT//IGNORE', $s );
	return false === $t ? $s : $t;
}
function sanitize_key( $s ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $s ) );
}
function home_url( $path = '' ) {
	return 'https://bestofislam.test' . ( '' === $path ? '' : '/' . ltrim( $path, '/' ) );
}
function get_bloginfo( $what = '' ) {
	$info = array(
		'name'        => 'Best of Islam',
		'description' => 'Islam, explained from its sources',
		'language'    => 'en-GB',
	);
	return isset( $info[ $what ] ) ? $info[ $what ] : '';
}
function get_option( $name ) {
	return 'page_for_posts' === $name ? 90 : false;
}
function get_site_icon_url() {
	return '';
}
function get_stylesheet() {
	return 'bestofislam';
}
function wp_get_theme() {
	return new class() {
		public function get( $k ) {
			return 'https://example.org/';
		}
	};
}
function wp_get_document_title() {
	$o = $GLOBALS['t']['obj'];
	return ( $o instanceof WP_Post ? $o->post_title : ( $o instanceof WP_Term ? $o->name : 'Best of Islam' ) ) . ' | Best of Islam';
}
function is_wp_error( $x ) {
	return $x instanceof WP_Error;
}
function wp_list_pluck( $list, $field ) {
	return array_map(
		function ( $o ) use ( $field ) {
			return is_object( $o ) ? $o->$field : $o[ $field ];
		},
		$list
	);
}
function wp_parse_args( $a, $d ) {
	return array_merge( $d, (array) $a );
}

// Views.
function boi_t_view( $v ) {
	return $GLOBALS['t']['view'] === $v;
}
function is_front_page() {
	return boi_t_view( 'front' );
}
function is_home() {
	return false;
}
function is_feed() {
	return false;
}
function is_embed() {
	return false;
}
function is_paged() {
	return false;
}
function is_singular( $type = '' ) {
	$o = $GLOBALS['t']['obj'];
	return in_array( $GLOBALS['t']['view'], array( 'post', 'page' ), true ) && $o instanceof WP_Post && ( '' === $type || $o->post_type === $type );
}
function is_page() {
	return is_singular( 'page' );
}
function is_author() {
	return boi_t_view( 'author' );
}
function is_search() {
	return boi_t_view( 'search' );
}
function is_404() {
	return boi_t_view( '404' );
}
function is_tax( $tax = '' ) {
	return boi_t_view( 'tax' );
}
function is_category() {
	return false;
}
function is_tag() {
	return false;
}
function is_archive() {
	return boi_t_view( 'tax' ) || boi_t_view( 'author' );
}
function is_post_type_archive() {
	return false;
}
function get_query_var() {
	return 0;
}
function get_queried_object() {
	return $GLOBALS['t']['obj'];
}
function get_the_ID() {
	return $GLOBALS['t']['obj'] instanceof WP_Post ? $GLOBALS['t']['obj']->ID : 0;
}
function get_post( $id = null ) {
	if ( null === $id ) {
		return $GLOBALS['t']['obj'] instanceof WP_Post ? $GLOBALS['t']['obj'] : null;
	}
	$id = $id instanceof WP_Post ? $id->ID : (int) $id;
	return isset( $GLOBALS['t']['posts'][ $id ] ) ? $GLOBALS['t']['posts'][ $id ] : null;
}
function get_post_path( $post ) {
	$path = $post->post_name;
	while ( $post->post_parent && ( $post = get_post( $post->post_parent ) ) ) {
		$path = $post->post_name . '/' . $path;
	}
	return $path;
}
function get_permalink( $post = null ) {
	$post = null === $post ? get_post() : get_post( $post );
	if ( ! $post ) {
		return '';
	}
	return 'home' === $post->post_name ? home_url( '/' ) : home_url( '/' . get_post_path( $post ) . '/' );
}
function get_the_title( $post = null ) {
	$post = null === $post ? get_post() : get_post( $post );
	return $post ? $post->post_title : '';
}
function single_post_title() {
	return get_the_title();
}
function get_page_by_path( $path, $o = null, $type = 'page' ) {
	foreach ( $GLOBALS['t']['posts'] as $post ) {
		if ( $post->post_type === $type && get_post_path( $post ) === trim( $path, '/' ) ) {
			return $post;
		}
	}
	return null;
}
function get_pages( $args ) {
	return array_values(
		array_filter(
			$GLOBALS['t']['posts'],
			function ( $p ) use ( $args ) {
				$parent = isset( $args['parent'] ) ? $args['parent'] : $args['child_of'];
				return 'page' === $p->post_type && (int) $p->post_parent === (int) $parent;
			}
		)
	);
}
function get_posts() {
	return array_values(
		array_filter(
			$GLOBALS['t']['posts'],
			function ( $p ) {
				return 'post' === $p->post_type && ! get_post_meta( $p->ID, BOI_LISTICLE_META, true );
			}
		)
	);
}
function get_post_meta( $id, $key = '', $single = false ) {
	return isset( $GLOBALS['t']['meta'][ $id ][ $key ] ) ? $GLOBALS['t']['meta'][ $id ][ $key ] : '';
}
function update_post_meta() {}
function has_excerpt( $post ) {
	return '' !== get_post( $post )->post_excerpt;
}
function get_the_excerpt( $post ) {
	return get_post( $post )->post_excerpt;
}
function get_post_time() {
	return '2026-10-01T10:00:00+00:00';
}
function get_post_modified_time() {
	return '2026-11-01T10:00:00+00:00';
}
function get_the_date() {
	return '1 October 2026';
}
function get_post_thumbnail_id() {
	return 0;
}
function wp_get_attachment_image_src() {
	return false;
}
function wp_get_attachment_image_url() {
	return false;
}
function get_the_terms( $post, $tax ) {
	$id = $post instanceof WP_Post ? $post->ID : (int) $post;
	return isset( $GLOBALS['t']['terms'][ $id ][ $tax ] ) ? $GLOBALS['t']['terms'][ $id ][ $tax ] : false;
}
function get_term_link( $term ) {
	return home_url( '/topic/' . $term->slug . '/' );
}
function get_term( $id ) {
	return null;
}
function single_term_title() {
	return $GLOBALS['t']['obj']->name;
}
function get_the_archive_title() {
	return is_author() ? 'Author: Site Author' : 'Archive';
}
function get_search_link() {
	return home_url( '/search/prayer/' );
}
function get_search_query() {
	return 'prayer';
}
function get_userdata() {
	return new WP_User();
}
function get_author_posts_url() {
	return home_url( '/author/site-author/' );
}
function count_user_posts() {
	return 3;
}
function get_the_author_meta() {
	return 'Writes for the site.';
}
function get_feed_link() {
	return home_url( '/feed/' );
}
function get_block_template() {
	return null;
}
function wp_getimagesize( $f ) {
	return @getimagesize( $f );
}
function has_block( $name, $post ) {
	$post = get_post( $post );
	return $post && false !== strpos( $post->post_content, '<!-- wp:' . $name . ' ' ) || ( $post && false !== strpos( $post->post_content, '<!-- wp:' . $name . ' -->' ) );
}

/**
 * Enough of the block parser for the theme's content: comment delimiters with
 * JSON attributes, self-closing or wrapping inner blocks.
 */
function parse_blocks( $content ) {
	$re    = '/<!--\s+(\/)?wp:([a-z][a-z0-9_-]*(?:\/[a-z][a-z0-9_-]*)?)\s+(\{(?:(?!-->).)*?\}\s+)?(\/)?-->/s';
	$stack = array( array( 'innerBlocks' => array(), 'innerHTML' => '' ) );
	$pos   = 0;
	while ( preg_match( $re, $content, $m, PREG_OFFSET_CAPTURE, $pos ) ) {
		$text = substr( $content, $pos, $m[0][1] - $pos );
		$top  = count( $stack ) - 1;
		if ( $top > 0 ) {
			$stack[ $top ]['innerHTML'] .= $text;
		}
		$pos   = $m[0][1] + strlen( $m[0][0] );
		$name  = false === strpos( $m[2][0], '/' ) ? 'core/' . $m[2][0] : $m[2][0];
		$attrs = ! empty( $m[3][0] ) ? (array) json_decode( trim( $m[3][0] ), true ) : array();
		if ( ! empty( $m[1][0] ) ) {
			$block = array_pop( $stack );
			$stack[ count( $stack ) - 1 ]['innerBlocks'][] = $block;
			continue;
		}
		$block = array(
			'blockName'   => $name,
			'attrs'       => $attrs,
			'innerBlocks' => array(),
			'innerHTML'   => '',
		);
		if ( ! empty( $m[4][0] ) ) {
			$stack[ count( $stack ) - 1 ]['innerBlocks'][] = $block;
		} else {
			$stack[] = $block;
		}
	}
	return $stack[0]['innerBlocks'];
}

foreach ( array( 'util', 'structured-data', 'schema', 'questions', 'glossary', 'seo', 'schema-graph', 'rank-math', 'images', 'inline-images', 'glossary-links', 'unlist', 'setup', 'blocks' ) as $boi_file ) {
	require BOI_DIR . '/inc/' . $boi_file . '.php';
}

// From modules the sample does not load.
function boi_add_heading_icon( $html ) {
	return $html;
}
function boi_get_options() {
	return array();
}
function get_users() {
	return array();
}
function get_transient() {
	return false;
}
function set_transient() {}
function boi_follow_networks() {
	return array();
}

// ---------- The sample site ----------
$fail = 0;
function boi_t_fail( $msg ) {
	global $fail;
	echo "FAIL: $msg\n";
	++$fail;
}
function boi_t_add( $props ) {
	$post                               = new WP_Post( $props );
	$GLOBALS['t']['posts'][ $post->ID ] = $post;
	return $post;
}
function boi_t_content( $slug ) {
	$data = json_decode( (string) file_get_contents( BOI_DIR . '/content/' . $slug . '.json' ), true );
	return $data['content'];
}

$seed  = json_decode( (string) file_get_contents( BOI_DIR . '/inc/data/seed.json' ), true );
$by    = array_column( $seed['listicles'], null, 'slug' );
$topic = new WP_Term( array( 'term_id' => 7, 'name' => 'Belief & Practices', 'slug' => 'belief-and-practices', 'taxonomy' => 'listicle-topic' ) );

boi_t_add( array( 'ID' => 1, 'post_name' => 'home', 'post_type' => 'page', 'post_title' => 'Home' ) );
boi_t_add( array( 'ID' => 90, 'post_name' => 'reflections', 'post_type' => 'page', 'post_title' => 'Reflections' ) );
$listicle = boi_t_add(
	array(
		'ID'           => 10,
		'post_name'    => 'does-the-quran-allow-wife-beating',
		'post_title'   => isset( $by['does-the-quran-allow-wife-beating'] ) ? $by['does-the-quran-allow-wife-beating']['title'] : 'Wife beating',
		'post_excerpt' => 'What the verse says.',
		'post_content' => boi_t_content( 'does-the-quran-allow-wife-beating' ),
	)
);
$quotes   = boi_t_add(
	array(
		'ID'           => 11,
		'post_name'    => 'quran-anachronism-claims',
		'post_title'   => 'Quran anachronism claims',
		'post_content' => boi_t_content( 'quran-anachronism-claims' ),
	)
);
$reflect  = boi_t_add(
	array(
		'ID'           => 12,
		'post_name'    => 'a-reflection',
		'post_title'   => 'A reflection on mercy',
		'post_excerpt' => 'On mercy in the Quran.',
		'post_content' => "<!-- wp:paragraph -->\n<p>The Quran says \"My mercy encompasses all things\" (Quran 7:156), and Muhammad taught it.</p>\n<!-- /wp:paragraph -->",
	)
);
$GLOBALS['t']['meta'][10][ BOI_LISTICLE_META ] = '1';
$GLOBALS['t']['meta'][11][ BOI_LISTICLE_META ] = '1';
$GLOBALS['t']['terms'][10]['listicle-topic']   = array( $topic );
$GLOBALS['t']['terms'][10]['post_tag']         = array( new WP_Term( array( 'name' => 'Marriage', 'slug' => 'marriage' ) ) );

$glossary = boi_t_add( array( 'ID' => 20, 'post_name' => 'glossary', 'post_type' => 'page', 'post_title' => 'Glossary', 'post_excerpt' => 'The words of Islam.' ) );
$n        = 21;
$entry    = null;
foreach ( array_slice( boi_glossary_terms(), 0, 6 ) as $term ) {
	$page = boi_t_add(
		array(
			'ID'           => $n,
			'post_name'    => $term['slug'],
			'post_type'    => 'page',
			'post_parent'  => 20,
			'post_title'   => $term['title'],
			'post_excerpt' => $term['def'],
			'post_content' => boi_glossary_content( $term ),
		)
	);
	$GLOBALS['t']['meta'][ $n ] = array(
		'_boi_term_ar'      => isset( $term['ar'] ) ? $term['ar'] : '',
		'_boi_term_type'    => isset( $term['type'] ) ? $term['type'] : 'term',
		'_boi_term_aliases' => isset( $term['aliases'] ) ? $term['aliases'] : array(),
	);
	$entry = $entry ? $entry : $page;
	++$n;
}
$about   = boi_t_add( array( 'ID' => 40, 'post_name' => 'about', 'post_type' => 'page', 'post_title' => 'About' ) );
$contact = boi_t_add( array( 'ID' => 41, 'post_name' => 'contact', 'post_type' => 'page', 'post_title' => 'Contact', 'post_content' => '<!-- wp:bestofislam/contact-form /-->' ) );

global $wp_query;
$wp_query = (object) array( 'posts' => array( $listicle, $quotes ) );

$views = array(
	'front'          => array( 'front', get_post( 1 ), array( 'WebPage', 'FAQPage' ) ),
	'listicle'       => array( 'post', $listicle, array( 'WebPage' ) ),
	'quotes'         => array( 'post', $quotes, array( 'WebPage' ) ),
	'reflection'     => array( 'post', $reflect, array( 'WebPage' ) ),
	'glossary'       => array( 'page', $glossary, array( 'CollectionPage' ) ),
	'glossary-entry' => array( 'page', $entry, array( 'WebPage', 'ItemPage' ) ),
	'about'          => array( 'page', $about, array( 'AboutPage' ) ),
	'contact'        => array( 'page', $contact, array( 'ContactPage' ) ),
	'topic'          => array( 'tax', $topic, array( 'CollectionPage' ) ),
	'search'         => array( 'search', null, array( 'SearchResultsPage' ) ),
	'author'         => array( 'author', new WP_User(), array( 'ProfilePage' ) ),
	'404'            => array( '404', null, array( 'WebPage' ) ),
);

// Every type the theme uses. A new one is added here on purpose.
$known = array( 'WebSite', 'WebPage', 'Organization', 'Person', 'ProfilePage', 'Article', 'BlogPosting', 'BreadcrumbList', 'ImageObject', 'ItemList', 'ListItem', 'CollectionPage', 'DefinedTermSet', 'DefinedTerm', 'FAQPage', 'Question', 'Answer', 'WPHeader', 'WPFooter', 'SiteNavigationElement', 'SearchAction', 'EntryPoint', 'SpeakableSpecification', 'AboutPage', 'ContactPage', 'ItemPage', 'SearchResultsPage', 'Quotation', 'Chapter', 'Book', 'CreativeWork', 'PropertyValue', 'Thing', 'Claim' );
// Every type marked before the graph was one.
$before = array( 'WebSite', 'WebPage', 'Organization', 'Person', 'ProfilePage', 'Article', 'BreadcrumbList', 'ImageObject', 'ItemList', 'ListItem', 'CollectionPage', 'DefinedTermSet', 'DefinedTerm', 'FAQPage', 'Question', 'Answer' );
$seen  = array();

/**
 * Walks a value, calling $fn on every array that carries @type or @id.
 */
function boi_t_walk( $value, $fn ) {
	if ( ! is_array( $value ) ) {
		return;
	}
	if ( isset( $value['@type'] ) || isset( $value['@id'] ) ) {
		$fn( $value );
	}
	foreach ( $value as $v ) {
		boi_t_walk( $v, $fn );
	}
}

/**
 * Checks one graph; returns the types it holds. $website_id is the @id the
 * graph's one WebSite node must carry.
 */
function boi_t_check( $label, $nodes, $known, $page_types, $website_id ) {
	$defined = array();
	$refs    = array();
	$types   = array();
	$byid    = array();

	foreach ( $nodes as $node ) {
		if ( empty( $node['@type'] ) ) {
			boi_t_fail( "$label: a top-level node has no @type: " . json_encode( $node ) );
		}
	}

	boi_t_walk(
		$nodes,
		function ( $node ) use ( &$defined, &$refs, &$types, &$byid, $label ) {
			$keys = array_diff( array_keys( $node ), array( '@id' ) );
			if ( isset( $node['@id'] ) && ! $keys ) {
				$refs[] = $node['@id'];
				return;
			}
			if ( isset( $node['@id'] ) ) {
				if ( isset( $defined[ $node['@id'] ] ) ) {
					boi_t_fail( "$label: @id defined twice: " . $node['@id'] );
				}
				$defined[ $node['@id'] ] = true;
				$byid[ $node['@id'] ]    = $node;
				if ( false === strpos( $node['@id'], '#' ) || 0 !== strpos( $node['@id'], 'https://' ) ) {
					boi_t_fail( "$label: @id is not an address with a fragment: " . $node['@id'] );
				}
			}
			foreach ( (array) ( isset( $node['@type'] ) ? $node['@type'] : array() ) as $t ) {
				$types[ $t ] = true;
			}
		}
	);

	$sites = array_keys(
		array_filter(
			$byid,
			function ( $n ) {
				return in_array( 'WebSite', (array) $n['@type'], true );
			}
		)
	);
	if ( array( $website_id ) !== $sites ) {
		boi_t_fail( "$label: expected one WebSite node $website_id, got " . ( $sites ? implode( ', ', $sites ) : 'none' ) );
	}

	foreach ( array_unique( $refs ) as $ref ) {
		if ( ! isset( $defined[ $ref ] ) ) {
			boi_t_fail( "$label: reference to a node not in the graph: $ref" );
		}
	}

	foreach ( array_keys( $types ) as $t ) {
		if ( ! in_array( $t, $known, true ) ) {
			boi_t_fail( "$label: unexpected type $t" );
		}
	}

	$required = array(
		'WebSite'               => array( 'name', 'url', 'potentialAction' ),
		'Organization'          => array( 'name', 'url', 'logo' ),
		'BreadcrumbList'        => array( 'itemListElement' ),
		'Article'               => array( 'headline', 'datePublished', 'dateModified', 'author', 'publisher', 'mainEntityOfPage' ),
		'ItemList'              => array( 'itemListElement' ),
		'DefinedTerm'           => array( 'name', 'url' ),
		'DefinedTermSet'        => array( 'name' ),
		'Question'              => array( 'name', 'acceptedAnswer' ),
		'Quotation'             => array( 'isPartOf' ),
		'Chapter'               => array( 'name', 'position', 'isPartOf' ),
		'ImageObject'           => array( 'contentUrl' ),
		'SiteNavigationElement' => array( 'name' ),
		'WPHeader'              => array( 'hasPart' ),
		'WPFooter'              => array( 'hasPart' ),
	);

	foreach ( $byid as $id => $node ) {
		foreach ( $required as $type => $props ) {
			if ( in_array( $type, (array) $node['@type'], true ) ) {
				foreach ( $props as $p ) {
					if ( ! isset( $node[ $p ] ) ) {
						boi_t_fail( "$label: $type $id lacks $p" );
					}
				}
			}
		}
	}

	// hasPart takes a CreativeWork; an ItemList is not one.
	foreach ( $byid as $id => $node ) {
		foreach ( isset( $node['hasPart'] ) ? ( isset( $node['hasPart']['@id'] ) ? array( $node['hasPart'] ) : (array) $node['hasPart'] ) : array() as $part ) {
			$ref = isset( $part['@id'] ) && isset( $byid[ $part['@id'] ] ) ? $byid[ $part['@id'] ] : $part;
			if ( isset( $ref['@type'] ) && in_array( 'ItemList', (array) $ref['@type'], true ) ) {
				boi_t_fail( "$label: $id has the ItemList " . ( isset( $ref['@id'] ) ? $ref['@id'] : '' ) . ' as hasPart' );
			}
		}
	}

	// Every list the theme adds is still linked from another node.
	foreach ( $byid as $id => $node ) {
		if ( ! in_array( 'ItemList', (array) $node['@type'], true ) || ( false === strpos( $id, '#itemlist' ) && false === strpos( $id, '#reflections' ) ) ) {
			continue;
		}
		if ( false === strpos( (string) json_encode( array_diff_key( $byid, array( $id => 1 ) ) ), json_encode( array( '@id' => $id ) ) ) ) {
			boi_t_fail( "$label: nothing refers to the list $id" );
		}
	}

	$page = null;
	foreach ( $byid as $node ) {
		if ( array_intersect( (array) $node['@type'], $page_types ) && isset( $node['url'] ) ) {
			$page = $node;
			break;
		}
	}

	if ( ! $page ) {
		boi_t_fail( "$label: no page node of type " . implode( '/', $page_types ) );
	} else {
		foreach ( array( 'url', 'name', 'inLanguage', 'isPartOf' ) as $p ) {
			if ( ! isset( $page[ $p ] ) ) {
				boi_t_fail( "$label: page node lacks $p" );
			}
		}
	}

	foreach ( array( 'WPHeader', 'WPFooter', 'SiteNavigationElement', 'SearchAction', 'WebSite', 'Organization' ) as $t ) {
		if ( ! isset( $types[ $t ] ) ) {
			boi_t_fail( "$label: no $t" );
		}
	}

	// The author (owner rule, 4 November 2026): one Person, #author, named
	// Mohd Elfie Nieshaem Juferi at https://menj.bio with sameAs including
	// it; every author value points to it, and every article and page node
	// has one.
	$author_id = home_url( '/#author' );
	$owner     = isset( $byid[ $author_id ] ) ? $byid[ $author_id ] : null;
	if ( ! $owner ) {
		boi_t_fail( "$label: no author Person" );
	} else {
		if ( 'Mohd Elfie Nieshaem Juferi' !== ( isset( $owner['name'] ) ? $owner['name'] : '' ) || 'https://menj.bio' !== ( isset( $owner['url'] ) ? $owner['url'] : '' ) ) {
			boi_t_fail( "$label: author Person has the wrong name or url" );
		}
		if ( ! in_array( 'https://menj.bio', (array) ( isset( $owner['sameAs'] ) ? $owner['sameAs'] : array() ), true ) ) {
			boi_t_fail( "$label: author sameAs lacks https://menj.bio" );
		}
	}
	$people = 0;
	foreach ( $byid as $id => $node ) {
		if ( in_array( 'Person', (array) $node['@type'], true ) && isset( $node['name'] ) && 'Mohd Elfie Nieshaem Juferi' === $node['name'] ) {
			$people++;
		}
	}
	if ( $people > 1 ) {
		boi_t_fail( "$label: $people author Person nodes" );
	}
	$bad  = array();
	$scan = function ( $n ) use ( &$scan, &$bad, $author_id ) {
		if ( ! is_array( $n ) ) {
			return;
		}
		foreach ( $n as $k => $v ) {
			if ( 'author' === $k && ( ! is_array( $v ) || ! isset( $v['@id'] ) || $author_id !== $v['@id'] || count( $v ) > 1 ) ) {
				$bad[] = json_encode( $v );
			} elseif ( is_array( $v ) ) {
				$scan( $v );
			}
		}
	};
	$scan( $nodes );
	if ( $bad ) {
		boi_t_fail( "$label: an author does not point to $author_id: " . $bad[0] );
	}
	foreach ( $nodes as $node ) {
		if ( array_intersect( (array) $node['@type'], array( 'Article', 'BlogPosting', 'WebPage', 'ProfilePage', 'CollectionPage', 'AboutPage', 'FAQPage' ) ) && empty( $node['author'] ) ) {
			boi_t_fail( "$label: " . ( isset( $node['@id'] ) ? $node['@id'] : '' ) . ' has no author' );
		}
	}

	return $types;
}

foreach ( $views as $label => $view ) {
	list( $kind, $obj, $page_types ) = $view;
	$GLOBALS['t']['view']            = $kind;
	$GLOBALS['t']['obj']             = $obj;

	// Without Rank Math: the theme prints one graph.
	$GLOBALS['t']['rm'] = false;
	ob_start();
	boi_print_schema_graph();
	$html = ob_get_clean();

	if ( 1 !== substr_count( $html, '<script type="application/ld+json">' ) ) {
		boi_t_fail( "$label: expected one JSON-LD script, got " . substr_count( $html, 'application/ld+json' ) );
		continue;
	}

	$json = json_decode( preg_replace( '#^\s*<script type="application/ld\+json">|</script>\s*$#', '', $html ), true );

	if ( ! is_array( $json ) || empty( $json['@graph'] ) || 'https://schema.org' !== $json['@context'] ) {
		boi_t_fail( "$label: the script does not hold a schema.org graph" );
		continue;
	}

	$seen += boi_t_check( $label, $json['@graph'], $known, $page_types, boi_entity_id( 'website' ) );

	// The breadcrumb block prints only the trail now.
	if ( 'front' !== $kind && false !== strpos( (string) boi_render_breadcrumbs(), 'ld+json' ) ) {
		boi_t_fail( "$label: the breadcrumb block still prints JSON-LD" );
	}

	// With Rank Math: nothing printed, the nodes merged into its graph.
	$GLOBALS['t']['rm'] = true;
	ob_start();
	boi_print_schema_graph();
	if ( '' !== ob_get_clean() ) {
		boi_t_fail( "$label: the theme printed JSON-LD beside Rank Math" );
	}

	$url = boi_schema_page_url();

	// Rank Math as it ships (#website, as the theme spells it), with its own
	// spelling of the WebSite @id, and with no WebSite node of its own.
	foreach ( array( 'Rank Math' => home_url( '/#website' ), 'Rank Math, own #website' => home_url( '/#rm-website' ), 'Rank Math, no WebSite' => null ) as $rm_label => $site_id ) {
		$rm = array(
			'WebSite'        => array( '@type' => 'WebSite', '@id' => $site_id, 'name' => 'Best of Islam', 'url' => home_url( '/' ), 'potentialAction' => array( '@type' => 'SearchAction', 'target' => home_url( '/?s={s}' ) ) ),
			'publisher'      => array( '@type' => 'Organization', '@id' => home_url( '/#organization' ), 'name' => 'Best of Islam', 'url' => home_url( '/' ), 'logo' => array( '@type' => 'ImageObject', '@id' => home_url( '/#rmlogo' ), 'contentUrl' => 'https://bestofislam.test/logo.png' ) ),
			'WebPage'        => array( '@type' => 'WebPage', '@id' => $url . '#webpage', 'url' => $url, 'name' => 'RM', 'inLanguage' => 'en-GB', 'isPartOf' => array( '@id' => null === $site_id ? boi_entity_id( 'website' ) : $site_id ) ),
			'BreadcrumbList' => array( '@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb', 'itemListElement' => array( array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home' ) ) ),
		);
		if ( null === $site_id ) {
			unset( $rm['WebSite'] );
		}
		if ( 'post' === $kind ) {
			$rm['richSnippet'] = array( '@type' => 'Article', '@id' => $url . '#richSnippet', 'headline' => 'RM', 'datePublished' => 'x', 'dateModified' => 'x', 'author' => array( '@type' => 'Person', 'name' => 'A' ), 'publisher' => array( '@id' => home_url( '/#organization' ) ), 'mainEntityOfPage' => array( '@id' => $url . '#webpage' ) );
		}

		$merged = boi_rank_math_json_ld( $rm );
		$enc    = json_encode( array( '@context' => 'https://schema.org', '@graph' => array_values( $merged ) ) );

		if ( ! $enc || ! json_decode( $enc, true ) ) {
			boi_t_fail( "$label ($rm_label): the merged graph does not encode" );
			continue;
		}

		if ( 'RM' !== $merged['WebPage']['name'] ) {
			boi_t_fail( "$label ($rm_label): Rank Math's own value was overwritten" );
		}

		$articles = 0;
		foreach ( $merged as $node ) {
			if ( isset( $node['@type'] ) && array_intersect( (array) $node['@type'], array( 'Article' ) ) && isset( $node['@id'] ) && 0 === strpos( $node['@id'], $url ) ) {
				++$articles;
			}
		}
		if ( 'post' === $kind && 1 !== $articles ) {
			boi_t_fail( "$label ($rm_label): $articles Article nodes for the page" );
		}

		$rm_page_types = 'author' === $kind ? array( 'ProfilePage' ) : $page_types;
		$seen         += boi_t_check( "$label ($rm_label)", array_values( $merged ), $known, $rm_page_types, null === $site_id ? boi_entity_id( 'website' ) : $site_id );
	}
}

// Specific content.
$GLOBALS['t']['rm']   = false;
$GLOBALS['t']['view'] = 'post';
$GLOBALS['t']['obj']  = $listicle;
$graph                = boi_schema_graph_nodes();
$graded               = array_filter(
	$graph,
	function ( $n ) {
		return 'Quotation' === $n['@type'] && isset( $n['identifier'] ) && is_array( $n['identifier'] );
	}
);
if ( ! $graded ) {
	boi_t_fail( 'listicle: no graded hadith Quotation from the notes' );
}
foreach ( $graph as $n ) {
	if ( isset( $n['headline'] ) && false !== strpos( (string) $n['@id'], '#article' ) && ( ! isset( $n['mainEntity'] ) || false === strpos( (string) json_encode( $n['mainEntity'] ), '#itemlist' ) ) ) {
		boi_t_fail( 'listicle: the Article does not name its list as mainEntity' );
	}
}
foreach ( $graded as $q ) {
	$g = end( $q['identifier'] );
	if ( 'grading' !== $g['propertyID'] || ! preg_match( '/^(hasan sahih|sahih|hasan)$/', $g['value'] ) ) {
		boi_t_fail( 'listicle: a grading is not a PropertyValue named grading: ' . json_encode( $q ) );
	}
}

$GLOBALS['t']['obj'] = $quotes;
$texts               = array();
foreach ( boi_schema_graph_nodes() as $n ) {
	if ( 'Quotation' === $n['@type'] && isset( $n['text'] ) ) {
		$texts[] = $n['text'];
		if ( false === strpos( boi_schema_plain_text( $quotes->post_content ), $n['text'] ) ) {
			boi_t_fail( 'quotes: a Quotation text is not on the page: ' . $n['text'] );
		}
	}
}
if ( count( $texts ) < 2 ) {
	boi_t_fail( 'quotes: expected the quoted verses as Quotations, got ' . count( $texts ) );
}

$GLOBALS['t']['obj'] = $reflect;
$art                 = null;
foreach ( boi_schema_graph_nodes() as $n ) {
	if ( in_array( 'BlogPosting', (array) $n['@type'], true ) && isset( $n['headline'] ) && false !== strpos( $n['@id'], 'a-reflection/#article' ) ) {
		$art = $n;
	}
}
if ( ! $art || empty( $art['abstract'] ) || empty( $art['mentions'] ) || empty( $art['citation'] ) || empty( $art['about'] ) ) {
	boi_t_fail( 'reflection: the BlogPosting lacks abstract, about, mentions or citation' );
}

foreach ( $before as $type ) {
	if ( ! isset( $seen[ $type ] ) ) {
		boi_t_fail( "type $type, marked before, no longer appears on any page" );
	}
}

echo $fail ? "$fail failure(s)\n" : 'OK: ' . count( $views ) . " pages, each one graph with and without Rank Math\n";
exit( $fail ? 1 : 0 );
