<?php
/**
 * The error drop-ins (inc/error-pages.php): each kind has its own page and
 * status, runs without WordPress, sends noindex and no-store; the state reads
 * current, outdated, another theme's, foreign and absent; another theme's
 * marked leftover is replaced; switching away removes only the theme's files; the static pages in errors/ are
 * current, and the site copies in wp-content/bestofislam-errors/ carry the
 * site's home address and are removed on switch. WordPress is replaced by small stubs.
 *
 * Run: php tests/error-pages-test.php
 *
 * @package BestOfIslam
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_DIR', dirname( __DIR__ ) );
define( 'BOI_VERSION', '9.9.9' );
define( 'BOI_OPTION_KEY', 'boi_options' );
// A fresh directory of its own, so the cleanup at the end removes only what this test wrote.
$boi_tmp = sys_get_temp_dir() . '/boi-err-' . getmypid() . '-' . bin2hex( random_bytes( 6 ) );
if ( ! mkdir( $boi_tmp, 0700 ) ) {
	fwrite( STDERR, "cannot create $boi_tmp\n" );
	exit( 1 );
}
define( 'WP_CONTENT_DIR', $boi_tmp );
$GLOBALS['boi_o'] = array();
function add_action() {}
function __( $s ) { return $s; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_url( $s ) { return (string) $s; }
function wp_strip_all_tags( $s ) { return strip_tags( (string) $s ); }
function get_bloginfo( $k = '' ) { return 'language' === $k ? 'en-GB' : 'Best of Islam'; }
function home_url( $p = '' ) { return $GLOBALS['boi_home'] . $p; }
$GLOBALS['boi_home'] = 'https://example.test';
function content_url( $p = '' ) { return 'https://example.test/wp-content' . $p; }
function wp_mkdir_p( $d ) { return is_dir( $d ) || mkdir( $d, 0700, true ); }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function untrailingslashit( $s ) { return rtrim( $s, '/' ); }
function get_stylesheet_directory_uri() { return 'https://example.test/wp-content/themes/bestofislam'; }
function get_option( $k, $d = false ) { return $GLOBALS['boi_o'][ $k ] ?? ( 'admin_email' === $k ? 'admin@example.test' : $d ); }
function update_option( $k, $v ) { $GLOBALS['boi_o'][ $k ] = $v; return true; }
function is_email( $s ) { return false !== strpos( (string) $s, '@' ); }
function wp_json_encode( $v ) { return json_encode( $v ); }
function wp_generate_password() { return bin2hex( random_bytes( 4 ) ); }
function wp_delete_file( $p ) { @unlink( $p ); }
function delete_option( $k ) { unset( $GLOBALS['boi_o'][ $k ] ); }
function is_multisite() { return false; }
function boi_get_options() { return array( 'errors_pages' => 1, 'errors_alerts' => 0, 'errors_email' => '', 'errors_cooldown' => 30 ); }
require dirname( __DIR__ ) . '/inc/error-pages.php';

$boi_fail = 0;
function boi_check( $ok, $label ) {
	global $boi_fail;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . $label . "\n";
	$boi_fail += $ok ? 0 : 1;
}

$boi_src = array();
foreach ( boi_error_kinds() as $boi_kind => $boi_def ) {
	$boi_src[ $boi_kind ] = boi_error_dropin_source( $boi_kind );
	boi_check( false !== strpos( $boi_src[ $boi_kind ], BOI_ERR_MARKER . ' 9.9.9 (' . $boi_kind . ')' ), "$boi_kind carries the marker and its kind" );
	boi_check( false !== strpos( $boi_src[ $boi_kind ], 'X-Robots-Tag: noindex' ) && false !== strpos( $boi_src[ $boi_kind ], 'no-store' ), "$boi_kind sends noindex and no-store" );
	$boi_file = "$boi_tmp/run-$boi_kind.php";
	file_put_contents( $boi_file, "<?php define( 'ABSPATH', '/' ); ?>" . $boi_src[ $boi_kind ] );
	$boi_out = (string) shell_exec( escapeshellarg( PHP_BINARY ) . ' -d display_errors=1 -d error_reporting=-1 ' . escapeshellarg( $boi_file ) . ' 2>&1' );
	boi_check( false !== strpos( $boi_out, '<!DOCTYPE html>' ) && ! preg_match( '/(Warning|Notice|Fatal error|Deprecated):/', $boi_out ), "$boi_kind runs without WordPress" );
	unlink( $boi_file );
}
boi_check( false !== strpos( $boi_src['maintenance'], "header( 'HTTP/1.1 503 Service Unavailable', true, 503 )" ) && false !== strpos( $boi_src['maintenance'], 'Retry-After' ), 'maintenance sends 503 and Retry-After' );
boi_check( false !== strpos( $boi_src['db-error'], "header( 'HTTP/1.1 503 Service Unavailable', true, 503 )" ) && false !== strpos( $boi_src['db-error'], 'We cannot reach our database' ), 'db-error sends 503 and names the database' );
boi_check( false !== strpos( $boi_src['db-error'], '<title>We cannot reach our database | Best of Islam</title>' ), 'db-error is titled after the database' );
boi_check( false !== strpos( $boi_src['php-error'], "header( 'HTTP/1.1 500 Internal Server Error', true, 500 )" ) && false === strpos( $boi_src['php-error'], 'Retry-After' ) && false !== strpos( $boi_src['php-error'], 'Something went wrong' ), 'php-error sends 500 without Retry-After' );

boi_check( array( 'maintenance' => 'absent', 'db-error' => 'absent', 'php-error' => 'absent' ) === boi_error_state(), 'nothing installed reads as absent' );
file_put_contents( "$boi_tmp/db-error.php", "<?php // someone else's\n" );
boi_error_sync();
$boi_state = boi_error_state();
boi_check( 'ours' === $boi_state['maintenance'] && 'ours' === $boi_state['php-error'], 'sync writes the theme files, read as current' );
boi_check( 'foreign' === $boi_state['db-error'] && "<?php // someone else's\n" === file_get_contents( "$boi_tmp/db-error.php" ), 'a foreign file is left alone' );
file_put_contents( "$boi_tmp/php-error.php", $boi_src['maintenance'] );
boi_check( 'outdated' === boi_error_state()['php-error'], 'the maintenance page in php-error.php reads as outdated' );
boi_error_sync();
boi_check( file_get_contents( "$boi_tmp/php-error.php" ) === $boi_src['php-error'], 'sync puts the right page back' );
boi_check( true === boi_error_sync() && array() === boi_error_sync_failures(), 'a sync that writes everything reports success' );
boi_check( false === boi_error_write( "$boi_tmp/no-such-dir/x.php", 'x' ), 'a write that cannot happen reports failure' );
if ( function_exists( 'posix_geteuid' ) && 0 !== posix_geteuid() ) {
	unlink( "$boi_tmp/php-error.php" );
	chmod( $boi_tmp, 0500 );
	$boi_ok = boi_error_sync();
	chmod( $boi_tmp, 0700 );
	boi_check( false === $boi_ok && in_array( 'php-error.php', boi_error_sync_failures(), true ), 'an unwritable wp-content fails the sync and names the file' );
}

file_put_contents( "$boi_tmp/maintenance.php", "<?php\n// Paulus error page drop-in 2.77.3. Written by the Paulus theme; safe to delete.\n" );
boi_check( 'theme' === boi_error_state()['maintenance'], 'another theme\'s marked leftover reads as theme' );
boi_error_sync();
boi_check( file_get_contents( "$boi_tmp/maintenance.php" ) === $boi_src['maintenance'], 'sync replaces another theme\'s marked leftover' );
file_put_contents( "$boi_tmp/maintenance.php", str_replace( 'Back shortly', 'Edited', $boi_src['maintenance'] ) );
boi_error_maybe_sync();
boi_check( file_get_contents( "$boi_tmp/maintenance.php" ) === $boi_src['maintenance'], 'an admin visit repairs an edited copy with the fingerprint current' );
$boi_static = "$boi_tmp/bestofislam-errors";
boi_check( array( 'ours' ) === array_values( array_unique( boi_error_static_state() ) ), 'sync writes the site copies of the static pages' );
boi_check( false !== strpos( (string) file_get_contents( "$boi_static/403.html" ), 'href="https://example.test/"' ), 'the 403 copy links to the site home' );
$GLOBALS['boi_home'] = 'https://example.test/blog';
boi_check( 'outdated' === boi_error_static_state()['bestofislam-errors/503.html'], 'a new home address makes the static copies outdated' );
boi_error_sync();
boi_check( false !== strpos( (string) file_get_contents( "$boi_static/503.html" ), 'href="https://example.test/blog/"' ) && false !== strpos( (string) file_get_contents( "$boi_static/503.html" ), '<html lang="en-GB">' ), 'a subfolder install gets its own home link and language' );
boi_check( false !== strpos( boi_error_htaccess_snippet(), 'ErrorDocument 503 /wp-content/bestofislam-errors/503.html' ) && false !== strpos( boi_error_htaccess_snippet(), 'ErrorDocument 504 /wp-content/bestofislam-errors/504.html' ), '.htaccess lines point at the site copies' );
file_put_contents( "$boi_static/500.html", "<p>owner's own</p>\n" );
boi_error_sync();
boi_check( "<p>owner's own</p>\n" === file_get_contents( "$boi_static/500.html" ), 'a static page the theme did not write is left alone' );
boi_error_remove();
boi_check( ! file_exists( "$boi_tmp/maintenance.php" ) && ! file_exists( "$boi_tmp/php-error.php" ) && "<?php // someone else's\n" === file_get_contents( "$boi_tmp/db-error.php" ), 'switching away removes only the theme\'s own files' );
boi_check( ! file_exists( "$boi_static/503.html" ) && file_exists( "$boi_static/500.html" ), 'switching away removes the theme\'s static copies and keeps the others' );
unlink( "$boi_static/500.html" );
rmdir( $boi_static );

exec( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( dirname( __DIR__ ) . '/bin/build-error-pages.php' ) . ' --check 2>&1', $boi_o, $boi_code );
boi_check( 0 === $boi_code, 'errors/*.html match the builder' );
foreach ( array( '403', '500', '502', '503', '504' ) as $boi_c ) {
	$boi_h = (string) file_get_contents( dirname( __DIR__ ) . "/errors/$boi_c.html" );
	boi_check( false !== strpos( $boi_h, 'noindex' ) && false === strpos( $boi_h, '{{' ) && preg_match( '~<title>[^<|]+ \| Best of Islam</title>~', $boi_h ), "errors/$boi_c.html is filled and noindex" );
}

array_map( 'unlink', array_filter( glob( "$boi_tmp/{,.}*", GLOB_BRACE ) ?: array(), 'is_file' ) );
@rmdir( $boi_tmp );
echo $boi_fail ? "$boi_fail failed\n" : "all passed\n";
exit( $boi_fail ? 1 : 0 );
