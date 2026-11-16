<?php
/**
 * The contents adapted from WP TOC (inc/anatomy.php): heading ids at levels
 * two and three, skipped headings, ids unique against every id in the text,
 * readable ids for Arabic headings, the nested tree, the shortcode settings,
 * the marker the shortcode leaves and the list that replaces it.
 * WordPress is replaced by stubs.
 *
 * Run: php tests/toc-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );

function add_action() {}
function add_filter() {}
function add_shortcode() {}
function __( $s ) { return $s; }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function wp_strip_all_tags( $s ) { return strip_tags( (string) $s ); }
function wp_json_encode( $v ) { return json_encode( $v ); }
function is_feed() { return false; }
function doing_filter() { return false; }
function is_singular() { return true; }
function is_front_page() { return false; }
function get_queried_object() { return $GLOBALS['t_post']; }
function is_main_query() { return $GLOBALS['t_main'] ?? true; }
function get_the_ID() { return $GLOBALS['t_id'] ?? 1; }

// Like WordPress: lower case, spaces to hyphens, other punctuation dropped,
// and letters outside ASCII percent-encoded.
function sanitize_title( $s ) {
	$s = strtolower( trim( strip_tags( (string) $s ) ) );
	$s = preg_replace( '/[^\p{L}\p{N}\s-]+/u', '', $s );
	$s = preg_replace( '/[\s-]+/u', '-', $s );
	return trim( preg_replace_callback( '/[^\x00-\x7F]/', function ( $m ) { return strtolower( rawurlencode( $m[0] ) ); }, $s ), '-' );
}

class WP_Post {
	public $ID            = 1;
	public $post_password = '';
	public $post_type     = 'post';
	public $post_content  = '';
}
$GLOBALS['t_post'] = new WP_Post();

require dirname( __DIR__ ) . '/inc/anatomy.php';

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

// Ids at both levels, unique against every id in the text.
$html = '<div id="intro"></div><h2>Intro</h2><h3>Detail</h3><h2 id="own">Mine</h2><h3>Detail</h3>';
$h    = boi_toc_headings( $html );
check( 'intro-2' === $h[0]['id'], 'a made id avoids an id held by any element' );
check( 3 === $h[1]['level'] && 'detail' === $h[1]['id'], 'level-three headings get ids' );
check( 'own' === $h[2]['id'] && $h[2]['own'], 'the author\'s id kept' );
check( 'detail-2' === $h[3]['id'], 'a repeated level-three heading is numbered' );

$ids = boi_anatomy_heading_ids( $html );
check( false !== strpos( $ids, '<h3 id="detail">Detail</h3>' ) && false !== strpos( $ids, '<h3 id="detail-2">Detail</h3>' ) && false !== strpos( $ids, '<h2 id="intro-2">Intro</h2>' ), 'rendered headings get the same ids' );

// Skipped headings.
$skip = '<h2 class="lead no-toc">Hidden</h2><h2 data-no-toc>Also hidden</h2><h2></h2><template><h2>In a template</h2></template><h2>Shown</h2>';
$h    = boi_toc_headings( $skip );
check( 4 === count( $h ), 'a heading inside a template is not read' );
check( $h[0]['skip'] && '' === $h[0]['id'] && $h[1]['skip'] && $h[2]['skip'], 'no-toc, data-no-toc and empty headings are skipped' );
check( 'shown' === $h[3]['id'], 'the rest are listed' );
$ids = boi_anatomy_heading_ids( $skip );
check( false !== strpos( $ids, '<h2 class="lead no-toc">Hidden</h2>' ) && false !== strpos( $ids, '<template><h2>In a template</h2></template>' ) && false !== strpos( $ids, '<h2 id="shown">Shown</h2>' ), 'skipped headings keep their markup' );

// Unquoted attributes, as HTML allows.
$h = boi_toc_headings( '<p id=taken></p><h2 id=custom>Mine</h2><h2 class=no-toc>Hidden</h2><h2 class="a  no-toc b">Hidden too</h2><h2 class="no-tocx">Shown</h2><h2>Taken</h2>' );
check( 'custom' === $h[0]['id'] && $h[0]['own'], 'an unquoted id is the author\'s id' );
check( $h[1]['skip'] && $h[2]['skip'] && ! $h[3]['skip'], 'class no-toc, quoted or not, among other classes; no-tocx is not no-toc' );
check( 'taken-2' === $h[4]['id'], 'an unquoted id elsewhere is reserved' );
check( false === strpos( boi_anatomy_heading_ids( '<h2 id=custom>Mine</h2>' ), 'id="' ), 'no second id on a heading with an unquoted one' );

// Arabic headings keep readable ids.
check( 'مرحبا-بالعالم' === boi_toc_slug( 'مرحبا بالعالم' ), 'an Arabic heading gets an Arabic id' );
check( 'section' === boi_toc_slug( '!!!' ), 'a heading with no letters gets "section"' );

// The tree.
$items = boi_toc_headings( '<h3>Before</h3><h2>One</h2><h3>One a</h3><h3>One b</h3><h2>Two</h2>' );
$tree  = boi_toc_tree( $items );
check( 3 === count( $tree ) && 'Before' === $tree[0]['text'], 'a level-three heading before any section stays at the top' );
check( 2 === count( $tree[1]['children'] ) && 'One b' === $tree[1]['children'][1]['text'], 'subsections nest under their section' );
check( 5 === boi_toc_count( $tree ), 'every entry is counted' );
check( 2 === count( boi_toc_tree( $items, array( 2 ) ) ), 'tags="h2" lists the sections only' );
check( array() === boi_toc_tree( $items, array( 2, 3 ), 1 )[1]['children'], 'depth="1" stops at one level' );

// Settings, read like WP TOC's attributes.
$s = boi_toc_settings( array( 'tags' => 'H2, h4, x', 'title' => '<b>Parts</b>', 'collapsible' => 'yes', 'collapsed' => 'on', 'min' => '0', 'depth' => '-2' ) );
check( array( 2 ) === $s['levels'], 'tags: only h2 and h3 are read; others are dropped' );
check( 'Parts' === $s['title'] && $s['collapsible'] && $s['collapsed'] && 1 === $s['min'] && 0 === $s['depth'], 'title stripped, booleans and numbers read' );
check( array( 2, 3 ) === boi_toc_settings( array( 'tags' => 'h5' ) )['levels'], 'nothing valid: the default levels' );

// The list.
$nav = boi_toc_html( $tree, boi_toc_settings() );
check( 0 === strpos( $nav, '<nav class="boi-contents" aria-labelledby="boi-contents-title">' ), 'a navigation region named by its label' );
check( false !== strpos( $nav, '<a href="#one">One</a><ol class="boi-contents__sublist"><li><a href="#one-a">One a</a></li>' ), 'subsections in a nested list' );
$fold = boi_toc_html( $tree, boi_toc_settings( array( 'collapsible' => 'true', 'collapsed' => 'true' ) ) );
check( false !== strpos( $fold, '<details class="boi-contents__details"><summary class="boi-contents__summary">' ), 'collapsed: a closed details element' );
check( false !== strpos( boi_toc_html( $tree, boi_toc_settings( array( 'collapsible' => '1' ) ) ), '<details class="boi-contents__details" open>' ), 'collapsible: open by default' );
check( false !== strpos( boi_toc_html( $tree, boi_toc_settings( array( 'title' => '' ) ) ), '<nav class="boi-contents" aria-label="In this article"><ol' ), 'no title: named by aria-label' );

// The shortcode leaves a marker; the finished text gets the list.
$marker = boi_toc_shortcode( array( 'tags' => 'h2' ) );
check( 0 === strpos( $marker, '<!--boi-toc:' ), 'the shortcode leaves a marker' );
$text   = boi_anatomy_heading_ids( '<p>' . $marker . '</p><h2>Alpha</h2><h3>Sub</h3><h2>Beta</h2>' );
$filled = boi_toc_fill( $text );
check( 0 === strpos( $filled, '<nav class="boi-contents"' ) && false !== strpos( $filled, '<a href="#alpha">Alpha</a></li><li><a href="#beta">Beta</a>' ) && false === strpos( $filled, 'Sub</a>' ), 'the marker and its paragraph become the list, at the levels asked' );
check( '<h2>Only</h2>' === boi_toc_fill( '<!--boi-toc:' . bin2hex( json_encode( array( 'min' => 3 ) ) ) . '--><h2>Only</h2>' ), 'below min: nothing printed' );
check( 0 === strpos( boi_toc_fill( '<!--boi-toc:abc--><h2 id="a">A</h2>' ), '<nav class="boi-contents"' ), 'a broken marker falls back to the defaults' );

$GLOBALS['t_id'] = 2;
check( '' === boi_toc_shortcode( array() ), 'nothing outside the main text (a widget, another loop)' );
$GLOBALS['t_id'] = 1;
$GLOBALS['t_main'] = false;
check( '' === boi_toc_shortcode( array() ), 'nothing in a secondary query' );
$GLOBALS['t_main'] = true;

$GLOBALS['boi_toc_busy'] = true;
check( '' === boi_toc_shortcode( array() ), 'nothing while the text is read for its headings' );
$GLOBALS['boi_toc_busy'] = false;

echo $fails ? "$fails of $count checks failed\n" : "All $count checks passed\n";
exit( $fails ? 1 : 0 );
