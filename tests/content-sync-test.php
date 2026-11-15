<?php
/**
 * Stub test of the seeded article review rules (inc/content-sync.php): the decision for a changed, edited,
 * dropped or superseded article, and the removal guard.
 *
 * Run: php tests/content-sync-test.php
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}
define( 'ABSPATH', '/' );
function wp_json_encode( $v ) {
	return json_encode( $v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}
function add_action() {}
function add_filter() {}
function boi_content_fingerprint( $c ) {
	return md5( (string) $c );
}

require dirname( __DIR__ ) . '/inc/content-sync.php';

$fail = 0;
function boi_check( $ok, $label ) {
	global $fail;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . $label . "\n";
	$fail += $ok ? 0 : 1;
}

$fa = boi_sync_fp_values( 'T', 'E', '<!-- wp:paragraph --><p>C</p><!-- /wp:paragraph -->' );
$fb = boi_sync_fp_values( 'T', 'E', '<!-- wp:paragraph --><p>C2</p><!-- /wp:paragraph -->' );
boi_check( $fa !== $fb, 'a changed body changes the fingerprint' );
boi_check( boi_sync_fp_values( 'T2', 'E', '<!-- wp:paragraph --><p>C</p><!-- /wp:paragraph -->' ) !== $fa, 'the title is part of the fingerprint' );
boi_check( boi_sync_fp_values( 'T', 'E2', '<!-- wp:paragraph --><p>C</p><!-- /wp:paragraph -->' ) !== $fa, 'the excerpt is part of the fingerprint' );

boi_check( 'current' === boi_sync_decide( $fb, $fb, $fb, $fb, '' ), 'equal text is current' );
boi_check( 'current' === boi_sync_decide( $fb, $fa, $fa, $fb, '' ), 'a bundle unchanged since the last write is current, even when the owner edited' );
boi_check( 'update' === boi_sync_decide( $fb, $fa, $fa, $fa, '' ), 'untouched and the bundle changed: update' );
boi_check( 'newer' === boi_sync_decide( $fb, 'mine', $fa, $fa, '' ), 'edited and the bundle changed: newer, never overwritten' );
boi_check( 'newer' === boi_sync_decide( $fb, $fa, '', '', '' ), 'no recorded fingerprint counts as edited' );
boi_check( 'kept' === boi_sync_decide( $fb, 'mine', $fa, $fa, $fb ), 'the owner chose to keep against this bundle text' );
boi_check( 'newer' === boi_sync_decide( $fb, 'mine', $fa, $fa, $fa ), 'a keep against an older bundle text does not hold' );

boi_check( 'trash' === boi_sync_decide_removed( $fa, $fa, '' ), 'dropped and untouched: trash' );
boi_check( 'removed' === boi_sync_decide_removed( 'mine', $fa, '' ), 'dropped and edited: listed' );
boi_check( 'removed' === boi_sync_decide_removed( $fa, '', '' ), 'dropped with no record: listed, never trashed' );
boi_check( 'kept' === boi_sync_decide_removed( 'mine', $fa, 'removed' ), 'dropped, edited, owner chose to keep: quiet' );
boi_check( 'kept' === boi_sync_decide_removed( 'mine', $fa, 'superseded' ), 'a keep recorded for a replaced article also quiets the dropped row' );

boi_check( '' === boi_sync_guard( false, true, 174, 174 ), 'the guard allows an ordinary sync' );
boi_check( '' !== boi_sync_guard( true, true, 174, 174 ), 'the kill switch stops removals' );
boi_check( '' !== boi_sync_guard( false, false, 174, 174 ), 'a failed checksum stops removals' );
boi_check( '' !== boi_sync_guard( false, true, 150, 174 ), 'a drop of more than 10 percent stops removals' );
boi_check( '' !== boi_sync_guard( false, true, 156, 174 ), 'a drop of 10.3 percent is stopped, with no rounding' );
boi_check( '' === boi_sync_guard( false, true, 157, 174 ), 'a drop of under 10 percent is allowed' );
boi_check( '' === boi_sync_guard( false, true, 5, 0 ), 'the first sync has no prior count' );

echo $fail ? "\n$fail check(s) failed\n" : "\nAll checks passed\n";
exit( $fail ? 1 : 0 );
