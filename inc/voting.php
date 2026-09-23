<?php
/**
 * Entry voting.
 *
 * Votes are stored in a dedicated table rather than post meta, since a
 * popular list would otherwise bloat the meta table and slow every query
 * that touches the post.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the votes table name.
 *
 * @return string
 */
function boi_votes_table() {
	global $wpdb;

	return $wpdb->prefix . 'boi_votes';
}

/**
 * Creates or updates the votes table.
 *
 * @return void
 */
function boi_install_votes_table() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table   = boi_votes_table();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id BIGINT UNSIGNED NOT NULL,
		entry_id VARCHAR(64) NOT NULL,
		voter_hash CHAR(64) NOT NULL,
		vote TINYINT NOT NULL DEFAULT 1,
		created_at DATETIME NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY voter_entry (post_id, entry_id, voter_hash),
		KEY post_entry (post_id, entry_id)
	) {$charset};";

	dbDelta( $sql );
}

/**
 * Whether voting is enabled in theme settings.
 *
 * @return bool
 */
function boi_voting_enabled() {
	$options = boi_get_options();

	return ! empty( $options['voting_enabled'] );
}

/**
 * Whether voting is restricted to logged-in users.
 *
 * @return bool
 */
function boi_voting_requires_login() {
	$options = boi_get_options();

	return ! empty( $options['voting_login_only'] );
}

/**
 * Builds the per-visitor hash used to enforce one vote per entry.
 *
 * @return string
 */
function boi_voter_hash() {
	if ( is_user_logged_in() ) {
		return hash( 'sha256', 'user:' . get_current_user_id() . wp_salt( 'auth' ) );
	}

	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

	return hash( 'sha256', 'anon:' . $ip . '|' . $agent . wp_salt( 'auth' ) );
}

/**
 * Returns the vote tally for one entry.
 *
 * @param int    $post_id  Post identifier.
 * @param string $entry_id Entry identifier.
 * @return int
 */
function boi_get_vote_count( $post_id, $entry_id ) {
	global $wpdb;

	$key    = 'boi_votes_' . $post_id . '_' . md5( $entry_id );
	$cached = get_transient( $key );

	if ( false !== $cached ) {
		return (int) $cached;
	}

	$table = boi_votes_table();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(vote), 0) FROM {$table} WHERE post_id = %d AND entry_id = %s",
			$post_id,
			$entry_id
		)
	);

	set_transient( $key, $count, HOUR_IN_SECONDS );

	return $count;
}

/**
 * Renders the vote control for an entry.
 *
 * @param int    $post_id  Post identifier.
 * @param string $entry_id Entry identifier.
 * @return string
 */
function boi_render_vote_widget( $post_id, $entry_id ) {
	$count = boi_get_vote_count( $post_id, $entry_id );

	return sprintf(
		'<div class="boi-vote" data-entry-id="%1$s">
			<button type="button" class="boi-vote__button" aria-label="%2$s">
				<span class="boi-vote__icon" aria-hidden="true">&#9650;</span>
				<span class="boi-vote__count">%3$s</span>
			</button>
			<p class="boi-vote__message" role="status" aria-live="polite"></p>
		</div>',
		esc_attr( $entry_id ),
		esc_attr__( 'Vote for this entry', 'bestofislam' ),
		esc_html( number_format_i18n( $count ) )
	);
}

/**
 * Registers the voting REST route.
 *
 * @return void
 */
function boi_register_vote_route() {
	register_rest_route(
		'bestofislam/v1',
		'/vote',
		array(
			'methods'             => 'POST',
			'callback'            => 'boi_handle_vote',
			'permission_callback' => 'boi_vote_permission',
			'args'                => array(
				'post_id'  => array(
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
				'entry_id' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'boi_register_vote_route' );

/**
 * Permission check for the voting route.
 *
 * @return true|WP_Error
 */
function boi_vote_permission() {
	if ( ! boi_voting_enabled() ) {
		return new WP_Error( 'boi_voting_disabled', __( 'Voting is disabled.', 'bestofislam' ), array( 'status' => 403 ) );
	}

	if ( boi_voting_requires_login() && ! is_user_logged_in() ) {
		return new WP_Error( 'boi_login_required', __( 'Please log in to vote.', 'bestofislam' ), array( 'status' => 401 ) );
	}

	return true;
}

/**
 * Records a vote.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error
 */
function boi_handle_vote( WP_REST_Request $request ) {
	global $wpdb;

	$post_id  = (int) $request->get_param( 'post_id' );
	$entry_id = (string) $request->get_param( 'entry_id' );

	if ( ! get_post( $post_id ) || '' === $entry_id ) {
		return new WP_Error( 'boi_invalid_target', __( 'Unknown entry.', 'bestofislam' ), array( 'status' => 400 ) );
	}

	$hash = boi_voter_hash();

	if ( boi_is_throttled( $hash ) ) {
		return new WP_Error( 'boi_throttled', __( 'Too many votes in a short period.', 'bestofislam' ), array( 'status' => 429 ) );
	}

	$table = boi_votes_table();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$inserted = $wpdb->query(
		$wpdb->prepare(
			"INSERT IGNORE INTO {$table} (post_id, entry_id, voter_hash, vote, created_at)
			VALUES (%d, %s, %s, 1, %s)",
			$post_id,
			$entry_id,
			$hash,
			current_time( 'mysql', true )
		)
	);

	if ( ! $inserted ) {
		return new WP_Error(
			'boi_duplicate',
			__( 'You have already voted on this entry.', 'bestofislam' ),
			array(
				'status' => 409,
				'count'  => boi_get_vote_count( $post_id, $entry_id ),
			)
		);
	}

	delete_transient( 'boi_votes_' . $post_id . '_' . md5( $entry_id ) );
	boi_record_throttle( $hash );

	return rest_ensure_response(
		array(
			'count' => boi_get_vote_count( $post_id, $entry_id ),
		)
	);
}

/**
 * Whether the voter has exceeded the permitted rate.
 *
 * @param string $hash Voter hash.
 * @return bool
 */
function boi_is_throttled( $hash ) {
	$options = boi_get_options();
	$limit   = max( 1, (int) $options['voting_rate_limit'] );
	$hits    = (int) get_transient( 'boi_rate_' . $hash );

	return $hits >= $limit;
}

/**
 * Increments the voter's rate counter.
 *
 * @param string $hash Voter hash.
 * @return void
 */
function boi_record_throttle( $hash ) {
	$key  = 'boi_rate_' . $hash;
	$hits = (int) get_transient( $key );

	set_transient( $key, $hits + 1, MINUTE_IN_SECONDS * 10 );
}
