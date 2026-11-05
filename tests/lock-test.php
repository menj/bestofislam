<?php
/**
 * Stub test of inc/lock.php against a real SQL table (SQLite in memory,
 * through a small stand-in for $wpdb): one taker wins, a fresh lock is
 * refused, a stale one is taken over once, and a release removes only the
 * releaser's own lock. Run: php tests/lock-test.php
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );

/** Enough of $wpdb for inc/lock.php. */
class BOI_Test_Wpdb {
	public $options = 'wp_options';
	private $db;
	public function __construct( $file ) {
		// Each instance is its own connection to the same database file, as
		// two web requests are.
		$this->db = new PDO( 'sqlite:' . $file );
		$this->db->exec( 'CREATE TABLE IF NOT EXISTS wp_options (option_name TEXT PRIMARY KEY, option_value TEXT, autoload TEXT)' );
	}
	public function prepare( $sql, ...$args ) {
		$db = $this->db;
		return vsprintf( $sql, array_map( function ( $a ) use ( $db ) { return $db->quote( (string) $a ); }, $args ) );
	}
	public function query( $sql ) {
		return $this->db->exec( str_replace( 'INSERT IGNORE', 'INSERT OR IGNORE', $sql ) );
	}
	public function get_var( $sql ) {
		$v = $this->db->query( $sql )->fetchColumn();
		return false === $v ? null : $v;
	}
}
$boi_db_file     = tempnam( sys_get_temp_dir(), 'boi-lock-' );
$boi_request_a   = new BOI_Test_Wpdb( $boi_db_file );
$boi_request_b   = new BOI_Test_Wpdb( $boi_db_file );
$GLOBALS['wpdb'] = $boi_request_a;
function wp_generate_password( $n ) { return bin2hex( random_bytes( (int) ( $n / 2 ) ) ); }
function wp_cache_delete() { return true; }
require dirname( __DIR__ ) . '/inc/lock.php';

$boi_fail = 0;
function boi_check( $ok, $label ) {
	global $boi_fail;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . $label . "\n";
	$boi_fail += $ok ? 0 : 1;
}
$held = function () {
	return (string) $GLOBALS['wpdb']->get_var( "SELECT option_value FROM wp_options WHERE option_name = 'ce_t'" );
};

$a = boi_lock_take( 'ce_t', 60 );
boi_check( '' !== $a && $held() === $a, 'the first taker gets the lock' );
boi_check( '' === boi_lock_take( 'ce_t', 60 ), 'a fresh lock is refused' );
boi_lock_release( 'ce_t', 'someone-else' );
boi_check( $held() === $a, 'a release with another token leaves the lock alone' );

$GLOBALS['wpdb']->query( "UPDATE wp_options SET option_value = '" . ( time() - 3600 ) . "|old' WHERE option_name = 'ce_t'" );
$b = boi_lock_take( 'ce_t', 60 );
boi_check( '' !== $b && $held() === $b, 'a stale lock is taken over' );
boi_check( '' === boi_lock_take( 'ce_t', 60 ), 'and only once' );
boi_lock_release( 'ce_t', $a );
boi_check( $held() === $b, 'the old holder cannot release the new holder\'s lock' );
boi_lock_release( 'ce_t', $b );
boi_check( '' === $held(), 'the holder releases it' );
boi_check( '' !== boi_lock_take( 'ce_t', 60 ), 'and it can be taken again' );

// Two requests, two connections: while one holds the lock the other is
// refused, and it gets the lock once the first lets go.
$GLOBALS['wpdb'] = $boi_request_a;
$GLOBALS['wpdb']->query( "DELETE FROM wp_options WHERE option_name = 'ce_t2'" );
$first = boi_lock_take( 'ce_t2', 60 );
$GLOBALS['wpdb'] = $boi_request_b;
boi_check( '' !== $first && '' === boi_lock_take( 'ce_t2', 60 ), 'a second connection is refused while the first holds the lock' );
$GLOBALS['wpdb'] = $boi_request_a;
boi_lock_release( 'ce_t2', $first );
$GLOBALS['wpdb'] = $boi_request_b;
boi_check( '' !== boi_lock_take( 'ce_t2', 60 ), 'the second connection gets it after the release' );
@unlink( $boi_db_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

echo $boi_fail ? "$boi_fail failed\n" : "all passed\n";
exit( $boi_fail ? 1 : 0 );
