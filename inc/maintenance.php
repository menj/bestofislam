<?php
/**
 * Guard against a half-uploaded theme.
 *
 * The owner uploads the theme by FTP one file at a time, and an upload can
 * take twenty minutes. Until 1.89.1 functions.php required inc/util.php and
 * the rest directly, so every request in that window died with "Failed
 * opening required" (27 and 29 October, 1 and 2 November 2026). Now
 * functions.php hands this guard the list of files it loads first. When one
 * is missing, nothing else loads: visitors get a short 503 page with
 * Retry-After and noindex, and the admin screens stay usable and name the
 * missing files.
 *
 * This file needs no other theme file and no theme constant, so it works
 * whatever else has or has not arrived.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The files from the list that cannot be read.
 *
 * @param string[] $files Absolute paths.
 * @return string[]
 */
function boi_maintenance_missing( $files ) {
	$missing = array();

	foreach ( (array) $files as $file ) {
		if ( ! is_readable( $file ) ) {
			$missing[] = $file;
		}
	}

	return $missing;
}

/**
 * The 503 page, a complete HTML document with its own styles.
 *
 * @param string $site Site name.
 * @return string
 */
function boi_maintenance_document( $site ) {
	$site = htmlspecialchars( (string) $site, ENT_QUOTES, 'UTF-8' );

	return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Updating | ' . $site . '</title>
<style>
:root{--bg:#f7f5f0;--ink:#1d2430;--muted:#5b6270;--accent:#0f6b4f}
@media (prefers-color-scheme:dark){:root{--bg:#11151b;--ink:#e9e6df;--muted:#a6abb4;--accent:#4fb38d}}
html,body{height:100%;margin:0}
body{display:flex;align-items:center;justify-content:center;padding:24px;box-sizing:border-box;background:var(--bg);color:var(--ink);font-family:Georgia,"Times New Roman",serif;line-height:1.5;text-align:center}
main{max-width:34rem}
h1{margin:0 0 .5em;font-size:1.8rem}
p{margin:0 0 1em;color:var(--muted)}
a{color:var(--accent)}
</style>
</head>
<body>
<main>
<h1>Updating, back in a minute</h1>
<p>' . $site . ' is being updated. Please try again shortly.</p>
<p><a href="/">Try again</a></p>
</main>
</body>
</html>';
}

/**
 * Sends the 503 page and stops.
 *
 * @return void
 */
function boi_maintenance_send() {
	if ( ! headers_sent() ) {
		header( 'HTTP/1.1 503 Service Unavailable', true, 503 );
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'Retry-After: 120' );
		header( 'Cache-Control: no-store, max-age=0' );
		header( 'X-Robots-Tag: noindex' );
	}

	echo boi_maintenance_document( function_exists( 'get_bloginfo' ) ? get_bloginfo( 'name' ) : 'Best of Islam' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

/**
 * Whether this request should be left to run with the theme unloaded: the
 * admin screens, AJAX, cron, WP-CLI and the login page, so an administrator
 * can still sign in and see what is missing.
 *
 * @return bool
 */
function boi_maintenance_exempt() {
	if ( ( function_exists( 'is_admin' ) && is_admin() ) || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) ) {
		return true;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	return false !== strpos( $uri, 'wp-login' ) || false !== strpos( (string) ( isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '' ), 'wp-login' );
}

/**
 * Checks the theme's files. All present: true, and functions.php goes on.
 * Any missing: visitors get the 503 page; exempt requests get false (the
 * caller then loads nothing more) and administrators a notice naming the
 * files.
 *
 * @param string[] $files Absolute paths the theme loads.
 * @param string   $base  Theme directory, cut from the names in the notice.
 * @return bool
 */
function boi_maintenance_guard( $files, $base = '' ) {
	$missing = boi_maintenance_missing( $files );

	if ( ! $missing ) {
		return true;
	}

	if ( ! boi_maintenance_exempt() ) {
		boi_maintenance_send();
	}

	if ( function_exists( 'add_action' ) ) {
		$names = array();
		foreach ( $missing as $file ) {
			$names[] = ( '' !== $base && 0 === strpos( $file, $base ) ) ? ltrim( substr( $file, strlen( $base ) ), '/' ) : $file;
		}

		add_action(
			'admin_notices',
			function () use ( $names ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				echo '<div class="notice notice-error"><p><strong>Best of Islam is waiting for theme files.</strong> Visitors see an "updating" page until these arrive:</p><ul style="list-style:disc;padding-left:2em">';
				foreach ( $names as $name ) {
					echo '<li><code>' . htmlspecialchars( $name, ENT_QUOTES, 'UTF-8' ) . '</code></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '</ul></div>';
			}
		);
	}

	return false;
}
