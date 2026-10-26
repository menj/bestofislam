<?php
/**
 * Removes development files from the theme directory.
 *
 * A theme installed from a clone or a repository archive carries the
 * workflow definitions, git hooks and editor settings used to develop it.
 * None of them serve a page, and a web-readable .github or .githooks folder
 * is clutter at best. This deletes them on activation and after each
 * version change.
 *
 * The .git directory is left alone unless BOI_REMOVE_GIT_DIR is defined
 * true in wp-config.php: a site deployed by git pull needs it, and deleting
 * it would break that deployment.
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
	$paths = array( '.github', '.githooks', '.claude', '.vscode', '.idea', '.gitattributes', '.gitignore', '.editorconfig', '.DS_Store', 'dist' );

	if ( defined( 'BOI_REMOVE_GIT_DIR' ) && BOI_REMOVE_GIT_DIR ) {
		$paths[] = '.git';
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
 * Removes the development files, once per theme version.
 *
 * @return void
 */
function boi_remove_dev_files() {
	if ( get_option( 'boi_dev_files_removed' ) === BOI_VERSION ) {
		return;
	}

	foreach ( boi_dev_paths() as $rel ) {
		$path = BOI_DIR . DIRECTORY_SEPARATOR . ltrim( $rel, '/\\' );

		if ( file_exists( $path ) || is_link( $path ) ) {
			boi_remove_path( $path );
		}
	}

	update_option( 'boi_dev_files_removed', BOI_VERSION, false );
}
add_action( 'after_switch_theme', 'boi_remove_dev_files' );
add_action( 'admin_init', 'boi_remove_dev_files' );
