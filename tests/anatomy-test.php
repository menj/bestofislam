<?php
/**
 * Article anatomy (inc/anatomy.php): heading ids and the contents they feed,
 * the Notes anchor and links, where the mid-article next step goes, key points, review
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
function wp_html_excerpt( $s, $n ) { return function_exists( 'mb_substr' ) ? mb_substr( (string) $s, 0, $n ) : substr( (string) $s, 0, $n ); }
function sanitize_title( $s ) { return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( (string) $s ) ), '-' ); }
function is_singular() { return true; }
function get_queried_object() { return $GLOBALS['t_post']; }
class WP_Post {
	public $ID            = 1;
	public $post_password = '';
	public $post_content  = '';
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
check( 'own-notes' === boi_anatomy_notes_id( '<h2>A</h2><h3 class="x" id="own-notes"><span>Notes</span></h3>' ), 'the Notes heading\'s own id is the link target' );
check( '' === boi_anatomy_notes_id( '<h3>Notes</h3>' ), 'a Notes heading without an id is no target' );

$note_html = '<p id="note-1">Text<sup>[1]</sup> repeated<sup>[1]</sup> and another<sup>[2]</sup> or missing<sup>[3]</sup>.</p>'
	. '<p><a href="/source/">Quoted<sup>[1]</sup></a> <code><sup>[2]</sup></code></p>'
	. '<h3>Other list</h3><ol><li>Unrelated</li></ol>'
	. '<h3 class="has-hicon"><svg aria-hidden="true"></svg><span>Notes</span></h3>'
	. '<ol class="wp-block-list"><li>Source one</li><li id="own-note"><a href="https://example.org/">Source two</a></li></ol>';
$linked_notes = boi_anatomy_link_notes( $note_html );
check( 2 === substr_count( $linked_notes, 'href="#note-1-2" aria-label="Note 1">[1]</a>' ) && false !== strpos( $linked_notes, '<li id="note-1-2">Source one</li>' ), 'repeated references share a collision-free note id' );
check( false !== strpos( $linked_notes, 'href="#own-note" aria-label="Note 2">[2]</a>' ) && false !== strpos( $linked_notes, '<li id="own-note"><a href="https://example.org/">' ), 'a note keeps its own anchor and source link' );
check( false !== strpos( $linked_notes, 'missing<sup>[3]</sup>' ) && false !== strpos( $linked_notes, '<a href="/source/">Quoted<sup>[1]</sup></a> <code><sup>[2]</sup></code>' ), 'missing targets, existing links and code samples are untouched' );
check( false !== strpos( $linked_notes, '<ol><li>Unrelated</li></ol>' ) && $linked_notes === boi_anatomy_link_notes( $linked_notes ), 'other lists stay untouched and filtering twice is safe' );
$unlinked = '<p>Text<sup>[1]</sup></p><h3>Notes</h3><ol start="2"><li>Source</li></ol>';
check( $unlinked === boi_anatomy_link_notes( $unlinked ), 'a custom starting number does not turn into a wrong reference' );
check( '<p>Text<sup>[1]</sup></p>' === boi_anatomy_link_notes( '<p>Text<sup>[1]</sup></p>' ), 'a reference without a Notes list stays plain' );
$grouped = boi_anatomy_link_notes( '<p>Text<sup>[1][2]</sup></p><h3>Notes</h3><ol><li>One</li><li>Two</li></ol>' );
check( false !== strpos( $grouped, 'aria-label="Note 1">[1]</a><a class="boi-note-ref" href="#note-2" aria-label="Note 2">[2]</a></sup>' ), 'adjacent markers in one superscript get separate links' );

$mid = boi_anatomy_insert_mid( '<h2>1</h2><h2>2</h2><h2>3</h2>', '<aside>N</aside>' );
check( '<h2>1</h2><h2>2</h2><aside>N</aside><h2>3</h2>' === $mid, 'before the third section' );
$entries = '<div><article id="e" class="wp-block-bestofislam-entry boi-entry"><h2>1</h2></article><article id="e" class="wp-block-bestofislam-entry boi-entry"><h2>2</h2></article><article id="e" class="wp-block-bestofislam-entry boi-entry"><h2>3</h2></article></div>';
check( false !== strpos( boi_anatomy_insert_mid( $entries, '<aside>N</aside>' ), '</article><aside>N</aside><article id="e" class="wp-block-bestofislam-entry boi-entry"><h2>3</h2>' ), 'in a listicle, between the second and third entries' );
check( '<h2>1</h2><h2>2</h2>' === boi_anatomy_insert_mid( '<h2>1</h2><h2>2</h2>', '<aside>N</aside>' ), 'two sections: nothing added' );

check( array( 'One', 'Two', 'Three' ) === boi_anatomy_points( " One\n\nTwo ;; Three\r\n" ), 'key points split on lines and ;;' );
check( '2026-11-02' === boi_anatomy_sanitize_date( '2026-11-02' ) && '' === boi_anatomy_sanitize_date( '2026-02-30' ) && '' === boi_anatomy_sanitize_date( 'soon' ), 'review dates checked' );

$GLOBALS['t_post'] = new WP_Post();
$table             = boi_anatomy_table_region( '<figure class="wp-block-table"><table></table><figcaption>Three <em>dates</em></figcaption></figure>' );
check( false !== strpos( $table, '<figure class="wp-block-table" role="region" tabindex="0" aria-label="Three dates">' ), 'a table is a region named by its caption' );
check( $table === boi_anatomy_table_region( $table ), 'a region is not wrapped twice' );

// The jump list shows by default (block.json: showJumpList true), so a list without the attribute has one.
function has_block() { return true; }
function parse_blocks( $c ) { return $c; }
function boi_find_listicles( $b ) { return $b; }
$list          = new WP_Post();
$list->post_content = array( array( 'attrs' => array() ) );
check( boi_anatomy_has_jump_list( $list ), 'a list without showJumpList has its jump list' );
$list->post_content = array( array( 'attrs' => array( 'showJumpList' => false ) ) );
check( ! boi_anatomy_has_jump_list( $list ), 'a list with the jump list off gets the contents' );

echo $fails ? "$fails of $count checks failed\n" : "All $count checks passed\n";
exit( $fails ? 1 : 0 );
