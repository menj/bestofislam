<?php
/**
 * The abstract (inc/abstract.php): every seeded article has one, the box goes
 * at the top of the main text, a block or shortcode in the body replaces it,
 * the switch hides it, and the text follows the voice rules. WordPress is
 * replaced by stubs.
 *
 * Run: php tests/abstract-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );
define( 'BOI_URI', '' );

class WP_Post {
	public $post_content = '';
	public $post_name    = '';
	public $post_type    = 'post';
}

$GLOBALS['t_post']    = new WP_Post();
$GLOBALS['t_single']  = true;
$GLOBALS['t_options'] = array();

function add_filter() {}
function add_action() {}
function add_shortcode() {}
function __( $s ) { return $s; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function wptexturize( $s ) { return str_replace( "'", '&#8217;', $s ); }
function wpautop( $s ) { return '<p>' . $s . '</p>'; }
function wp_kses( $s ) { return $s; }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function is_singular( $t = '' ) { return $GLOBALS['t_single']; }
function boi_anatomy_is_main() { return true; }
function get_queried_object() { return $GLOBALS['t_post']; }
function get_post( $p = null ) { return $p instanceof WP_Post ? $p : $GLOBALS['t_post']; }
function has_block( $name, $post ) { return false !== strpos( $post->post_content, '<!-- wp:' . $name ); }
function parse_blocks( $c ) {
	if ( preg_match( '/<!-- wp:bestofislam\/abstract (\{.*?\}) \/-->/', $c, $m ) ) {
		return array( array( 'blockName' => 'bestofislam/abstract', 'attrs' => json_decode( $m[1], true ) ) );
	}
	return array();
}
// The same groups as WordPress: 3 attributes, 4 self-closing slash, 5 content.
function get_shortcode_regex() { return '\[(\[?)(abstract)(?![\w-])([^\]\/]*)(\/?)\](.*?)\[\/abstract\](\]?)'; }
function shortcode_parse_atts( $s ) { return array(); }
function boi_get_options() {
	return array_merge( array( 'abstract_enabled' => 1, 'abstract_title' => 'Abstract', 'abstract_style' => 'ruled', 'abstract_scheme' => 'theme', 'abstract_accent' => '' ), $GLOBALS['t_options'] );
}

require BOI_DIR . '/inc/abstract.php';
require BOI_DIR . '/inc/lead-list.php';

$fails = 0;
$count = 0;
function check( $ok, $label ) {
	global $fails, $count;
	++$count;
	if ( ! $ok ) {
		++$fails;
		echo "FAIL: $label\n";
	}
}

// Every seeded article has an abstract of 55 to 110 words that keeps the voice rules.
$seed  = json_decode( file_get_contents( BOI_DIR . '/inc/data/seed.json' ), true );
$data  = boi_abstract_data();
$slugs = array_column( $seed['listicles'], 'slug' );
check( array() === array_diff( $slugs, array_keys( $data ) ), 'every seeded article has an abstract' );
check( array() === array_diff( array_keys( $data ), $slugs ), 'no abstract for an article that does not exist' );
foreach ( $data as $slug => $text ) {
	$words = str_word_count( $text );
	check( $words >= 55 && $words <= 110, "$slug: 55 to 110 words ($words)" );
	check( ! preg_match( '/\x{2014}|\x{2013}/u', $text ), "$slug: no em or en dash" );
	check( ! preg_match( "/\\b\\w+n't\\b|\\b(it|that|there|we|they|what)'s\\b/i", $text ), "$slug: no contractions" );
	check( false === strpos( $text, 'Muhammad' ) || false !== strpos( $text, 'peace be upon him' ), "$slug: (peace be upon him) with Muhammad" );
}

// Every colour preset keeps the label at 4.5:1 in both appearances.
foreach ( boi_abstract_schemes() as $slug => $scheme ) {
	if ( '' !== $scheme[1] ) {
		check( boi_lead_list_accent_ok( $scheme[1] ), "preset $slug reaches 4.5:1 in light and dark" );
	}
}

// The Excerpt is for a general reader: 150 characters at most, with a
// mild call to action. The Abstract has none (SSOT D67).
foreach ( $seed['listicles'] as $item ) {
	check( mb_strlen( $item['excerpt'] ) <= 150, $item['slug'] . ': excerpt within 150 characters (' . mb_strlen( $item['excerpt'] ) . ')' );
	$last = preg_split( '/(?<=[.!?])\s+/', trim( $item['excerpt'] ) );
	check( (bool) preg_match( '/^(Read|See|Weigh|Find|Learn|Check|Start|Test|Follow|Judge|Discover|Explore|Look|Hear|Meet|Get|Take|Compare|Decide)\b/', end( $last ) ), $item['slug'] . ': excerpt ends with a mild call to action' );
	check( $item['excerpt'] !== ( isset( $data[ $item['slug'] ] ) ? $data[ $item['slug'] ] : '' ), $item['slug'] . ': excerpt and abstract differ' );
}
foreach ( $data as $slug => $text ) {
	check( ! preg_match( '/(^|[.!?]\s+)(Read(?! (as|with|without|that|in|alone|together|whole|slowly|this))|See|Find out|Click|Learn|Discover|Explore)\b[^.!?]*[.!?]?\s*$/', $text ), "$slug: no call to action in the abstract" );
}

// The box at the top of the main text.
$GLOBALS['t_post']->post_name = 'al-zutt-allegation';
$body = '<p>Opening paragraph.</p>';
$out  = boi_abstract_filter( $body );
check( 0 === strpos( $out, '<aside class="boi-abstract boi-abstract--ruled" id="abstract"' ), 'the box opens the main text' );
check( false !== strpos( $out, 'Musnad of Ahmad ibn Hanbal' ), 'the bundled text is used' );
check( substr( $out, -strlen( $body ) ) === $body, 'the body follows unchanged' );
check( $out === boi_abstract_filter( $out ), 'a second pass adds nothing' );
check( '' !== boi_abstract_text( $GLOBALS['t_post'] ), 'the schema text is there' );

// Style and colour from Theme Options.
$GLOBALS['t_options'] = array( 'abstract_style' => 'academic', 'abstract_scheme' => 'rose' );
$out                  = boi_abstract_filter( $body );
check( false !== strpos( $out, 'boi-abstract--academic' ), 'the saved style' );
check( false !== strpos( $out, '--boi-abstract-accent:#b0457a' ), 'the preset accent' );
$GLOBALS['t_options'] = array( 'abstract_scheme' => 'custom', 'abstract_accent' => '#2f7d4f' );
check( false !== strpos( boi_abstract_filter( $body ), '--boi-abstract-accent:#2f7d4f' ), 'the custom accent' );
$GLOBALS['t_options'] = array();

// A block in the body replaces the bundled box, and its text goes to the schema.
$GLOBALS['t_post']->post_content = '<!-- wp:bestofislam/abstract {"text":"My own summary."} /-->';
check( $body === boi_abstract_filter( $body ), 'a block in the body: no second box' );
check( 'My own summary.' === boi_abstract_text( $GLOBALS['t_post'] ), 'the block text goes to the schema' );
$GLOBALS['t_post']->post_content = '[abstract]Short code summary.[/abstract]';
check( 'Short code summary.' === boi_abstract_text( $GLOBALS['t_post'] ), 'the shortcode text goes to the schema' );
$GLOBALS['t_post']->post_content = '';

// Off, on a page, and for an article with no abstract.
$GLOBALS['t_options'] = array( 'abstract_enabled' => 0 );
check( $body === boi_abstract_filter( $body ), 'no box when switched off' );
$GLOBALS['t_options'] = array();
$GLOBALS['t_single']  = false;
check( $body === boi_abstract_filter( $body ), 'no box on a page' );
$GLOBALS['t_single']          = true;
$GLOBALS['t_post']->post_name = 'no-such-article';
check( $body === boi_abstract_filter( $body ), 'no box without an abstract' );

echo $fails ? "$fails of $count checks failed\n" : "All $count checks passed\n";
exit( $fails ? 1 : 0 );
