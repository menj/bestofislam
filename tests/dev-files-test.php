<?php
/**
 * inc/dev-files.php against a temporary theme folder and a real SQL table
 * (SQLite, through a small stand-in for $wpdb, with inc/lock.php itself):
 * which entries are listed, that local and development sites and
 * BOI_KEEP_DEV_FILES keep them, the lease, the clean-up, the record, the
 * renewal, symlinks, and the hourly look.
 *
 * Run: php tests/dev-files-test.php
 *
 * The constant case needs a fresh process, so the test runs itself again
 * for it.
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

$boi_case = $argv[1] ?? 'main';
$boi_sub_code = 0;
if ( 'main' === $boi_case ) {
	passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' keep', $boi_c );
	$boi_sub_code += $boi_c;
	// A file PHP cannot remove needs a folder the test process does not own:
	// started as root, the case builds one and then runs as nobody.
	// The parent, still root, removes what the case leaves behind.
	if ( function_exists( 'posix_geteuid' ) && 0 === posix_geteuid() && posix_getpwnam( 'nobody' ) ) {
		foreach ( array( 'stuck', 'unreadable' ) as $boi_sub ) {
			$boi_sub_dir = sys_get_temp_dir() . '/boi-df-' . $boi_sub . '-' . getmypid() . '-' . bin2hex( random_bytes( 4 ) );
			passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' ' . $boi_sub . ' ' . escapeshellarg( $boi_sub_dir ), $boi_c );
			$boi_sub_code += $boi_c;
			foreach ( array( $boi_sub_dir, "$boi_sub_dir-outside" ) as $boi_d ) {
				if ( is_dir( $boi_d ) ) {
					exec( 'chmod -R u+rwx ' . escapeshellarg( $boi_d ) . ' && rm -rf ' . escapeshellarg( $boi_d ) );
				}
			}
			foreach ( array( "$boi_sub_dir.db", "$boi_sub_dir.log" ) as $boi_f ) {
				@unlink( $boi_f ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			}
		}
	} else {
		echo "skip [stuck] [unreadable] need root to build folders the test cannot delete or read\n";
	}
}

define( 'ABSPATH', __DIR__ . '/' );
define( 'BOI_VERSION', '9.9.9' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
if ( 'keep' === $boi_case ) {
	define( 'BOI_KEEP_DEV_FILES', true );
}

/** Enough of $wpdb: one connection per request, every query logged. */
class BOI_Test_Wpdb {
	public $options = 'wp_options';
	public $log     = array();
	public $on_read = null;
	private $db;
	public function __construct( $file ) {
		$this->db = new PDO( 'sqlite:' . $file );
		$this->db->exec( 'CREATE TABLE IF NOT EXISTS wp_options (option_name TEXT PRIMARY KEY, option_value TEXT, autoload TEXT)' );
	}
	public function prepare( $sql, ...$args ) {
		$db = $this->db;
		return vsprintf( $sql, array_map( function ( $a ) use ( $db ) { return $db->quote( (string) $a ); }, $args ) );
	}
	public function query( $sql ) {
		$this->log[] = $sql;
		return $this->db->exec( str_replace( 'INSERT IGNORE', 'INSERT OR IGNORE', $sql ) );
	}
	public function get_var( $sql ) {
		$this->log[] = $sql;
		$v = $this->db->query( $sql )->fetchColumn();
		// A hook between this read and whatever the caller does next, to
		// interleave a second request.
		if ( $this->on_read ) {
			$f             = $this->on_read;
			$this->on_read = null;
			$f();
		}
		return false === $v ? null : $v;
	}
}

// The root-only cases run in a folder the parent names, so it can clean up.
$boi_given = $argv[2] ?? '';
$boi_db    = '' !== $boi_given ? "$boi_given.db" : tempnam( sys_get_temp_dir(), 'boi-df-' );
$boi_a   = new BOI_Test_Wpdb( $boi_db );
$boi_b   = new BOI_Test_Wpdb( $boi_db );
$GLOBALS['wpdb'] = $boi_a;

$boi_tmp = '' !== $boi_given ? $boi_given : sys_get_temp_dir() . '/boi-df-' . getmypid() . '-' . bin2hex( random_bytes( 4 ) );
$boi_out = $boi_tmp . '-outside';
define( 'BOI_DIR', $boi_tmp );

$GLOBALS['boi_env']     = 'production';
$GLOBALS['boi_cron']    = array();
$GLOBALS['boi_filters'] = array();
function add_action() {}
function apply_filters( $hook, $value ) { return array_key_exists( $hook, $GLOBALS['boi_filters'] ) ? $GLOBALS['boi_filters'][ $hook ] : $value; }
function wp_get_environment_type() { return $GLOBALS['boi_env']; }
function wp_generate_password( $n ) { return bin2hex( random_bytes( (int) ( $n / 2 ) ) ); }
function wp_cache_delete() { return true; }
function get_option( $k, $d = false ) {
	$v = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( 'SELECT option_value FROM wp_options WHERE option_name = %s', $k ) );
	return null === $v ? $d : $v;
}
function update_option( $k, $v ) { $GLOBALS['wpdb']->query( $GLOBALS['wpdb']->prepare( 'INSERT OR REPLACE INTO wp_options (option_name, option_value, autoload) VALUES (%s, %s, %s)', $k, $v, 'yes' ) ); return true; }
function delete_option( $k ) { $GLOBALS['wpdb']->query( $GLOBALS['wpdb']->prepare( 'DELETE FROM wp_options WHERE option_name = %s', $k ) ); return true; }
function wp_next_scheduled( $hook ) { return $GLOBALS['boi_cron'][ $hook ] ?? false; }
function wp_schedule_event( $t, $r, $hook ) { $GLOBALS['boi_cron'][ $hook ] = $t; return true; }
function wp_clear_scheduled_hook( $hook ) { unset( $GLOBALS['boi_cron'][ $hook ] ); return 1; }
require dirname( __DIR__ ) . '/inc/lock.php';
require dirname( __DIR__ ) . '/inc/dev-files.php';

$boi_fail = 0;
function boi_check( $ok, $label ) {
	global $boi_fail, $boi_case;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . "[$boi_case] $label\n";
	$boi_fail += $ok ? 0 : 1;
}
function boi_put( $root, $files ) {
	foreach ( $files as $f ) {
		if ( ! is_dir( dirname( "$root/$f" ) ) ) {
			mkdir( dirname( "$root/$f" ), 0755, true );
		}
		file_put_contents( "$root/$f", 'x' );
	}
}
function boi_build( $root, $out ) {
	boi_put( $root, array( '.git/HEAD', '.git/objects/ab/cdef', '.github/workflows/checks.yml', '.gitignore', '.claude/CLAUDE.md', 'tools/build.php', 'tests/a-test.php', 'bin/build.py', 'dist/bestofislam/style.css' ) );
	boi_put( $root, array( '.htaccess', '.user.ini', '.well-known/security.txt', 'style.css', 'functions.php', 'inc/keep.php', 'inc/data/seed.json', 'data/d.json', 'docs/SSOT.md' ) );
	// Git keeps its objects read-only.
	chmod( "$root/.git/objects/ab/cdef", 0444 );
	chmod( "$root/.git/objects/ab", 0555 );
	// A link to a folder outside the theme, with a file in it.
	boi_put( $out, array( 'precious.txt' ) );
	if ( ! is_link( "$root/.idea" ) ) {
		symlink( $out, "$root/.idea" );
	}
}
function boi_kept( $root, $out ) {
	foreach ( array( '.htaccess', '.user.ini', '.well-known/security.txt', 'style.css', 'functions.php', 'inc/keep.php', 'inc/data/seed.json', 'data/d.json', 'docs/SSOT.md' ) as $f ) {
		if ( ! is_file( "$root/$f" ) ) {
			return false;
		}
	}
	return is_file( "$out/precious.txt" );
}
function boi_lease() {
	return (string) $GLOBALS['wpdb']->get_var( "SELECT option_value FROM wp_options WHERE option_name = 'boi_dev_files_lock'" );
}
function boi_rm( $dir ) {
	if ( is_link( $dir ) || is_file( $dir ) ) {
		unlink( $dir );
		return;
	}
	if ( ! is_dir( $dir ) ) {
		return;
	}
	chmod( $dir, 0755 );
	foreach ( array_diff( scandir( $dir ), array( '.', '..' ) ) as $i ) {
		boi_rm( "$dir/$i" );
	}
	rmdir( $dir );
}

$boi_dev = array( '.claude', '.git', '.github', '.gitignore', '.idea', 'bin', 'dist', 'tests', 'tools' );
boi_build( $boi_tmp, $boi_out );

if ( 'unreadable' === $boi_case ) {
	// The theme folder belongs to root and cannot be listed (0711); the
	// clean-up runs as nobody.
	$boi_nobody = posix_getpwnam( 'nobody' );
	$boi_log    = "$boi_tmp.log";
	touch( $boi_log );
	chown( $boi_log, $boi_nobody['uid'] );
	chown( $boi_db, $boi_nobody['uid'] );
	chmod( $boi_tmp, 0711 );
	ini_set( 'error_log', $boi_log );
	posix_setgid( $boi_nobody['gid'] );
	posix_setuid( $boi_nobody['uid'] );
	boi_dev_files_maybe_run();
	boi_check( null === boi_dev_files_entries() && false === get_option( 'boi_dev_files_removed' ) && '' !== boi_lease(), 'a theme folder that cannot be read is never taken for a clean one: the version stays unrecorded and the lease held' );
	update_option( 'boi_dev_files_removed', '9.9.9' );
	$boi_a->log = array();
	boi_dev_files_hourly();
	boi_check( '9.9.9' === get_option( 'boi_dev_files_removed' ) && array() === preg_grep( '/^(DELETE|UPDATE|INSERT)/', $boi_a->log ), 'the hourly callback leaves an unreadable folder alone' );
	exit( $boi_fail ? 1 : 0 );
} elseif ( 'stuck' === $boi_case ) {
	// .git/locked and its file belong to root; the rest of the folder, the
	// database and the log to nobody, who then runs the clean-up.
	boi_put( $boi_tmp, array( '.git/locked/pack' ) );
	$boi_nobody = posix_getpwnam( 'nobody' );
	$boi_log    = "$boi_tmp.log";
	touch( $boi_log );
	foreach ( array( $boi_db, $boi_log ) as $boi_f ) {
		chown( $boi_f, $boi_nobody['uid'] );
	}
	$boi_walk = function ( $p ) use ( &$boi_walk, $boi_nobody, $boi_tmp ) {
		if ( "$boi_tmp/.git/locked" === $p ) {
			return;
		}
		lchown( $p, $boi_nobody['uid'] );
		if ( is_dir( $p ) && ! is_link( $p ) ) {
			foreach ( array_diff( scandir( $p ), array( '.', '..' ) ) as $i ) {
				$boi_walk( "$p/$i" );
			}
		}
	};
	$boi_walk( $boi_tmp );
	ini_set( 'error_log', $boi_log );
	posix_setgid( $boi_nobody['gid'] );
	posix_setuid( $boi_nobody['uid'] );

	boi_dev_files_maybe_run();
	$boi_lease_1 = boi_lease();
	boi_check( is_file( "$boi_tmp/.git/locked/pack" ) && array( '.git' ) === boi_dev_files_entries() && boi_kept( $boi_tmp, $boi_out ), 'a folder PHP cannot remove stays, and everything else goes' );
	boi_check( false === get_option( 'boi_dev_files_removed' ) && '' !== $boi_lease_1, 'the version stays unrecorded and the lease stays held' );
	$boi_a->log = array();
	boi_dev_files_maybe_run();
	boi_check( $boi_lease_1 === boi_lease() && 0 === count( preg_grep( '/^(UPDATE|DELETE)/', $boi_a->log ) ), 'the next request finds the lease fresh and does no clean-up' );
	// Five minutes on, the lease is stale and the retry comes; the log line
	// is written once a day.
	update_option( 'boi_dev_files_lock', ( time() - 301 ) . '|' . substr( $boi_lease_1, strpos( $boi_lease_1, '|' ) + 1 ) );
	boi_dev_files_maybe_run();
	boi_check( '' !== boi_lease() && $boi_lease_1 !== boi_lease() && false === get_option( 'boi_dev_files_removed' ), 'five minutes on, the stale lease is taken over and the clean-up tries again' );
	$boi_lines = array_values( array_filter( file( $boi_log ) ) );
	boi_check( 1 === count( $boi_lines ) && false !== strpos( $boi_lines[0], ' 1 development file(s)' ) && false !== strpos( $boi_lines[0], 'delete them by FTP' ), 'two failed runs on one day log one line, naming how many files remain: ' . trim( (string) ( $boi_lines[0] ?? '' ) ) );
	exit( $boi_fail ? 1 : 0 );
} elseif ( 'keep' === $boi_case ) {
	boi_dev_files_maybe_run();
	boi_dev_files_schedule();
	boi_check( $boi_dev === boi_dev_files_entries() && false === get_option( 'boi_dev_files_removed' ), 'BOI_KEEP_DEV_FILES keeps every entry on a production site' );
	boi_check( array() === $GLOBALS['boi_cron'], 'and schedules no hourly event' );
} else {
	// 1. What is listed.
	boi_check( $boi_dev === boi_dev_files_entries(), 'listed: the hidden entries except .htaccess, .user.ini and .well-known, and tools, tests, bin and dist' );

	// 2. Local and development sites keep them.
	foreach ( array( 'local', 'development' ) as $boi_e ) {
		$GLOBALS['boi_env'] = $boi_e;
		boi_dev_files_maybe_run();
		boi_dev_files_schedule();
		boi_check( $boi_dev === boi_dev_files_entries() && false === get_option( 'boi_dev_files_removed' ) && array() === $GLOBALS['boi_cron'], "a $boi_e site keeps every entry and schedules nothing" );
	}
	$GLOBALS['boi_env'] = 'production';

	// 3. A fresh lease held by another request keeps this one out.
	update_option( 'boi_dev_files_lock', time() . '|other' );
	boi_dev_files_maybe_run();
	boi_check( $boi_dev === boi_dev_files_entries() && false === get_option( 'boi_dev_files_removed' ) && time() . '|other' === boi_lease(), 'a fresh lease held by another request keeps this one out' );

	// 4. Two requests read the same stale lease: only one wins. Request A
	// reads it; before A writes, request B reads it and takes it over.
	$boi_stale = ( time() - 600 ) . '|dead';
	update_option( 'boi_dev_files_lock', $boi_stale );
	$boi_won_b = '';
	$boi_a->on_read = function () use ( $boi_a, $boi_b, &$boi_won_b ) {
		$GLOBALS['wpdb'] = $boi_b;
		$boi_won_b       = boi_lock_take( 'boi_dev_files_lock', BOI_DEV_FILES_LEASE );
		$GLOBALS['wpdb'] = $boi_a;
	};
	// A's INSERT IGNORE fails on the existing row; its SELECT reads the stale
	// value (and lets B in); its UPDATE then finds the value changed.
	$boi_won_a = boi_lock_take( 'boi_dev_files_lock', BOI_DEV_FILES_LEASE );
	boi_check( '' !== $boi_won_b && '' === $boi_won_a && boi_lock_holds( 'boi_dev_files_lock', $boi_won_b ), 'of two requests that read the same stale lease, only one wins' );

	// 5. A stale lease is taken over, and the files go.
	update_option( 'boi_dev_files_lock', $boi_stale );
	$boi_a->log = array();
	boi_dev_files_maybe_run();
	$boi_renews = count( preg_grep( '/^UPDATE .* LIKE /', $boi_a->log ) );
	boi_check( array() === boi_dev_files_entries(), 'a stale lease is taken over, and every development entry goes (read-only git objects too)' );
	boi_check( boi_kept( $boi_tmp, $boi_out ), 'the code, the data, docs, .htaccess, .user.ini and .well-known stay' );
	boi_check( ! is_link( "$boi_tmp/.idea" ) && is_file( "$boi_out/precious.txt" ), 'a symlink is removed and its target is not' );
	// A folder swapped for a link to the outside part-way through: what the
	// run would delete next, by its old path, is left alone.
	symlink( $boi_out, "$boi_tmp/.cache" );
	$GLOBALS['boi_dev_files_token']   = 'x';
	$GLOBALS['boi_dev_files_renewed'] = time();
	$GLOBALS['boi_dev_files_lost']    = false;
	boi_dev_files_remove( "$boi_tmp/.cache/precious.txt" );
	unset( $GLOBALS['boi_dev_files_token'], $GLOBALS['boi_dev_files_renewed'], $GLOBALS['boi_dev_files_lost'] );
	boi_check( is_file( "$boi_out/precious.txt" ), 'a folder swapped for a symlink part-way through: nothing outside the theme is deleted' );
	unlink( "$boi_tmp/.cache" );
	boi_check( '9.9.9' === get_option( 'boi_dev_files_removed' ) && '' === boi_lease(), 'the version is recorded and the lease freed' );
	boi_check( 0 === $boi_renews, 'a quick run does not renew its lease (at most once a minute)' );

	// 6. Not twice for one version.
	boi_put( $boi_tmp, array( 'tests/b-test.php' ) );
	$boi_a->log = array();
	boi_dev_files_maybe_run();
	boi_check( is_file( "$boi_tmp/tests/b-test.php" ) && 1 === count( $boi_a->log ), 'it does not run twice for one version (one option read, nothing else)' );
	boi_rm( "$boi_tmp/tests" );

	// 7. A running clean-up renews its lease once its minute is up (here
	// every entry, with the interval at zero).
	boi_build( $boi_tmp, $boi_out );
	delete_option( 'boi_dev_files_removed' );
	$GLOBALS['boi_filters']['boi_dev_files_renew_every'] = 0;
	$boi_a->log = array();
	boi_dev_files_maybe_run();
	$boi_renews = count( preg_grep( '/^UPDATE .* LIKE /', $boi_a->log ) );
	unset( $GLOBALS['boi_filters']['boi_dev_files_renew_every'] );
	boi_check( $boi_renews > 1 && array() === boi_dev_files_entries() && '9.9.9' === get_option( 'boi_dev_files_removed' ), "a running clean-up renews its lease ($boi_renews renewals) and still finishes" );

	// 8. The hourly look.
	$boi_a->log = array();
	boi_dev_files_hourly();
	boi_check( array() === $boi_a->log && '9.9.9' === get_option( 'boi_dev_files_removed' ), 'the hourly callback leaves a folder with no development entries alone' );
	boi_put( $boi_tmp, array( '.git/HEAD', '.git/refs/heads/main' ) );
	boi_dev_files_hourly();
	boi_check( ! file_exists( "$boi_tmp/.git" ) && '9.9.9' === get_option( 'boi_dev_files_removed' ) && '' === boi_lease() && boi_kept( $boi_tmp, $boi_out ), 'the hourly callback removes a .git uploaded after the version was recorded' );

	// 9. Scheduling.
	boi_dev_files_schedule();
	boi_check( isset( $GLOBALS['boi_cron']['boi_dev_files_hourly'] ), 'a production site schedules the hourly event' );
	boi_dev_files_unschedule();
	boi_check( array() === $GLOBALS['boi_cron'], 'switching theme clears it' );
}

boi_rm( $boi_tmp );
boi_rm( $boi_out );
@unlink( $boi_db ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
if ( 'main' === $boi_case ) {
	$boi_fail += $boi_sub_code ? 1 : 0;
	echo $boi_fail ? "$boi_fail check(s) failed\n" : "all passed\n";
}
exit( $boi_fail ? 1 : 0 );
