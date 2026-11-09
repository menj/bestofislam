<?php
/**
 * Self-test for inc/lead-list.php: when the lead and list draws, which
 * piece leads, and that the lead stays out of the list. WordPress is
 * replaced by small stubs. Run: php tests/lead-list-test.php. Exit code 0
 * when every check passes.
 *
 * @package BestOfIslam
 */

// Run from the command line only: on a web server this file does nothing.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );

class WP_Post {
	public $ID;
	public $post_content = 'one two three four five six seven eight nine ten';
	public $post_status = 'publish';
	public $post_title  = '';
	public function __construct( $id, $img = true ) {
		$this->ID         = $id;
		$this->post_title = 'Piece ' . $id;
		$this->img        = $img;
	}
	public $img;
}
class WP_Term {
	public $slug;
	public $name;
	public $term_id;
}
class WP_Query {}
class WP_Block {}

$GLOBALS['boi_t'] = array(
	'posts'    => array(),
	'unlisted' => array(),
	'filters'  => array(),
);
function boi_t_posts( array $spec ) {
	$GLOBALS['boi_t']['posts'] = array();
	foreach ( $spec as $id => $img ) {
		$GLOBALS['boi_t']['posts'][ $id ] = new WP_Post( $id, $img );
	}
	return array_values( $GLOBALS['boi_t']['posts'] );
}
function add_action() {}
function add_filter() {}
function apply_filters( $tag, $value ) {
	return $GLOBALS['boi_t']['filters'][ $tag ] ?? $value;
}
function strip_shortcodes( $s ) {
	return $s;
}
function wp_strip_all_tags( $s ) {
	return strip_tags( (string) $s );
}
function _n( $a ) {
	return $a;
}
function esc_html__( $s ) {
	return $s;
}
function get_the_date( $f, $p ) {
	return 'c' === $f ? '2026-11-05T00:00:00+00:00' : '5 Nov 2026';
}
function get_the_category( $id ) {
	$cats = array(
		1 => array( 'history' ),
		2 => array( 'uncategorized', 'scripture' ),
		3 => array( 'uncategorized' ),
		4 => array( 'a-new-section' ),
	);
	$out  = array();
	foreach ( $cats[ $id ] ?? array( 'theology' ) as $slug ) {
		$c           = new WP_Term();
		$c->slug     = $slug;
		$c->name     = ucfirst( str_replace( '-', ' ', $slug ) );
		$c->term_id  = crc32( $slug ) % 1000;
		$out[]       = $c;
	}
	return $out;
}
function get_category_link( $id ) {
	return '/category/' . $id . '/';
}
function __( $s ) {
	return $s;
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
function sanitize_html_class( $s ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $s );
}
function wp_parse_args( $a, $d ) {
	return array_merge( $d, $a );
}
function wp_list_pluck( $list, $f ) {
	return array_map(
		static function ( $o ) use ( $f ) {
			return $o->$f;
		},
		$list
	);
}
function get_post( $p ) {
	if ( $p instanceof WP_Post ) {
		return $p;
	}
	return $GLOBALS['boi_t']['posts'][ (int) $p ] ?? null;
}
function boi_is_unlisted( $p ) {
	return in_array( (int) ( is_object( $p ) ? $p->ID : $p ), $GLOBALS['boi_t']['unlisted'], true );
}
function has_post_thumbnail( $p ) {
	return get_post( $p )->img;
}
function boi_bundled_image( $p ) {
	return 7 === get_post( $p )->ID ? array( 'file' => 'bundled.avif' ) : null;
}
function boi_lead_image( $p, $sizes, $attr ) {
	return get_the_post_thumbnail( $p, 'large', $attr ) ?: ( boi_bundled_image( $p ) ? '<img class="bundled" src="/b.avif" alt="">' : '<span class="boi-placeholder" aria-hidden="true"></span>' );
}
function boi_list_thumbnail( $p, $attr ) {
	return get_the_post_thumbnail( $p, 'medium_large', $attr ) ?: '<span class="boi-placeholder" aria-hidden="true"></span>';
}
function boi_image_credit_markup() {
	return '<p class="boi-credit">Caption line</p>';
}
function get_the_post_thumbnail( $p, $size, $attr ) {
	return get_post( $p )->img ? '<img class="' . $attr['class'] . '" alt="' . ( $attr['alt'] ?? 'photo' ) . '" src="/' . get_post( $p )->ID . '.avif">' : '';
}
function get_post_thumbnail_id( $p ) {
	return get_post( $p )->img ? 900 + get_post( $p )->ID : 0;
}
function get_post_meta() {
	return '';
}
function get_option( $k, $d = false ) {
	return $d;
}
function wp_get_attachment_caption() {
	return 'Caption line';
}
function wp_kses( $s ) {
	return $s;
}
function get_permalink( $p ) {
	return '/piece-' . get_post( $p )->ID . '/';
}
function get_the_title( $p ) {
	return get_post( $p )->post_title;
}

require dirname( __DIR__ ) . '/inc/lead-list.php';

$boi_fail = 0;
function boi_test_check( $ok, $what ) {
	global $boi_fail;
	echo ( $ok ? 'PASS ' : 'FAIL ' ) . $what . "\n";
	$boi_fail += $ok ? 0 : 1;
}

$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => true ) ) );
boi_test_check( array( 1, 2, 3, 4, 5, 6 ) === $r['ids'], 'the newest piece leads and the next five form the list' );
boi_test_check( 1 === substr_count( $r['html'], '<article' ) && 5 === substr_count( $r['html'], '<li ' ), 'one lead piece, five list items' );
boi_test_check( false !== strpos( $r['html'], 'aria-labelledby="boi-lead-list-heading"' ) && false !== strpos( $r['html'], 'id="boi-lead-list-heading"' ), 'the section is labelled by its heading' );
boi_test_check( 5 === substr_count( $r['html'], 'alt=""' ), 'list thumbnails carry empty alt text' );
boi_test_check( false !== strpos( $r['html'], '<figcaption class="boi-lead-list__credit"><p class="boi-credit">Caption line</p></figcaption>' ), 'the credit sits in the figcaption' );
$boi_overlay = substr( $r['html'], strpos( $r['html'], 'boi-lead-list__overlay' ), strpos( $r['html'], '</figure>' ) - strpos( $r['html'], 'boi-lead-list__overlay' ) );
boi_test_check( preg_match( '#^boi-lead-list__overlay.*?</div></div><figcaption#s', $boi_overlay ) === 1, 'the credit is outside the overlay' );

$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true ) ), array( 'lead_id' => 4 ) );
boi_test_check( array( 4, 1, 2, 3, 5, 6 ) === $r['ids'], 'lead_id leads and is left out of the list' );

$GLOBALS['boi_t']['unlisted'] = array( 2, 4 );
$r                            = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => true ) ), array( 'lead_id' => 4 ) );
boi_test_check( array( 1, 3, 5, 6, 7 ) === $r['ids'], 'unlisted pieces neither lead nor appear in the list' );
$GLOBALS['boi_t']['unlisted'] = array();

$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true ) ) );
boi_test_check( '' === $r['html'], 'three pieces: nothing is drawn and the grid stays as it was' );

$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true, 4 => true ) ) );
boi_test_check( '' === $r['html'], 'four pieces: nothing is drawn' );

$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true, 4 => true, 5 => true ) ) );
boi_test_check( array( 1, 2, 3, 4, 5 ) === $r['ids'], 'five pieces: a lead and a list of four' );

$r = boi_lead_list( boi_t_posts( array( 1 => false, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true ) ) );
boi_test_check( '' === $r['html'], 'a lead with neither a photograph nor a bundled image: nothing is drawn' );

$r = boi_lead_list( boi_t_posts( array( 7 => false, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true ) ) );
boi_test_check( array( 7, 2, 3, 4, 5, 6 ) === $r['ids'] && false !== strpos( $r['html'], 'class="bundled"' ), 'a lead with only a bundled image leads with it' );

$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => false, 3 => true, 4 => true, 5 => true, 6 => true ) ) );
boi_test_check( 1 === substr_count( $r['html'], 'boi-placeholder' ), 'a list piece without a photograph shows the drawn placeholder' );

// Category tabs, meta line, current row, settings.
$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true ) ) );
boi_test_check( 6 === substr_count( $r['html'], 'class="boi-lead-list__meta"' ) && false !== strpos( $r['html'], '1 min read' ), 'the lead and each item carry a date and reading-time line' );
boi_test_check( 1 === preg_match( '#<a class="boi-lead-list__tab boi-lead-list__tab--c2" href="/category/\d+/">History</a>#', $r['html'] ), 'the lead tab is a link that names the category and keeps its colour' );
boi_test_check( false !== strpos( $r['html'], '--c1" href="/category/' ) && false === strpos( $r['html'], '>Uncategorized<' ), 'the uncategorised term is skipped for the next category' );
boi_test_check( 5 === substr_count( $r['html'], '<a class="boi-lead-list__tab' ), 'a piece with only the default category has no tab' );
boi_test_check( 1 === substr_count( $r['html'], 'is-current' ) && false !== strpos( $r['html'], 'boi-lead-list__item is-current' ), 'only the first list item is the current row' );
boi_test_check( 5 === substr_count( $r['html'], 'class="boi-lead-list__arrow" aria-hidden="true"' ) && false !== strpos( $r['html'], 'boi-lead-list__read" aria-hidden="true"' ), 'arrows are decoration and hidden from assistive technology' );
boi_test_check( false === strpos( $r['html'], 'boi-lead-list--calm' ) && false === strpos( $r['html'], 'style="--boi-ll-accent' ), 'by default the motion is on and the theme accent is used' );

$GLOBALS['boi_t']['filters'] = array(
	'boi_lead_list_show_tabs' => false,
	'boi_lead_list_motion'    => false,
	'boi_lead_list_accent'    => '#8a1f2c',
);
$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true ) ) );
boi_test_check( false === strpos( $r['html'], '<a class="boi-lead-list__tab' ), 'the tabs switch removes the tabs' );
boi_test_check( false !== strpos( $r['html'], 'boi-lead-list--calm' ) && false !== strpos( $r['html'], 'style="--boi-ll-accent:#8a1f2c"' ), 'the motion switch adds the calm class and the accent override is applied' );
$GLOBALS['boi_t']['filters'] = array( 'boi_lead_list_accent' => 'red; background:url(x)' );
$r = boi_lead_list( boi_t_posts( array( 1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true ) ) );
boi_test_check( false === strpos( $r['html'], 'style=' . '"--boi-ll-accent' ), 'an accent that is not a hex colour is dropped' );
$GLOBALS['boi_t']['filters'] = array();

// Contrast: the tab pairs in both appearances, and the hovered title on its tint.
$boi_light = array( 'base' => '#ffffff', 'contrast' => '#0d2b4e', 'a1' => '#1d6ac3', 'a2' => '#e8b33c', 'a3' => '#eaf2fb', 'a5' => '#4a5a6e' );
$boi_dark  = array( 'base' => '#0a2038', 'contrast' => '#eaf2fb', 'a1' => '#3fa9f5', 'a2' => '#e8b33c', 'a3' => '#0f2b49', 'a5' => '#9db3c9' );
foreach ( array( 'light' => $boi_light, 'dark' => $boi_dark ) as $mode => $t ) {
	$pairs = array( 'c0' => array( $t['a1'], $t['base'] ), 'c1' => array( $t['a2'], '#0d2b4e' ), 'c2' => array( $t['contrast'], $t['base'] ), 'c3' => array( $t['a5'], $t['base'] ), 'c4' => array( $t['a3'], $t['contrast'] ) );
	foreach ( $pairs as $name => $pair ) {
		$ratio = boi_lead_list_ratio( $pair[0], $pair[1] );
		boi_test_check( $ratio >= 4.5, sprintf( 'tab %s in the %s appearance reads at %.1f:1 (needs 4.5)', $name, $mode, $ratio ) );
	}
	$text = boi_lead_list_mix( $t['a1'], $t['contrast'], 70 );
	$tint = boi_lead_list_mix( $t['a1'], $t['base'], 9 );
	$ratio = boi_lead_list_ratio( $text, $tint );
	boi_test_check( $ratio >= 4.5, sprintf( 'the hovered title in the %s appearance reads at %.1f:1 on its tint', $mode, $ratio ) );
	$ratio = boi_lead_list_ratio( $t['a5'], boi_lead_list_mix( $t['a1'], $t['base'], 9 ) );
	boi_test_check( $ratio >= 4.5, sprintf( 'the meta line in the %s appearance reads at %.1f:1 on the tint', $mode, $ratio ) );
}
boi_test_check( boi_lead_list_accent_ok( '#1d6ac3' ) && ! boi_lead_list_accent_ok( '#ffffff' ) && ! boi_lead_list_accent_ok( '#f5f5a0' ) && ! boi_lead_list_accent_ok( '#8a1f2c' ), 'an accent override that cannot carry text in both appearances is refused' );

exit( $boi_fail ? 1 : 0 );
