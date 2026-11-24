<?php
/**
 * Best of Islam: removes the development files an FTP upload brings along.
 *
 * The owner uploads the theme over FTP from a working copy, so the
 * repository (.git, .gitignore, .github, .claude and other hidden entries),
 * the build scripts (bin, or tools) and the self-tests (tests) reach the
 * live server, where the web server may serve or run them. On the first
 * request of any kind after the theme version changes, this deletes every
 * hidden file and folder at the top of the theme folder except .htaccess,
 * .user.ini and .well-known, and the tools, tests and bin folders. The data,
 * the code and the rest of the theme stay.
 *
 * One request runs it, under a lease kept in wp_options (inc/lock.php):
 * a free lease is taken with one INSERT IGNORE, a lease nobody renewed for
 * five minutes is taken over with one compare-and-set UPDATE, and a lease is
 * released with one DELETE that matches only its holder's token. The holder
 * renews the lease at most once a minute while it works.
 *
 * The version is recorded only when nothing is left. A file PHP cannot
 * remove keeps the version unrecorded and the lease held, so the next try
 * comes when the lease goes stale, five minutes on, and a line in the PHP
 * error log, once a day, says how many files remain for deletion by FTP.
 *
 * An upload can put .git back at any time, so an hourly WP-Cron event looks
 * again: when the folder holds a development entry it forgets the recorded
 * version and runs the same clean-up under the same lease.
 *
 * Nothing is deleted on a local or development site
 * (wp_get_environment_type()), or where BOI_KEEP_DEV_FILES is defined true
 * in wp-config.php. Until the first clean-up, the theme's .htaccess refuses
 * the same entries.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/** Option that records the theme version once the folder is clean. */
const BOI_DEV_FILES_OPTION = 'boi_dev_files_removed';

/** Option that holds the lease ("<time>|<token>", inc/lock.php). */
const BOI_DEV_FILES_LOCK = 'boi_dev_files_lock';

/** Option that records the day of the last "could not remove" log line. */
const BOI_DEV_FILES_LOGGED = 'boi_dev_files_logged';

/** The hourly WP-Cron event. */
const BOI_DEV_FILES_EVENT = 'boi_dev_files_hourly';

/** Seconds without renewal after which a lease belongs to a dead request. */
const BOI_DEV_FILES_LEASE = 300;

/**
 * Whether the development files stay: a local or development site, or
 * BOI_KEEP_DEV_FILES defined true.
 *
 * @return bool
 */
function boi_keep_dev_files() {
	if ( defined( 'BOI_KEEP_DEV_FILES' ) && BOI_KEEP_DEV_FILES ) {
		return true;
	}
	$env = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
	return in_array( $env, array( 'local', 'development' ), true );
}

/**
 * Names, at the top of the theme folder, of the development entries present
 * now: every hidden file and folder except .htaccess, .user.ini and
 * .well-known, and the tools, tests and bin folders.
 *
 * @return string[]
 */
function boi_dev_files_entries() {
	$items = @scandir( BOI_DIR ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( false === $items ) {
		return array();
	}
	$keep  = array( '.', '..', '.htaccess', '.user.ini', '.well-known' );
	$names = array();
	foreach ( $items as $name ) {
		if ( in_array( $name, $keep, true ) ) {
			continue;
		}
		if ( '.' === $name[0] || in_array( $name, array( 'tools', 'tests', 'bin' ), true ) ) {
			$names[] = $name;
		}
	}
	return array_values( (array) apply_filters( 'boi_dev_paths', $names ) );
}

/**
 * The recorded version, read from the database itself, past any cache.
 *
 * @return string
 */
function boi_dev_files_recorded() {
	global $wpdb;
	return (string) $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", BOI_DEV_FILES_OPTION ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * Renews the lease when a minute has passed since the last renewal.
 *
 * @return bool False once the lease is lost; the run then stops.
 */
function boi_dev_files_keep_lease() {
	if ( ! empty( $GLOBALS['boi_dev_files_lost'] ) ) {
		return false;
	}
	$every = (int) apply_filters( 'boi_dev_files_renew_every', MINUTE_IN_SECONDS );
	if ( time() - (int) $GLOBALS['boi_dev_files_renewed'] < $every ) {
		return true;
	}
	$GLOBALS['boi_dev_files_renewed'] = time(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	if ( ! boi_lock_renew( BOI_DEV_FILES_LOCK, $GLOBALS['boi_dev_files_token'] ) ) {
		$GLOBALS['boi_dev_files_lost'] = true; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		return false;
	}
	return true;
}

/**
 * Deletes a file, a symlink or a folder tree. A symlink is removed itself
 * and never followed; a read-only file or folder is made writable first.
 * The paths come from scanning the theme folder down, so nothing outside it
 * is reached.
 *
 * @param string $path Absolute path.
 * @return void
 */
function boi_dev_files_remove( $path ) {
	if ( ! boi_dev_files_keep_lease() ) {
		return;
	}
	// phpcs:disable WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	if ( is_link( $path ) ) {
		// A link to a folder is a directory entry on Windows: rmdir removes it.
		if ( ! @unlink( $path ) ) {
			@rmdir( $path );
		}
		return;
	}
	if ( is_dir( $path ) ) {
		if ( ! is_writable( $path ) || ! is_readable( $path ) ) {
			@chmod( $path, 0755 );
		}
		$items = @scandir( $path );
		if ( false === $items ) {
			return;
		}
		foreach ( array_diff( $items, array( '.', '..' ) ) as $item ) {
			boi_dev_files_remove( $path . DIRECTORY_SEPARATOR . $item );
		}
		if ( empty( $GLOBALS['boi_dev_files_lost'] ) ) {
			@rmdir( $path );
		}
		return;
	}
	if ( ! is_writable( $path ) ) {
		@chmod( $path, 0644 );
	}
	@unlink( $path );
	// phpcs:enable
}

/**
 * How many files a path still holds: a file or a symlink counts one, a
 * folder that cannot be read counts one.
 *
 * @param string $path Absolute path.
 * @return int
 */
function boi_dev_files_count( $path ) {
	if ( is_link( $path ) || ! is_dir( $path ) ) {
		return file_exists( $path ) || is_link( $path ) ? 1 : 0;
	}
	$items = @scandir( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( false === $items ) {
		return 1;
	}
	$n = 0;
	foreach ( array_diff( $items, array( '.', '..' ) ) as $item ) {
		$n += boi_dev_files_count( $path . DIRECTORY_SEPARATOR . $item );
	}
	return max( 1, $n );
}

/**
 * Runs the clean-up under the lease.
 *
 * @return bool Whether the folder is clean and the version recorded.
 */
function boi_dev_files_run() {
	// inc/lock.php is loaded and complete (an upload cut off part-way leaves
	// the constant undefined, and the clean-up waits).
	if ( ! defined( 'BOI_LOCK_READY' ) ) {
		return false;
	}
	$token = boi_lock_take( BOI_DEV_FILES_LOCK, BOI_DEV_FILES_LEASE );
	if ( '' === $token ) {
		return false;
	}
	// Another request may have finished between our cached read and the lease.
	if ( BOI_VERSION === boi_dev_files_recorded() ) {
		boi_lock_release( BOI_DEV_FILES_LOCK, $token );
		return true;
	}

	$GLOBALS['boi_dev_files_token']   = $token; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$GLOBALS['boi_dev_files_renewed'] = time(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$GLOBALS['boi_dev_files_lost']    = false; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	foreach ( boi_dev_files_entries() as $name ) {
		boi_dev_files_remove( BOI_DIR . DIRECTORY_SEPARATOR . $name );
	}
	clearstatcache();

	$lost = ! empty( $GLOBALS['boi_dev_files_lost'] ) || ! boi_lock_holds( BOI_DEV_FILES_LOCK, $token );
	unset( $GLOBALS['boi_dev_files_token'], $GLOBALS['boi_dev_files_renewed'], $GLOBALS['boi_dev_files_lost'] );
	if ( $lost ) {
		// Another request took the lease over: it finishes, and records.
		return false;
	}

	$left = 0;
	foreach ( boi_dev_files_entries() as $name ) {
		$left += boi_dev_files_count( BOI_DIR . DIRECTORY_SEPARATOR . $name );
	}
	if ( 0 === $left ) {
		update_option( BOI_DEV_FILES_OPTION, BOI_VERSION, true ); // Autoloaded: read on every request.
		boi_lock_release( BOI_DEV_FILES_LOCK, $token );
		return true;
	}

	// Something stays: keep the lease, renewed now, so the next try comes
	// when it goes stale in five minutes, and say so once a day.
	boi_lock_renew( BOI_DEV_FILES_LOCK, $token );
	$today = gmdate( 'Y-m-d' );
	if ( get_option( BOI_DEV_FILES_LOGGED ) !== $today ) {
		update_option( BOI_DEV_FILES_LOGGED, $today, false );
		error_log( sprintf( 'Best of Islam: %d development file(s) in %s could not be removed; delete them by FTP.', $left, BOI_DIR ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
	return false;
}

/**
 * On the first request after the theme version changes, any kind of request.
 *
 * @return void
 */
function boi_dev_files_maybe_run() {
	if ( BOI_VERSION === get_option( BOI_DEV_FILES_OPTION ) || boi_keep_dev_files() ) {
		return;
	}
	boi_dev_files_run();
}
add_action( 'init', 'boi_dev_files_maybe_run', 1 );

/**
 * The hourly look: nothing to do while the folder has no development entry.
 * When it finds one, it forgets the recorded version and runs the clean-up.
 *
 * @return void
 */
function boi_dev_files_hourly() {
	if ( boi_keep_dev_files() || ! boi_dev_files_entries() ) {
		return;
	}
	delete_option( BOI_DEV_FILES_OPTION );
	boi_dev_files_run();
}
add_action( BOI_DEV_FILES_EVENT, 'boi_dev_files_hourly' );

/**
 * Schedules the hourly event, except on a site that keeps its development
 * files, where an event left from before is cleared.
 *
 * @return void
 */
function boi_dev_files_schedule() {
	$next = wp_next_scheduled( BOI_DEV_FILES_EVENT );
	if ( boi_keep_dev_files() ) {
		if ( $next ) {
			wp_clear_scheduled_hook( BOI_DEV_FILES_EVENT );
		}
		return;
	}
	if ( ! $next ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', BOI_DEV_FILES_EVENT );
	}
}
add_action( 'init', 'boi_dev_files_schedule' );

/**
 * Clears the hourly event when the site switches to another theme.
 *
 * @return void
 */
function boi_dev_files_unschedule() {
	wp_clear_scheduled_hook( BOI_DEV_FILES_EVENT );
}
add_action( 'switch_theme', 'boi_dev_files_unschedule' );
