<?php
/**
 * Contact form.
 *
 * A native form with no plugin: nonce, honeypot, per-address rate limit,
 * and delivery through wp_mail() to the address set in the theme options or,
 * failing that, the site administrator.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the contact form block.
 *
 * @return void
 */
function boi_register_contact_block() {
	register_block_type(
		'bestofislam/contact-form',
		array( 'render_callback' => 'boi_render_contact_form' )
	);
}
add_action( 'init', 'boi_register_contact_block' );

/**
 * Address messages are delivered to.
 *
 * @return string
 */
function boi_contact_recipient() {
	$options = boi_get_options();

	if ( ! empty( $options['contact_email'] ) && is_email( $options['contact_email'] ) ) {
		return $options['contact_email'];
	}

	return get_option( 'admin_email' );
}

/**
 * Renders the form, with a status message when returning from a submission.
 *
 * @return string
 */
function boi_render_contact_form() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$status = isset( $_GET['boi_contact'] ) ? sanitize_key( wp_unslash( $_GET['boi_contact'] ) ) : '';
	$notice = '';

	if ( 'sent' === $status ) {
		$notice = '<p class="boi-contact__notice is-success" role="status">' . esc_html__( 'Thank you. Your message has been sent.', 'bestofislam' ) . '</p>';
	} elseif ( 'error' === $status ) {
		$notice = '<p class="boi-contact__notice is-error" role="alert">' . esc_html__( 'The message could not be sent. Check the fields and try again.', 'bestofislam' ) . '</p>';
	} elseif ( 'limit' === $status ) {
		$notice = '<p class="boi-contact__notice is-error" role="alert">' . esc_html__( 'Too many messages in a short period. Please wait a while.', 'bestofislam' ) . '</p>';
	}

	ob_start();
	?>
	<div class="boi-contact">
		<?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<form class="boi-contact__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="boi_contact" />
			<input type="hidden" name="boi_return" value="<?php echo esc_url( get_permalink() ); ?>" />
			<?php wp_nonce_field( 'boi_contact', 'boi_contact_nonce' ); ?>

			<p class="boi-contact__trap" aria-hidden="true">
				<label for="boi-website"><?php esc_html_e( 'Leave this field empty', 'bestofislam' ); ?></label>
				<input type="text" id="boi-website" name="website" tabindex="-1" autocomplete="off" />
			</p>

			<p class="boi-contact__field">
				<label for="boi-name"><?php esc_html_e( 'Name', 'bestofislam' ); ?></label>
				<input type="text" id="boi-name" name="name" required maxlength="120" autocomplete="name" />
			</p>

			<p class="boi-contact__field">
				<label for="boi-email"><?php esc_html_e( 'Email', 'bestofislam' ); ?></label>
				<input type="email" id="boi-email" name="email" required maxlength="200" autocomplete="email" />
			</p>

			<p class="boi-contact__field">
				<label for="boi-subject"><?php esc_html_e( 'Subject', 'bestofislam' ); ?></label>
				<input type="text" id="boi-subject" name="subject" required maxlength="200" />
			</p>

			<p class="boi-contact__field">
				<label for="boi-message"><?php esc_html_e( 'Message', 'bestofislam' ); ?></label>
				<textarea id="boi-message" name="message" required rows="8" maxlength="5000"></textarea>
			</p>

			<p class="boi-contact__submit">
				<button type="submit" class="boi-contact__button"><?php esc_html_e( 'Send message', 'bestofislam' ); ?></button>
			</p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Handles a submission.
 *
 * @return void
 */
function boi_handle_contact() {
	$return = isset( $_POST['boi_return'] ) ? esc_url_raw( wp_unslash( $_POST['boi_return'] ) ) : home_url( '/' );

	if ( ! wp_http_validate_url( $return ) || 0 !== strpos( $return, home_url() ) ) {
		$return = home_url( '/' );
	}

	$redirect = function ( $status ) use ( $return ) {
		wp_safe_redirect( add_query_arg( 'boi_contact', $status, $return ) . '#boi-contact' );
		exit;
	};

	if ( ! isset( $_POST['boi_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['boi_contact_nonce'] ) ), 'boi_contact' ) ) {
		$redirect( 'error' );
	}

	// Honeypot: a real browser leaves it empty.
	if ( ! empty( $_POST['website'] ) ) {
		$redirect( 'sent' );
	}

	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key  = 'boi_contact_' . md5( $ip );
	$hits = (int) get_transient( $key );

	if ( $hits >= 3 ) {
		$redirect( 'limit' );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( '' === $name || ! is_email( $email ) || '' === $subject || '' === $message ) {
		$redirect( 'error' );
	}

	$body = sprintf(
		"%s\n\n%s\n\n---\n%s: %s\n%s: %s\n%s: %s",
		$message,
		'',
		__( 'From', 'bestofislam' ),
		$name,
		__( 'Email', 'bestofislam' ),
		$email,
		__( 'Sent from', 'bestofislam' ),
		$return
	);

	$headers = array(
		'Reply-To: ' . $name . ' <' . $email . '>',
	);

	$sent = wp_mail(
		boi_contact_recipient(),
		sprintf( '[%s] %s', get_bloginfo( 'name' ), $subject ),
		$body,
		$headers
	);

	set_transient( $key, $hits + 1, HOUR_IN_SECONDS );

	$redirect( $sent ? 'sent' : 'error' );
}
add_action( 'admin_post_nopriv_boi_contact', 'boi_handle_contact' );
add_action( 'admin_post_boi_contact', 'boi_handle_contact' );
