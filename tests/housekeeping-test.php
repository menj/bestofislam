<?php
/**
 * inc/housekeeping.php: in a development environment nothing is deleted; on
 * production the development files go, .git, .claude, tests, bin and docs
 * among them, even in a git working tree. BOI_KEEP_DEV_FILES keeps them,
 * BOI_REMOVE_GIT_DIR true forces the cleanup and false keeps .git.
 *
 * Run: php tests/housekeeping-test.php
 *
 * The constant cases need a fresh process each, so the test runs itself
 * once per case.
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

$boi_case = $argv[1] ?? '';

if ( '' === $boi_case ) {
	$boi_fail = 0;
	foreach ( array( 'env', 'keep', 'force', 'keep-git', 'stuck', 'lock' ) as $boi_c ) {
		passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' ' . escapeshellarg( $boi_c ), $boi_code );
		$boi_fail += $boi_code ? 1 : 0;
	}
	echo $boi_fail ? "$boi_fail case(s) failed\n" : "all passed\n";
	exit( $boi_fail ? 1 : 0 );
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_VERSION', '9.9.9' );
if ( 'keep' === $boi_case ) {
	define( 'BOI_KEEP_DEV_FILES', true );
	define( 'BOI_REMOVE_GIT_DIR', true );
} elseif ( 'force' === $boi_case ) {
	define( 'BOI_REMOVE_GIT_DIR', true );
} elseif ( 'keep-git' === $boi_case ) {
	define( 'BOI_REMOVE_GIT_DIR', false );
}

$boi_tmp = sys_get_temp_dir() . '/boi-hk-' . getmypid() . '-' . bin2hex( random_bytes( 6 ) );
foreach ( array( '.claude', '.git/objects', 'tests', 'bin', 'docs', 'inc' ) as $boi_d ) {
	mkdir( "$boi_tmp/$boi_d", 0700, true );
}
foreach ( array( '.claude/CLAUDE.md', '.git/objects/ab', '.git/HEAD', 'tests/a-test.php', 'bin/build.php', 'docs/SSOT.md', 'inc/keep.php', 'style.css', '.gitattributes' ) as $boi_f ) {
	file_put_contents( "$boi_tmp/$boi_f", 'x' );
}
define( 'BOI_DIR', $boi_tmp );
$GLOBALS['boi_o']   = array();
$GLOBALS['boi_env'] = 'local';
function add_action() {}
function apply_filters( $h, $v ) { return $v; }
function get_option( $k, $d = false ) { return $GLOBALS['boi_o'][ $k ] ?? $d; }
function update_option( $k, $v ) { $GLOBALS['boi_o'][ $k ] = $v; return true; }
function wp_get_environment_type() { return $GLOBALS['boi_env']; }
function set_transient( $k, $v, $t = 0 ) { $GLOBALS['boi_t'][ $k ] = $v; return true; }
function add_option( $k, $v, $d = '', $a = true ) { if ( isset( $GLOBALS['boi_o'][ $k ] ) ) { return false; } $GLOBALS['boi_o'][ $k ] = $v; return true; }
function delete_option( $k ) { unset( $GLOBALS['boi_o'][ $k ] ); return true; }
define( 'HOUR_IN_SECONDS', 3600 );
define( 'MINUTE_IN_SECONDS', 60 );
require dirname( __DIR__ ) . '/inc/housekeeping.php';

$boi_fail = 0;
function boi_check( $ok, $label ) {
	global $boi_fail, $boi_case;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . "[$boi_case] $label\n";
	$boi_fail += $ok ? 0 : 1;
}
function boi_dev_left( $root ) {
	return array_values( array_filter( array( '.claude', '.git', 'tests', 'bin', 'docs', '.gitattributes' ), function ( $p ) use ( $root ) {
		return file_exists( "$root/$p" );
	} ) );
}
function boi_kept( $root ) {
	return is_file( "$root/inc/keep.php" ) && is_file( "$root/style.css" );
}

switch ( $boi_case ) {
	case 'env':
		foreach ( array( 'local', 'development' ) as $boi_e ) {
			$GLOBALS['boi_env'] = $boi_e;
			boi_remove_dev_files();
			boi_check( 6 === count( boi_dev_left( $boi_tmp ) ) && false === get_option( 'boi_dev_files_removed' ), "a $boi_e environment keeps every file" );
		}
		$GLOBALS['boi_env'] = 'production';
		boi_remove_dev_files();
		boi_check( array() === boi_dev_left( $boi_tmp ) && boi_kept( $boi_tmp ) && '9.9.9' === get_option( 'boi_dev_files_removed' ), 'production removes .git, .claude, tests, bin and docs, and keeps the theme' );
		mkdir( "$boi_tmp/tests" );
		boi_remove_dev_files();
		boi_check( is_dir( "$boi_tmp/tests" ), 'the cleanup runs once per version' );
		rmdir( "$boi_tmp/tests" );
		break;
	case 'lock':
		$GLOBALS['boi_env'] = 'production';
		add_option( 'boi_dev_files_lock', time() );
		boi_remove_dev_files();
		boi_check( 6 === count( boi_dev_left( $boi_tmp ) ) && false === get_option( 'boi_dev_files_removed' ), 'a fresh lock held by another request: nothing is deleted' );
		$GLOBALS['boi_o']['boi_dev_files_lock'] = time() - 3600;
		boi_remove_dev_files();
		boi_check( array() === boi_dev_left( $boi_tmp ) && '9.9.9' === get_option( 'boi_dev_files_removed' ) && false === get_option( 'boi_dev_files_lock' ), 'a stale lock is taken over, and released after the run' );
		break;
	case 'keep':
		$GLOBALS['boi_env'] = 'production';
		boi_remove_dev_files();
		boi_check( 6 === count( boi_dev_left( $boi_tmp ) ), 'BOI_KEEP_DEV_FILES keeps every file on production, over BOI_REMOVE_GIT_DIR' );
		break;
	case 'force':
		$GLOBALS['boi_env'] = 'local';
		boi_remove_dev_files();
		boi_check( array() === boi_dev_left( $boi_tmp ) && boi_kept( $boi_tmp ) && '9.9.9-git' === get_option( 'boi_dev_files_removed' ), 'BOI_REMOVE_GIT_DIR true cleans a local checkout, .git included' );
		break;
	case 'stuck':
		// A link that leads out of the theme is never followed or removed, so it
		// stands for a path PHP cannot delete.
		$GLOBALS['boi_env'] = 'production';
		$boi_out = sys_get_temp_dir() . '/boi-hk-out-' . getmypid();
		@mkdir( $boi_out );
		symlink( $boi_out, "$boi_tmp/.idea" );
		boi_remove_dev_files();
		boi_check( false === get_option( 'boi_dev_files_removed' ) && array( '.idea' ) === ( $GLOBALS['boi_t']['boi_dev_files_retry'] ?? null ) && is_dir( $boi_out ), 'a path left behind keeps the version unrecorded and is queued for a retry' );
		unlink( "$boi_tmp/.idea" );
		rmdir( $boi_out );
		boi_remove_dev_files();
		boi_check( '9.9.9' === get_option( 'boi_dev_files_removed' ), 'the retry records the version once every path is gone' );
		break;
	case 'keep-git':
		$GLOBALS['boi_env'] = 'production';
		boi_remove_dev_files();
		boi_check( array( '.git' ) === boi_dev_left( $boi_tmp ) && boi_kept( $boi_tmp ), 'BOI_REMOVE_GIT_DIR false keeps .git and removes the rest' );
		boi_check( '9.9.9-keep-git' === get_option( 'boi_dev_files_removed' ), 'the record carries the choice, so setting the constant to true later runs the cleanup again' );
		break;
}

boi_remove_path_test_cleanup( $boi_tmp );
function boi_remove_path_test_cleanup( $dir ) {
	if ( ! file_exists( $dir ) ) {
		return;
	}
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $it as $f ) {
		$f->isDir() ? rmdir( $f->getPathname() ) : unlink( $f->getPathname() );
	}
	rmdir( $dir );
}
exit( $boi_fail ? 1 : 0 );
