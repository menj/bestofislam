<?php
/**
 * Removes development files from the theme directory.
 *
 * A theme uploaded from a clone carries the repository (.git), the agent
 * notes (.claude), the tests, the build scripts and the docs, beside the
 * workflow definitions, git hooks and editor settings used to develop it.
 * None of them serve a page. Several are readable over the web, and .git
 * holds the whole source history, so this deletes them on activation and
 * after each version change.
 *
 * Decision (1.100.8): on a production server the cleanup runs even when
 * the theme folder is a git working tree, and .git goes with the rest. The
 * owner deploys over FTP with .git included, and a public .git is the
 * theme's full source. The files are kept only in a development
 * environment: wp_get_environment_type() is "local" or "development", or
 * BOI_KEEP_DEV_FILES is defined true in wp-config.php. BOI_REMOVE_GIT_DIR
 * still works: true forces the cleanup, .git included, even in a
 * development environment, and false keeps .git on production while the
 * other paths go.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Paths, relative to the theme root, that have no business on a server.
 *
 * @return string[]
 */
function boi_dev_paths() {
	$paths = array( '.git', '.github', '.githooks', '.claude', '.vscode', '.idea', '.gitattributes', '.gitignore', '.editorconfig', '.DS_Store', 'dist', 'tests', 'bin', 'docs' );

	// BOI_REMOVE_GIT_DIR defined false keeps the repository on production.
	if ( defined( 'BOI_REMOVE_GIT_DIR' ) && ! BOI_REMOVE_GIT_DIR ) {
		$paths = array_values( array_diff( $paths, array( '.git' ) ) );
	}

	return (array) apply_filters( 'boi_dev_paths', $paths );
}

/**
 * Deletes a file or a directory tree inside the theme root. Refuses
 * anything that resolves outside it.
 *
 * @param string $path Absolute path.
 * @return bool
 */
function boi_remove_path( $path ) {
	// Every 200 entries the cleanup renews its lock, so a long run over a
	// large .git tree is never taken for a dead one.
	static $seen = 0;
	// A holder that finds its lock gone (another request took it over after
	// a long pause) stops at once and leaves the rest to that request.
	if ( ! empty( $GLOBALS['boi_dev_files_lost'] ) ) {
		return false;
	}
	if ( 0 === ++$seen % 200 && ! empty( $GLOBALS['boi_dev_files_token'] ) && function_exists( 'boi_lock_renew' )
		&& ! boi_lock_renew( 'boi_dev_files_lock', $GLOBALS['boi_dev_files_token'] ) ) {
		$GLOBALS['boi_dev_files_lost'] = true; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		return false;
	}
	$root = realpath( BOI_DIR );
	$real = realpath( $path );

	if ( ! $root || ! $real || 0 !== strpos( $real, $root . DIRECTORY_SEPARATOR ) ) {
		return false;
	}

	if ( is_link( $path ) || is_file( $path ) ) {
		return @unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	}

	$items = scandir( $path );

	if ( false === $items ) {
		return false;
	}

	foreach ( array_diff( $items, array( '.', '..' ) ) as $item ) {
		boi_remove_path( $path . DIRECTORY_SEPARATOR . $item );
	}

	return @rmdir( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
}

/**
 * Whether the theme folder is a git working tree.
 *
 * @return bool
 */
function boi_is_git_checkout() {
	return file_exists( BOI_DIR . DIRECTORY_SEPARATOR . '.git' );
}

/**
 * Whether the development files must stay: a development environment, or
 * BOI_KEEP_DEV_FILES defined true. BOI_KEEP_DEV_FILES wins over
 * BOI_REMOVE_GIT_DIR; BOI_REMOVE_GIT_DIR true wins over the environment.
 *
 * @return bool
 */
function boi_keep_dev_files() {
	if ( defined( 'BOI_KEEP_DEV_FILES' ) && BOI_KEEP_DEV_FILES ) {
		return true;
	}

	if ( defined( 'BOI_REMOVE_GIT_DIR' ) && BOI_REMOVE_GIT_DIR ) {
		return false;
	}

	$env = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';

	return in_array( $env, array( 'local', 'development' ), true );
}

/**
 * The value recorded when the cleanup is done: the theme version, plus the
 * BOI_REMOVE_GIT_DIR choice, so changing that constant runs the cleanup
 * again without a new version.
 *
 * @return string
 */
function boi_dev_files_key() {
	if ( ! defined( 'BOI_REMOVE_GIT_DIR' ) ) {
		return BOI_VERSION;
	}
	return BOI_VERSION . ( BOI_REMOVE_GIT_DIR ? '-git' : '-keep-git' );
}

/**
 * Removes the development files, once per theme version.
 *
 * @return void
 */
function boi_remove_dev_files() {
	if ( get_option( 'boi_dev_files_removed' ) === boi_dev_files_key() ) {
		return;
	}

	// A development environment: nothing is deleted, and the version is not
	// recorded, so the files go once the site runs as production.
	if ( boi_keep_dev_files() ) {
		return;
	}

	// One cleanup at a time, whichever hook started it (inc/lock.php); a lock
	// not renewed for ten minutes is from a request that died, and is taken over.
	$token = function_exists( 'boi_lock_take' ) ? boi_lock_take( 'boi_dev_files_lock', 10 * MINUTE_IN_SECONDS ) : '';
	if ( '' === $token ) {
		return;
	}
	$GLOBALS['boi_dev_files_token'] = $token; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$GLOBALS['boi_dev_files_lost']  = false; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	$left = array();

	foreach ( boi_dev_paths() as $rel ) {
		$path = BOI_DIR . DIRECTORY_SEPARATOR . ltrim( $rel, '/\\' );

		if ( file_exists( $path ) || is_link( $path ) ) {
			boi_remove_path( $path );
		}
		clearstatcache();
		if ( file_exists( $path ) || is_link( $path ) ) {
			$left[] = $rel;
		}
	}

	// The version is recorded only once every path is gone. A path PHP could
	// not delete (file ownership, say) is tried again an hour later from the
	// front end, and on every admin page load.
	// Lost the lock part-way: record nothing, and leave the lock alone.
	if ( ! empty( $GLOBALS['boi_dev_files_lost'] ) || ! boi_lock_holds( 'boi_dev_files_lock', $token ) ) {
		unset( $GLOBALS['boi_dev_files_token'], $GLOBALS['boi_dev_files_lost'] );
		return;
	}

	if ( $left ) {
		set_transient( 'boi_dev_files_retry', $left, HOUR_IN_SECONDS );
	} else {
		update_option( 'boi_dev_files_removed', boi_dev_files_key(), true ); // Autoloaded: read on every request.
	}

	boi_lock_release( 'boi_dev_files_lock', $token );
	unset( $GLOBALS['boi_dev_files_token'] );
}
add_action( 'after_switch_theme', 'boi_remove_dev_files' );
add_action( 'admin_init', 'boi_remove_dev_files' );

/**
 * The same, on the first front-end request after an FTP update: such an
 * update fires no switch hook, and the site may see no admin visit for
 * days. boi_remove_dev_files() takes the lock that keeps two requests from
 * deleting at once.
 *
 * @return void
 */
function boi_remove_dev_files_front() {
	if ( is_admin() || get_option( 'boi_dev_files_removed' ) === boi_dev_files_key() || boi_keep_dev_files() || get_transient( 'boi_dev_files_retry' ) ) {
		return;
	}
	boi_remove_dev_files();
}
add_action( 'init', 'boi_remove_dev_files_front', 99 );
