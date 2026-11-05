<?php
/**
 * Branded error pages and failure alerts.
 *
 * WordPress shows three drop-ins from wp-content when the site cannot render
 * itself: maintenance.php (updates), db-error.php (database connection lost)
 * and php-error.php (fatal PHP error). They run before any theme loads, so the
 * theme generates self-contained copies, with the stylesheet inlined from
 * assets/error-pages/, and writes them to wp-content. Only files carrying the
 * theme's marker are ever removed; a copy another theme left behind, marked
 * "Written by the ... theme; safe to delete", is replaced on install, and a
 * file with no such mark is never touched.
 *
 * Alerts are emailed to the configured address, rate limited by a stamp file
 * so a failing site cannot flood an inbox. The triggers follow the site's own
 * logs: the September 2026 outage was a run of HTTP 500 responses from the
 * front page and login (13 to 17 September) and a missing core table
 * (18 September), so the theme also reports those two conditions while the
 * site is still able to run.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

const BOI_ERR_MARKER = 'Best of Islam error drop-in';

/**
 * The drop-ins: file name, HTTP status and whether a failure is alertable.
 *
 * @return array
 */
function boi_error_kinds() {
	return array(
		'maintenance' => array(
			'file'  => 'maintenance.php',
			'alert' => false,
		),
		'db-error'    => array(
			'file'  => 'db-error.php',
			'alert' => true,
		),
		'php-error'   => array(
			'file'  => 'php-error.php',
			'alert' => true,
		),
	);
}

/**
 * Copy for each page.
 *
 * @param string $kind Drop-in kind.
 * @return array
 */
function boi_error_copy( $kind ) {
	$again = __( 'Try again', 'bestofislam' );
	$copy  = array(
		'maintenance' => array(
			'title'   => __( 'Back shortly', 'bestofislam' ),
			'heading' => __( 'Back shortly', 'bestofislam' ),
			'line1'   => __( 'The site is briefly offline while it is being updated.', 'bestofislam' ),
			'line2'   => __( 'Please try again in a few minutes.', 'bestofislam' ),
			'status'  => 503,
		),
		'db-error'    => array(
			'title'   => __( 'We cannot reach our database', 'bestofislam' ),
			'heading' => __( 'We cannot reach our database', 'bestofislam' ),
			'line1'   => __( 'This is temporary, and nothing you did caused it.', 'bestofislam' ),
			'line2'   => __( 'Please try again in a few minutes.', 'bestofislam' ),
			'status'  => 503,
		),
		'php-error'   => array(
			'title'   => __( 'Something went wrong', 'bestofislam' ),
			'heading' => __( 'Something went wrong', 'bestofislam' ),
			'line1'   => __( 'An error stopped this page from loading.', 'bestofislam' ),
			'line2'   => __( 'Please try again shortly.', 'bestofislam' ),
			'status'  => 500,
		),
	);

	$out          = $copy[ $kind ];
	$out['again'] = $again;
	return $out;
}

/**
 * Builds the source of one drop-in.
 *
 * @param string $kind Drop-in kind.
 * @return string PHP source.
 */
function boi_error_dropin_source( $kind ) {
	$kinds = boi_error_kinds();
	$copy  = boi_error_copy( $kind );
	$opts  = boi_get_options();
	$name  = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( get_bloginfo( 'name' ) ) ) );
	$css   = (string) file_get_contents( BOI_DIR . '/assets/error-pages/page.css' );
	$html  = (string) file_get_contents( BOI_DIR . '/assets/error-pages/page.html' );

	$html = strtr(
		$html,
		array(
			'{{LANG}}'    => esc_attr( get_bloginfo( 'language' ) ),
			'{{TITLE}}'   => esc_html( $copy['title'] ),
			'{{NAME}}'    => esc_html( $name ),
			'{{CSS}}'     => str_replace( '</', '<\/', $css ),
			'{{HEADING}}' => esc_html( $copy['heading'] ),
			'{{LINE1}}'   => esc_html( $copy['line1'] ),
			'{{LINE2}}'   => esc_html( $copy['line2'] ),
			'{{AGAIN}}'   => esc_html( $copy['again'] ),
			'{{HOME}}'    => esc_url( home_url( '/' ) ),
		)
	);

	$src  = "<?php\n// " . BOI_ERR_MARKER . ' ' . BOI_VERSION . " ({$kind}). Written by the Best of Islam theme; safe to delete.\n";
	// WordPress includes these files with ABSPATH defined. A direct request
	// has neither, so it gets nothing: it can neither read the page nor
	// trigger the alert mail.
	$src .= "if ( ! defined( 'ABSPATH' ) ) {\n\theader( 'HTTP/1.1 403 Forbidden', true, 403 );\n\texit;\n}\n";
	$src .= "if ( ! headers_sent() ) {\n";
	$reason = 503 === (int) $copy['status'] ? 'Service Unavailable' : 'Internal Server Error';
	$src   .= "\theader( 'HTTP/1.1 {$copy['status']} {$reason}', true, {$copy['status']} );\n";
	$src .= "\theader( 'Content-Type: text/html; charset=utf-8' );\n";
	if ( 503 === (int) $copy['status'] ) {
		$src .= "\theader( 'Retry-After: 300' );\n";
	}
	$src .= "\theader( 'X-Robots-Tag: noindex' );\n";
	$src .= "\theader( 'Cache-Control: no-store, max-age=0' );\n}\n";

	$to = ! empty( $opts['errors_alerts'] ) ? boi_error_recipient() : '';

	if ( $kinds[ $kind ]['alert'] && '' !== $to ) {
		$subject = '[' . $name . '] ' . ( 'db-error' === $kind ? 'Database connection failed' : 'PHP fatal error' );
		$subject = preg_replace( '/[\r\n]+/', ' ', $subject );
		$src    .= "\$boi_to    = " . var_export( $to, true ) . ";\n";
		$src    .= "\$boi_subj  = " . var_export( $subject, true ) . ";\n";
		$src    .= "\$boi_stamp = __DIR__ . '/.boi-alert-{$kind}';\n";
		$src    .= "\$boi_gap   = " . (int) $opts['errors_cooldown'] * 60 . ";\n";
		$src    .= <<<'PHP'
$boi_fh = @fopen( $boi_stamp, 'c' );
if ( $boi_fh && @flock( $boi_fh, LOCK_EX | LOCK_NB ) ) {
	if ( time() - (int) @filemtime( $boi_stamp ) > $boi_gap ) {
		@touch( $boi_stamp );
		$boi_root   = defined( 'ABSPATH' ) ? ABSPATH : '';
		$boi_detail = '';
		if ( isset( $error ) && is_array( $error ) ) {
			$boi_detail = ( isset( $error['message'] ) ? $error['message'] : '' ) . "\n" . ( isset( $error['file'] ) ? $error['file'] : '' ) . ':' . ( isset( $error['line'] ) ? $error['line'] : '' );
			if ( '' !== $boi_root ) {
				$boi_detail = str_replace( $boi_root, '', $boi_detail );
			}
		}
		$boi_host = isset( $_SERVER['HTTP_HOST'] ) ? preg_replace( '/[^A-Za-z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ) : '';
		$boi_uri  = isset( $_SERVER['REQUEST_URI'] ) ? preg_replace( '/[\r\n]+/', '', (string) strtok( $_SERVER['REQUEST_URI'], '?' ) ) : '';
		$boi_body = "The visitor-facing error page was shown.\n\nHost: {$boi_host}\nPath: {$boi_uri}\nTime: " . gmdate( 'c' ) . "\n" . ( '' !== $boi_detail ? "\n{$boi_detail}\n" : '' );
		if ( function_exists( 'wp_mail' ) ) {
			@wp_mail( $boi_to, $boi_subj, $boi_body );
		} else {
			@mail( $boi_to, $boi_subj, $boi_body );
		}
	}
	@flock( $boi_fh, LOCK_UN );
}
if ( $boi_fh ) {
	@fclose( $boi_fh );
}

PHP;
	}

	return $src . "?>\n" . $html;
}

/**
 * The alert recipient: the configured address, else the site admin's.
 *
 * @return string
 */
function boi_error_recipient() {
	$opts = boi_get_options();
	$to   = isset( $opts['errors_email'] ) ? $opts['errors_email'] : '';
	$to   = is_email( $to ) ? $to : get_option( 'admin_email' );

	if ( ! is_email( $to ) ) {
		// The options table may be the thing that is missing: use the copy
		// saved to wp-content when the drop-ins were last written.
		$saved = boi_error_saved_config();
		$to    = isset( $saved['to'] ) ? $saved['to'] : '';
	}

	return is_email( $to ) ? $to : '';
}

/**
 * Path of the alert settings copy kept outside the database.
 *
 * @return string
 */
function boi_error_config_path() {
	return WP_CONTENT_DIR . '/.boi-alert-config.php';
}

/**
 * Reads the saved alert settings.
 *
 * @return array
 */
function boi_error_saved_config() {
	// A PHP file that returns an array and exits when requested directly, so
	// the recipient is never served as text from a public folder.
	$data = is_readable( boi_error_config_path() ) ? include boi_error_config_path() : array();
	return is_array( $data ) ? $data : array();
}

/**
 * Writes a file atomically, so a request that includes it never reads a
 * half-written copy.
 *
 * @param string $path     Destination.
 * @param string $contents File contents.
 * @return void
 */
function boi_error_write( $path, $contents ) {
	$tmp = $path . '.' . wp_generate_password( 8, false ) . '.tmp';
	// A short write (a full disk) must not be moved into place as a whole page.
	if ( strlen( $contents ) !== @file_put_contents( $tmp, $contents, LOCK_EX ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
		wp_delete_file( $tmp );
		return false;
	}
	if ( ! @rename( $tmp, $path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
		wp_delete_file( $tmp );
		return false;
	}
	return true;
}

/**
 * A fingerprint of everything the generated files embed.
 *
 * @return string
 */
function boi_error_fingerprint() {
	$opts = boi_get_options();
	return md5(
		wp_json_encode(
			array(
				BOI_VERSION,
				get_bloginfo( 'name' ),
				get_bloginfo( 'language' ),
				home_url( '/' ),
				boi_error_recipient(),
				$opts['errors_pages'],
				$opts['errors_alerts'],
				$opts['errors_cooldown'],
			)
		)
	);
}

/**
 * Whether a file is one of ours.
 *
 * @param string $path File path.
 * @return bool
 */
function boi_error_is_ours( $path ) {
	if ( ! is_readable( $path ) ) {
		return false;
	}
	$head = (string) file_get_contents( $path, false, null, 0, 300 );
	return false !== strpos( $head, BOI_ERR_MARKER );
}

/**
 * Whether a file is a drop-in another theme left behind: it carries the
 * "Written by the ... theme; safe to delete" mark and is not ours.
 *
 * @param string $path File path.
 * @return bool
 */
function boi_error_is_other_theme( $path ) {
	if ( ! is_readable( $path ) || boi_error_is_ours( $path ) ) {
		return false;
	}
	$head = (string) file_get_contents( $path, false, null, 0, 400 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	// The mark must sit on a comment line of the file's header.
	return 1 === preg_match( '~^//[^\n]*Written by the [^\n]{1,80} theme; safe to delete~m', $head );
}

/**
 * Status of each drop-in for the settings screen.
 *
 * @return array kind => string state: ours (current), outdated (ours, from
 *               another version or other settings), theme (another theme's
 *               leftover, replaced on install), foreign, absent.
 */
function boi_error_state() {
	$out = array();
	foreach ( boi_error_kinds() as $kind => $def ) {
		$path = WP_CONTENT_DIR . '/' . $def['file'];
		if ( ! file_exists( $path ) ) {
			$out[ $kind ] = 'absent';
		} elseif ( boi_error_is_other_theme( $path ) ) {
			$out[ $kind ] = 'theme';
		} elseif ( ! boi_error_is_ours( $path ) ) {
			$out[ $kind ] = 'foreign';
		} else {
			$out[ $kind ] = (string) file_get_contents( $path ) === boi_error_dropin_source( $kind ) ? 'ours' : 'outdated'; // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
	}
	return $out;
}

/**
 * "Install error pages" on the Error pages tab: a nonce link, since the tab
 * sits inside the settings form. Writes the pages at once.
 */
function boi_error_handle_install() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'bestofislam' ), 403 );
	}
	check_admin_referer( 'boi_install_error_pages' );
	if ( is_multisite() && ! is_main_site() ) {
		wp_safe_redirect( admin_url( 'themes.php?page=boi-settings&tab=errors' ) );
		exit;
	}
	if ( boi_error_sync() ) {
		wp_safe_redirect( admin_url( 'themes.php?page=boi-settings&tab=errors&boi_errors_installed=1' ) );
		exit;
	}
	$failed = implode( ',', boi_error_sync_failures() );
	wp_safe_redirect( admin_url( 'themes.php?page=boi-settings&tab=errors&boi_errors_failed=' . rawurlencode( $failed ) ) );
	exit;
}
add_action( 'admin_post_boi_install_error_pages', 'boi_error_handle_install' );

/**
 * The lines for .htaccess that point the web server at the static pages in
 * errors/ (built by bin/build-error-pages.php).
 *
 * @return string
 */
function boi_error_htaccess_snippet() {
	$base  = untrailingslashit( (string) wp_parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) ) . '/errors/';
	$lines = array( '# Best of Islam: pages for the errors the web server answers before WordPress runs' );
	foreach ( array( 403, 500, 502, 503, 504 ) as $code ) {
		$lines[] = 'ErrorDocument ' . $code . ' ' . $base . $code . '.html';
	}
	return implode( "\n", $lines );
}

/**
 * The file names the last boi_error_sync() could not write.
 *
 * @param string[]|null $set Replaces the list when given.
 * @return string[]
 */
function boi_error_sync_failures( $set = null ) {
	static $failed = array();
	if ( null !== $set ) {
		$failed = $set;
	}
	return $failed;
}

/**
 * Writes or removes the drop-ins to match the settings. Another theme's
 * marked leftover is replaced; a file with no theme's mark is never touched,
 * and only our own files are removed.
 *
 * @return bool True when every file it meant to write was written.
 */
function boi_error_sync() {
	$opts   = boi_get_options();
	$failed = array();

	foreach ( boi_error_kinds() as $kind => $def ) {
		$path = WP_CONTENT_DIR . '/' . $def['file'];

		// A database or PHP failure happens before the theme loads, so the
		// alert can only be sent from its drop-in. Those two stay installed
		// while either the pages or the alerts are on.
		$enabled = ! empty( $opts['errors_pages'] ) || ( $def['alert'] && ! empty( $opts['errors_alerts'] ) );

		if ( file_exists( $path ) && ! boi_error_is_ours( $path ) && ! ( $enabled && boi_error_is_other_theme( $path ) ) ) {
			continue;
		}

		if ( $enabled ) {
			if ( ! ( is_writable( WP_CONTENT_DIR ) || is_writable( $path ) ) || ! boi_error_write( $path, boi_error_dropin_source( $kind ) ) ) {
				$failed[] = $def['file'];
			}
		} elseif ( file_exists( $path ) ) {
			wp_delete_file( $path );
		}
	}

	$config = "<?php\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\nreturn " . var_export(
		array(
			'to'       => boi_error_recipient(),
			'alerts'   => (int) $opts['errors_alerts'],
			'cooldown' => (int) $opts['errors_cooldown'],
		),
		true
	) . ";\n";
	if ( ! is_writable( WP_CONTENT_DIR ) || ! boi_error_write( boi_error_config_path(), $config ) ) {
		$failed[] = basename( boi_error_config_path() );
	}

	// An earlier version kept these settings in a readable JSON file.
	if ( file_exists( WP_CONTENT_DIR . '/.boi-alert-config.json' ) ) {
		wp_delete_file( WP_CONTENT_DIR . '/.boi-alert-config.json' );
	}

	update_option( 'boi_error_dropins_fingerprint', boi_error_fingerprint(), false );
	boi_error_sync_failures( $failed );
	return array() === $failed;
}

/**
 * Keeps the drop-ins current: after an update, after a wp-content upload that
 * removed them, and when the settings or admin email change.
 *
 * @return void
 */
function boi_error_maybe_sync() {
	$opts = boi_get_options();

	if ( empty( $opts['errors_pages'] ) && empty( $opts['errors_alerts'] ) ) {
		return;
	}

	$state = boi_error_state();
	if ( get_option( 'boi_error_dropins_fingerprint' ) === boi_error_fingerprint() && ! array_intersect( array( 'absent', 'theme', 'outdated' ), $state ) ) {
		return;
	}

	boi_error_sync();
}
add_action( 'admin_init', 'boi_error_maybe_sync' );
add_action( 'update_option_' . BOI_OPTION_KEY, 'boi_error_sync' );
add_action( 'update_option_admin_email', 'boi_error_sync' );
add_action( 'after_switch_theme', 'boi_error_sync' );

/**
 * Removes this theme's own drop-ins when another theme is activated. Only
 * files carrying the theme's marker are deleted. On a network the files are
 * left in place: wp-content is shared, and another site may still use this
 * theme.
 *
 * @return void
 */
function boi_error_remove() {
	if ( is_multisite() ) {
		return;
	}
	foreach ( boi_error_kinds() as $def ) {
		$path = WP_CONTENT_DIR . '/' . $def['file'];
		if ( boi_error_is_ours( $path ) ) {
			wp_delete_file( $path );
		}
	}
	delete_option( 'boi_error_dropins_fingerprint' );
}
add_action( 'switch_theme', 'boi_error_remove' );

/**
 * Sends an alert from a running site, rate limited.
 *
 * @param string $key     Condition key.
 * @param string $subject Subject.
 * @param string $detail  Body detail.
 * @return void
 */
function boi_error_alert( $key, $subject, $detail ) {
	$opts = boi_get_options();
	$to   = boi_error_recipient();

	// With the options table unreadable the defaults would apply, so honour
	// the settings last saved to wp-content instead.
	if ( false === get_option( BOI_OPTION_KEY, false ) ) {
		$saved                   = boi_error_saved_config();
		$opts['errors_alerts']   = isset( $saved['alerts'] ) ? $saved['alerts'] : $opts['errors_alerts'];
		$opts['errors_cooldown'] = isset( $saved['cooldown'] ) ? $saved['cooldown'] : $opts['errors_cooldown'];
	}

	if ( empty( $opts['errors_alerts'] ) || '' === $to ) {
		return;
	}
	$stamp = WP_CONTENT_DIR . '/.boi-alert-' . sanitize_key( $key );
	$fh    = @fopen( $stamp, 'c' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	if ( ! $fh ) {
		return;
	}

	// An exclusive, non-blocking lock lets one of several concurrent requests
	// send the alert; the rest return.
	if ( flock( $fh, LOCK_EX | LOCK_NB ) ) {
		if ( time() - (int) filemtime( $stamp ) > max( 5, (int) $opts['errors_cooldown'] ) * MINUTE_IN_SECONDS ) {
			touch( $stamp );

			$name = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( get_bloginfo( 'name' ) ) ) );
			// The path only: a query string can carry reset or login tokens.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$path = isset( $_SERVER['REQUEST_URI'] ) ? preg_replace( '/[\r\n]+/', '', (string) strtok( wp_unslash( $_SERVER['REQUEST_URI'] ), '?' ) ) : '';
			wp_mail(
				$to,
				'[' . $name . '] ' . $subject,
				str_replace( ABSPATH, '', $detail ) . "\n\nPath: " . $path . "\nTime: " . gmdate( 'c' ) . "\n"
			);
		}
		flock( $fh, LOCK_UN );
	}
	fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/**
 * The PHP error levels that end the request: a fatal of one kind or
 * another, the only ones error_get_last() can still report by the time
 * shutdown runs.
 *
 * @return int[]
 */
function boi_fatal_error_levels() {
	return array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR );
}

/**
 * Reports, at the end of a request, the conditions the logs show.
 *
 * - A core table that does not exist (18 September: Table '...options'
 *   doesn't exist).
 * - A request that is answered with HTTP 500 (13 to 17 September).
 * - An uncaught fatal error, checked directly with error_get_last() rather
 *   than relying on the response status alone: an xmlrpc.php fatal on
 *   20 October (Class "WPS_Guard" not found) reached this same shutdown
 *   hook but the server had already sent a 200 status for the request, so
 *   the HTTP-500 check above would have missed it.
 *
 * @return void
 */
function boi_error_watch() {
	global $wpdb;

	if ( wp_doing_cron() ) {
		return;
	}

	if ( ! empty( $wpdb->last_error ) && preg_match( "/Table '[^']+' doesn't exist/", $wpdb->last_error ) ) {
		boi_error_alert( 'missing-table', 'A database table is missing', substr( $wpdb->last_error, 0, 500 ) );
		return;
	}

	$last = error_get_last();

	if ( is_array( $last ) && in_array( $last['type'], boi_fatal_error_levels(), true ) ) {
		$detail = ( isset( $last['message'] ) ? $last['message'] : '' ) . "\n" . ( isset( $last['file'] ) ? $last['file'] : '' ) . ':' . ( isset( $last['line'] ) ? $last['line'] : '' );
		boi_error_alert( 'fatal-error', 'A PHP fatal error occurred', $detail );
		return;
	}

	if ( 500 === http_response_code() ) {
		boi_error_alert( 'http-500', 'The site answered with HTTP 500', 'A request was answered with a 500 status.' );
	}
}
add_action( 'shutdown', 'boi_error_watch', 0 );

/**
 * Errors tab fields.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_errors_tab( $opts ) {
	$key   = BOI_OPTION_KEY;
	$state = boi_error_state();
	$label = array(
		'ours'     => __( 'Installed, current', 'bestofislam' ),
		'outdated' => __( 'Installed by the theme, outdated', 'bestofislam' ),
		'theme'    => __( 'Left behind by another theme; replaced on the next install', 'bestofislam' ),
		'foreign' => __( 'Another file is present and has been left untouched', 'bestofislam' ),
		'absent'  => __( 'Not installed', 'bestofislam' ),
	);

	boi_opt_card_open( __( 'Error pages', 'bestofislam' ) );
	boi_opt_toggle(
		array(
			'key'     => 'errors_pages',
			'label'   => __( 'Error pages', 'bestofislam' ),
			'text'    => __( 'Show branded maintenance, database and PHP error pages', 'bestofislam' ),
			'help'    => __( 'Written to wp-content. If a folder upload removes them, they are restored on the next admin page load.', 'bestofislam' ),
			'checked' => ! empty( $opts['errors_pages'] ),
		)
	);
	boi_opt_row_open( __( 'Files', 'bestofislam' ) );
	echo '<ul class="boi-checklist">';
	foreach ( boi_error_kinds() as $kind => $def ) {
		echo '<li><code>' . esc_html( $def['file'] ) . '</code>: ' . esc_html( $label[ $state[ $kind ] ] ) . '</li>';
	}
	echo '</ul>';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	if ( isset( $_GET['boi_errors_installed'] ) ) {
		echo '<p class="notice notice-success inline">' . esc_html__( 'The error pages were written where the theme may write them.', 'bestofislam' ) . '</p>';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	if ( isset( $_GET['boi_errors_failed'] ) ) {
		$known = array_merge( wp_list_pluck( boi_error_kinds(), 'file' ), array( basename( boi_error_config_path() ) ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only, and only known names are shown.
		$names = array_values( array_intersect( $known, explode( ',', sanitize_text_field( wp_unslash( $_GET['boi_errors_failed'] ) ) ) ) );
		echo '<p class="notice notice-error inline">' . esc_html(
			sprintf(
				/* translators: %s: comma-separated file names */
				__( 'These files could not be written to wp-content: %s. Check that the web server may write there.', 'bestofislam' ),
				$names ? implode( ', ', $names ) : __( 'unknown', 'bestofislam' )
			)
		) . '</p>';
	}
	if ( is_multisite() && ! is_main_site() ) {
		echo '<p class="description">' . esc_html__( 'The whole network shares wp-content, so the error pages are installed from the main site.', 'bestofislam' ) . '</p>';
	} else {
		printf(
			'<p><a class="button" href="%s">%s</a></p>',
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=boi_install_error_pages' ), 'boi_install_error_pages' ) ),
			esc_html__( 'Install error pages', 'bestofislam' )
		);
	}
	boi_opt_row_close();
	boi_opt_row_open( __( 'Server errors', 'bestofislam' ), 'boi-errors-htaccess' );
	echo '<p class="description">' . esc_html__( 'For the errors the web server answers before WordPress starts, static pages are in the errors folder of the theme. Add these lines to .htaccess to use them:', 'bestofislam' ) . '</p>';
	echo '<textarea id="boi-errors-htaccess" readonly rows="6" class="large-text code">' . esc_textarea( boi_error_htaccess_snippet() ) . '</textarea>';
	boi_opt_row_close();
	boi_opt_card_close();

	boi_opt_card_open( __( 'Alerts', 'bestofislam' ) );
	boi_opt_toggle(
		array(
			'key'     => 'errors_alerts',
			'label'   => __( 'Alerts', 'bestofislam' ),
			'text'    => __( 'Email when the database fails, a PHP fatal occurs, a core table is missing or a page answers with HTTP 500', 'bestofislam' ),
			'help'    => __( 'Database and PHP failures are reported by their error pages, which stay installed while alerts are on.', 'bestofislam' ),
			'checked' => ! empty( $opts['errors_alerts'] ),
		)
	);
	boi_opt_row_open( __( 'Send alerts to', 'bestofislam' ), 'boi-errors-email' );
	?>
	<input type="email" id="boi-errors-email" class="regular-text" name="<?php echo esc_attr( $key ); ?>[errors_email]" value="<?php echo esc_attr( $opts['errors_email'] ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" aria-describedby="boi-errors-email-help" />
	<p class="description" id="boi-errors-email-help"><?php esc_html_e( 'Leave empty to use the site admin email.', 'bestofislam' ); ?></p>
	<?php
	boi_opt_row_close();
	boi_opt_range(
		array(
			'key'   => 'errors_cooldown',
			'label' => __( 'Minutes between alerts', 'bestofislam' ),
			'value' => (int) $opts['errors_cooldown'],
			'min'   => 5,
			'max'   => 1440,
			'unit'  => __( 'minutes', 'bestofislam' ),
			'help'  => __( 'One alert per kind of failure in this window, so a long outage sends one email, not hundreds.', 'bestofislam' ),
		)
	);
	boi_opt_card_close();
}
