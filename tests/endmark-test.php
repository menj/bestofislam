<?php
/**
 * The endmark (inc/endmark.php): it closes the last paragraph of an article's
 * main text, before the Notes, never inside a list, quotation or FAQ, and
 * never on a page or a post without the listicle block. WordPress is
 * replaced by stubs.
 *
 * Run: php tests/endmark-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );

class WP_Post {
	public $post_content = '';
}

$GLOBALS['t_single']   = true;
$GLOBALS['t_listicle'] = true;
$GLOBALS['t_on']       = true;

function add_filter() {}
function is_singular( $type = '' ) { return $GLOBALS['t_single']; }
function boi_anatomy_is_main() { return true; }
function boi_anatomy_on( $key ) { return $GLOBALS['t_on']; }
function get_queried_object() { return new WP_Post(); }
function has_block( $name, $post ) { return $GLOBALS['t_listicle']; }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }

require dirname( __DIR__ ) . '/inc/endmark.php';

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

$article = '<p>Intro.</p><h2>The bottom line</h2><p>First verdict.</p><p>God knows best.</p>'
	. '<h3 id="notes">Notes</h3><ol><li><p>A note paragraph.</p></li></ol>';
$out     = boi_endmark_filter( $article );
check( false !== strpos( $out, 'God knows best.<span class="boi-endmark"' ), 'the mark closes the last paragraph of the bottom line' );
check( 1 === substr_count( $out, 'class="boi-endmark"' ), 'exactly one mark' );
check( false === strpos( substr( $out, strpos( $out, 'Notes' ) ), 'boi-endmark' ), 'nothing inside the Notes' );
check( $out === boi_endmark_filter( $out ), 'a second pass adds nothing' );

$faq = '<p>Last real line.</p><details class="is-style-faq"><summary>Q?</summary><p>An answer.</p></details>';
check( false !== strpos( boi_endmark_filter( $faq ), 'Last real line.<span class="boi-endmark"' ), 'an FAQ answer never carries the mark' );

$quote = '<p>The line before.</p><blockquote><p>A quotation.</p></blockquote>';
check( false !== strpos( boi_endmark_filter( $quote ), 'The line before.<span class="boi-endmark"' ), 'a quotation never carries the mark' );

$GLOBALS['t_single'] = false;
check( $article === boi_endmark_filter( $article ), 'no mark on a page (FAQ, Terms, Contact, Sitemap)' );
$GLOBALS['t_single']   = true;
$GLOBALS['t_listicle'] = false;
check( $article === boi_endmark_filter( $article ), 'no mark on a post without the listicle block' );
$GLOBALS['t_listicle'] = true;
$GLOBALS['t_on']       = false;
check( $article === boi_endmark_filter( $article ), 'no mark when switched off' );

echo $fails ? "$fails of $count checks failed\n" : "All $count checks passed\n";
exit( $fails ? 1 : 0 );
