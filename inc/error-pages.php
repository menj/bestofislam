<?php
/**
 * Branded error pages and failure alerts.
 *
 * WordPress shows three drop-ins from wp-content when the site cannot render
 * itself: maintenance.php (updates), db-error.php (database connection lost)
 * and php-error.php (fatal PHP error). They run before any theme loads, so the
 * theme generates self-contained copies, with the stylesheet inlined from
 * assets/error-pages/, and writes them to wp-content. Only files carrying the
 * theme's marker are ever replaced or removed.
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
			'title'   => __( 'Temporarily unavailable', 'bestofislam' ),
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
		)
	);

	$src  = "<?php\n// " . BOI_ERR_MARKER . ' ' . BOI_VERSION . " ({$kind}). Written by the Best of Islam theme; safe to delete.\n";
	$src .= "if ( ! headers_sent() ) {\n";
	$src .= "\theader( 'HTTP/1.1 {$copy['status']}', true, {$copy['status']} );\n";
	$src .= "\theader( 'Content-Type: text/html; charset=utf-8' );\n";
	$src .= "\theader( 'Retry-After: 300' );\n";
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
if ( ! file_exists( $boi_stamp ) || time() - (int) @filemtime( $boi_stamp ) > $boi_gap ) {
	@touch( $boi_stamp );
	$boi_detail = '';
	if ( isset( $error ) && is_array( $error ) ) {
		$boi_detail = ( isset( $error['message'] ) ? $error['message'] : '' ) . "\n" . ( isset( $error['file'] ) ? $error['file'] : '' ) . ':' . ( isset( $error['line'] ) ? $error['line'] : '' );
	}
	$boi_host = isset( $_SERVER['HTTP_HOST'] ) ? preg_replace( '/[^A-Za-z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ) : '';
	$boi_uri  = isset( $_SERVER['REQUEST_URI'] ) ? preg_replace( '/[\r\n]+/', '', $_SERVER['REQUEST_URI'] ) : '';
	$boi_body = "The visitor-facing error page was shown.\n\nHost: {$boi_host}\nRequest: {$boi_uri}\nTime: " . gmdate( 'c' ) . "\n" . ( '' !== $boi_detail ? "\n{$boi_detail}\n" : '' );
	if ( function_exists( 'wp_mail' ) ) {
		@wp_mail( $boi_to, $boi_subj, $boi_body );
	} else {
		@mail( $boi_to, $boi_subj, $boi_body );
	}
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
	return is_email( $to ) ? $to : '';
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
 * Status of each drop-in for the settings screen.
 *
 * @return array kind => string state: ours, foreign, absent.
 */
function boi_error_state() {
	$out = array();
	foreach ( boi_error_kinds() as $kind => $def ) {
		$path        = WP_CONTENT_DIR . '/' . $def['file'];
		$out[ $kind ] = ! file_exists( $path ) ? 'absent' : ( boi_error_is_ours( $path ) ? 'ours' : 'foreign' );
	}
	return $out;
}

/**
 * Writes or removes the drop-ins to match the settings. Files that are not
 * ours are never touched.
 *
 * @return void
 */
function boi_error_sync() {
	$opts = boi_get_options();

	foreach ( boi_error_kinds() as $kind => $def ) {
		$path = WP_CONTENT_DIR . '/' . $def['file'];

		// A database or PHP failure happens before the theme loads, so the
		// alert can only be sent from its drop-in. Those two stay installed
		// while either the pages or the alerts are on.
		$enabled = ! empty( $opts['errors_pages'] ) || ( $def['alert'] && ! empty( $opts['errors_alerts'] ) );

		if ( file_exists( $path ) && ! boi_error_is_ours( $path ) ) {
			continue;
		}

		if ( $enabled ) {
			if ( is_writable( WP_CONTENT_DIR ) || is_writable( $path ) ) {
				file_put_contents( $path, boi_error_dropin_source( $kind ), LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			}
		} elseif ( file_exists( $path ) ) {
			wp_delete_file( $path );
		}
	}

	update_option( 'boi_error_dropins_version', BOI_VERSION, false );
}

/**
 * Keeps the drop-ins current: after an update, after a wp-content upload that
 * removed them, and when the settings or admin email change.
 *
 * @return void
 */
function boi_error_maybe_sync() {
	if ( get_option( 'boi_error_dropins_version' ) === BOI_VERSION ) {
		$state = boi_error_state();
		$opts  = boi_get_options();
		if ( ( empty( $opts['errors_pages'] ) && empty( $opts['errors_alerts'] ) ) || ! in_array( 'absent', $state, true ) ) {
			return;
		}
	}
	boi_error_sync();
}
add_action( 'admin_init', 'boi_error_maybe_sync' );
add_action( 'update_option_' . BOI_OPTION_KEY, 'boi_error_sync' );
add_action( 'update_option_admin_email', 'boi_error_sync' );
add_action( 'after_switch_theme', 'boi_error_sync' );

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
	if ( empty( $opts['errors_alerts'] ) || '' === $to ) {
		return;
	}
	$transient = 'boi_err_' . md5( $key );
	if ( get_transient( $transient ) ) {
		return;
	}
	set_transient( $transient, 1, max( 1, (int) $opts['errors_cooldown'] ) * MINUTE_IN_SECONDS );

	$name = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( get_bloginfo( 'name' ) ) ) );
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? preg_replace( '/[\r\n]+/', '', wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	wp_mail(
		$to,
		'[' . $name . '] ' . $subject,
		$detail . "\n\nRequest: " . $uri . "\nTime: " . gmdate( 'c' ) . "\n"
	);
}

/**
 * Reports, at the end of a request, the two conditions the logs show.
 *
 * - A core table that does not exist (18 September: Table '...options'
 *   doesn't exist).
 * - A request that is answered with HTTP 500 (13 to 17 September).
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
		'ours'    => __( 'Installed', 'bestofislam' ),
		'foreign' => __( 'Another file is present and has been left untouched', 'bestofislam' ),
		'absent'  => __( 'Not installed', 'bestofislam' ),
	);
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Error pages', 'bestofislam' ); ?></th>
			<td>
				<label><input type="checkbox" name="<?php echo esc_attr( $key ); ?>[errors_pages]" value="1" <?php checked( $opts['errors_pages'], 1 ); ?> /> <?php esc_html_e( 'Show branded maintenance, database and PHP error pages', 'bestofislam' ); ?></label>
				<ul>
					<?php foreach ( boi_error_kinds() as $kind => $def ) : ?>
						<li><code><?php echo esc_html( $def['file'] ); ?></code>: <?php echo esc_html( $label[ $state[ $kind ] ] ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p class="description"><?php esc_html_e( 'Written to wp-content. If a folder upload removes them, they are restored on the next admin page load.', 'bestofislam' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Alerts', 'bestofislam' ); ?></th>
			<td>
				<label><input type="checkbox" name="<?php echo esc_attr( $key ); ?>[errors_alerts]" value="1" <?php checked( $opts['errors_alerts'], 1 ); ?> /> <?php esc_html_e( 'Email when the database fails, a PHP fatal occurs, a core table is missing or a page answers with HTTP 500', 'bestofislam' ); ?></label>
				<p class="description"><?php esc_html_e( 'Database and PHP failures are reported by their error pages, which stay installed while alerts are on.', 'bestofislam' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="boi-errors-email"><?php esc_html_e( 'Send alerts to', 'bestofislam' ); ?></label></th>
			<td>
				<input type="email" id="boi-errors-email" class="regular-text" name="<?php echo esc_attr( $key ); ?>[errors_email]" value="<?php echo esc_attr( $opts['errors_email'] ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Leave empty to use the site admin email.', 'bestofislam' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="boi-errors-cooldown"><?php esc_html_e( 'Minutes between alerts', 'bestofislam' ); ?></label></th>
			<td>
				<input type="number" id="boi-errors-cooldown" min="5" max="1440" name="<?php echo esc_attr( $key ); ?>[errors_cooldown]" value="<?php echo esc_attr( $opts['errors_cooldown'] ); ?>" />
				<p class="description"><?php esc_html_e( 'One alert per kind of failure in this window, so a long outage sends one email, not hundreds.', 'bestofislam' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
