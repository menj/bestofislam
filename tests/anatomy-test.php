<?php
/**
 * Article anatomy (inc/anatomy.php): heading ids and the contents they feed,
 * the Notes anchor, where the mid-article next step goes, key points, review
 * dates and the table region. WordPress is replaced by stubs.
 *
 * Run: php tests/anatomy-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['t_post'] = null;
function add_action() {}
function add_filter() {}
function __( $s ) { return $s; }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function wp_strip_all_tags( $s ) { return strip_tags( (string) $s ); }
function wp_html_excerpt( $s, $n ) { return mb_substr( (string) $s, 0, $n ); }
function sanitize_title( $s ) { return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( (string) $s ) ), '-' ); }
function is_singular() { return true; }
function get_queried_object() { return $GLOBALS['t_post']; }
class WP_Post {
	public $ID            = 1;
	public $post_password = '';
}

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

$html = '<h2>The objection</h2><p>a</p><h2 id="own">Mine</h2><h2>The objection</h2><h2 class="x">Own <em>words</em></h2><h2></h2>';
$list = boi_anatomy_heading_list( $html );
check( array( 'the-objection', 'The objection', false ) === $list[0], 'id made from the text' );
check( array( 'own', 'Mine', true ) === $list[1], 'the author\'s id kept' );
check( 'the-objection-2' === $list[2][0], 'a repeated heading is numbered' );
check( array( 'own-words', 'Own words', false ) === $list[3], 'inline markup leaves the text' );
check( '' === $list[4][0], 'an empty heading gets no id' );

$ids = boi_anatomy_heading_ids( $html );
check( false !== strpos( $ids, '<h2 id="the-objection">The objection</h2>' ) && false !== strpos( $ids, '<h2 id="the-objection-2">' ) && false !== strpos( $ids, '<h2 id="own">Mine</h2>' ) && false !== strpos( $ids, '<h2 id="own-words" class="x">' ), 'rendered headings get the same ids' );
check( false !== strpos( $ids, '<h2></h2>' ), 'an empty heading is left alone' );

$notes = boi_anatomy_notes_anchor( '<h2>Intro</h2><h3 class="has-hicon"><svg aria-hidden="true"></svg><span>Notes</span></h3><h3>Notes</h3>' );
check( 1 === substr_count( $notes, 'id="notes"' ) && false !== strpos( $notes, '<h3 id="notes" class="has-hicon">' ), 'the first Notes heading, past other headings, gets the anchor' );
check( '<h3 id="notes">Notes</h3>' === boi_anatomy_notes_anchor( '<h3 id="notes">Notes</h3>' ), 'an existing anchor is kept' );

$mid = boi_anatomy_insert_mid( '<h2>1</h2><h2>2</h2><h2>3</h2>', '<aside>N</aside>' );
check( '<h2>1</h2><h2>2</h2><aside>N</aside><h2>3</h2>' === $mid, 'before the third section' );
$entries = '<div><article class="boi-entry a"><h2>1</h2></article><article class="boi-entry a"><h2>2</h2></article><article class="boi-entry a"><h2>3</h2></article></div>';
check( false !== strpos( boi_anatomy_insert_mid( $entries, '<aside>N</aside>' ), '</article><aside>N</aside><article class="boi-entry a"><h2>3</h2>' ), 'in a listicle, between the second and third entries' );
check( '<h2>1</h2><h2>2</h2>' === boi_anatomy_insert_mid( '<h2>1</h2><h2>2</h2>', '<aside>N</aside>' ), 'two sections: nothing added' );

check( array( 'One', 'Two', 'Three' ) === boi_anatomy_points( " One\n\nTwo ;; Three\r\n" ), 'key points split on lines and ;;' );
check( '2026-11-02' === boi_anatomy_sanitize_date( '2026-11-02' ) && '' === boi_anatomy_sanitize_date( '2026-02-30' ) && '' === boi_anatomy_sanitize_date( 'soon' ), 'review dates checked' );

$GLOBALS['t_post'] = new WP_Post();
$table             = boi_anatomy_table_region( '<figure class="wp-block-table"><table></table><figcaption>Three <em>dates</em></figcaption></figure>' );
check( false !== strpos( $table, '<figure class="wp-block-table" role="region" tabindex="0" aria-label="Three dates">' ), 'a table is a region named by its caption' );
check( $table === boi_anatomy_table_region( $table ), 'a region is not wrapped twice' );

echo $fails ? "$fails of $count checks failed\n" : "All $count checks passed\n";
exit( $fails ? 1 : 0 );
