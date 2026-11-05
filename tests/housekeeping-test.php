<?php
/**
 * inc/housekeeping.php: in a git working tree nothing is deleted; without
 * .git the development files, .claude among them, are removed.
 *
 * Run: php tests/housekeeping-test.php
 *
 * @package BestOfIslam
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_VERSION', '9.9.9' );
$boi_tmp = sys_get_temp_dir() . '/boi-hk-' . getmypid() . '-' . bin2hex( random_bytes( 6 ) );
mkdir( $boi_tmp . '/.claude', 0700, true );
file_put_contents( $boi_tmp . '/.claude/CLAUDE.md', 'x' );
file_put_contents( $boi_tmp . '/.gitattributes', 'x' );
mkdir( $boi_tmp . '/.git' );
define( 'BOI_DIR', $boi_tmp );
$GLOBALS['boi_o'] = array();
function add_action() {}
function apply_filters( $h, $v ) { return $v; }
function get_option( $k, $d = false ) { return $GLOBALS['boi_o'][ $k ] ?? $d; }
function update_option( $k, $v ) { $GLOBALS['boi_o'][ $k ] = $v; return true; }
require dirname( __DIR__ ) . '/inc/housekeeping.php';

$boi_fail = 0;
function boi_check( $ok, $label ) {
	global $boi_fail;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . $label . "\n";
	$boi_fail += $ok ? 0 : 1;
}

boi_remove_dev_files();
boi_check( is_file( "$boi_tmp/.claude/CLAUDE.md" ) && is_file( "$boi_tmp/.gitattributes" ) && false === get_option( 'boi_dev_files_removed' ), 'a git working tree is left alone' );
rmdir( "$boi_tmp/.git" );
file_put_contents( "$boi_tmp/.git", "gitdir: /elsewhere\n" );
boi_remove_dev_files();
boi_check( is_file( "$boi_tmp/.claude/CLAUDE.md" ), 'a worktree (.git file) is left alone' );
unlink( "$boi_tmp/.git" );
boi_remove_dev_files();
boi_check( ! file_exists( "$boi_tmp/.claude" ) && ! file_exists( "$boi_tmp/.gitattributes" ) && '9.9.9' === get_option( 'boi_dev_files_removed' ), 'without .git the development files and .claude go' );

@rmdir( $boi_tmp );
echo $boi_fail ? "$boi_fail failed\n" : "all passed\n";
exit( $boi_fail ? 1 : 0 );
