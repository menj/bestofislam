<?php
/**
 * Tabbed settings screen.
 *
 * Colour configuration is handled through theme.json custom properties and
 * style variations in the Site Editor, so it is deliberately absent here.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

const BOI_OPTION_KEY = 'boi_settings';

/**
 * Returns settings merged with defaults.
 *
 * @return array
 */
function boi_get_options() {
	$defaults = array(
		'show_rank'           => 1,
		'image_size'          => 'large',
		'default_schema'      => 'ItemList',
		'voting_enabled'      => 0,
		'voting_login_only'   => 0,
		'voting_rate_limit'   => 10,
		'seo_description'     => 1,
		'follow_youtube'      => '',
		'follow_x'            => '',
		'follow_facebook'     => '',
		'follow_telegram'     => '',
		'follow_instagram'    => '',
		'follow_tiktok'       => '',
		'follow_threads'      => '',
		'follow_bluesky'      => '',
		'follow_mastodon'     => '',
		'follow_whatsapp'     => '',
		'contact_email'       => '',
		'picks'               => '',
		'hero_image'          => '',
	);

	$stored = get_option( BOI_OPTION_KEY, array() );

	return wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );
}

/**
 * Registers the settings page.
 *
 * @return void
 */
function boi_add_settings_page() {
	add_theme_page(
		__( 'Listicle Settings', 'bestofislam' ),
		__( 'Listicles', 'bestofislam' ),
		'manage_options',
		'boi-settings',
		'boi_render_settings_page'
	);
}
add_action( 'admin_menu', 'boi_add_settings_page' );

/**
 * Registers settings and fields.
 *
 * @return void
 */
function boi_register_settings() {
	register_setting(
		'boi_settings_group',
		BOI_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'boi_sanitize_options',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'boi_register_settings' );

/**
 * Sanitises submitted settings.
 *
 * @param array $input Raw input.
 * @return array
 */
function boi_sanitize_options( $input ) {
	$current = boi_get_options();
	$input   = is_array( $input ) ? $input : array();
	$tab     = isset( $input['_tab'] ) ? sanitize_key( $input['_tab'] ) : 'display';

	switch ( $tab ) {
		case 'display':
			$current['show_rank']  = empty( $input['show_rank'] ) ? 0 : 1;
			$current['image_size'] = sanitize_key( isset( $input['image_size'] ) ? $input['image_size'] : 'large' );

			foreach ( array_keys( boi_follow_networks() ) as $network ) {
				$current[ 'follow_' . $network ] = isset( $input[ 'follow_' . $network ] ) ? esc_url_raw( trim( $input[ 'follow_' . $network ] ) ) : '';
			}

			$current['contact_email'] = isset( $input['contact_email'] ) && is_email( $input['contact_email'] ) ? sanitize_email( $input['contact_email'] ) : '';

			$hero                  = isset( $input['hero_image'] ) ? sanitize_title( $input['hero_image'] ) : '';
			$current['hero_image'] = ( in_array( $hero, array( '', 'none', 'newest' ), true ) || get_page_by_path( $hero, OBJECT, 'post' ) ) ? $hero : '';

			$current['picks'] = isset( $input['picks'] ) ? implode( ',', array_filter( array_map( 'sanitize_title', explode( ',', (string) $input['picks'] ) ) ) ) : '';

			break;

		case 'schema':
			$allowed                  = array( 'ItemList', 'none' );
			$value                    = isset( $input['default_schema'] ) ? $input['default_schema'] : 'ItemList';
			$current['default_schema'] = in_array( $value, $allowed, true ) ? $value : 'ItemList';
			break;

		case 'seo':
			$current['seo_description'] = empty( $input['seo_description'] ) ? 0 : 1;
			break;

		case 'voting':
			$current['voting_enabled']    = empty( $input['voting_enabled'] ) ? 0 : 1;
			$current['voting_login_only'] = empty( $input['voting_login_only'] ) ? 0 : 1;
			$current['voting_rate_limit'] = max( 1, min( 100, (int) ( isset( $input['voting_rate_limit'] ) ? $input['voting_rate_limit'] : 10 ) ) );
			break;
	}

	return $current;
}

/**
 * Enqueues settings screen assets.
 *
 * @param string $hook Current admin page.
 * @return void
 */
function boi_settings_assets( $hook ) {
	if ( 'appearance_page_boi-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'boi-admin-settings', BOI_URI . '/assets/css/admin-settings.css', array(), BOI_VERSION );
	wp_enqueue_script( 'boi-admin-settings', BOI_URI . '/assets/js/admin-settings.js', array(), BOI_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'boi_settings_assets' );

/**
 * Renders the tabbed settings page.
 *
 * @return void
 */
function boi_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$tabs = array(
		'display' => __( 'Display', 'bestofislam' ),
		'schema'  => __( 'Schema', 'bestofislam' ),
		'voting'  => __( 'Voting', 'bestofislam' ),
		'content' => __( 'Content', 'bestofislam' ),
		'seo'     => __( 'Search engines', 'bestofislam' ),
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'display';
	$active = isset( $tabs[ $active ] ) ? $active : 'display';
	$opts   = boi_get_options();

	echo '<div class="wrap boi-settings">';
	echo '<h1>' . esc_html__( 'Listicle Settings', 'bestofislam' ) . '</h1>';

	echo '<nav class="nav-tab-wrapper boi-settings__tabs">';
	foreach ( $tabs as $slug => $label ) {
		printf(
			'<a href="%1$s" class="nav-tab%2$s">%3$s</a>',
			esc_url( admin_url( 'themes.php?page=boi-settings&tab=' . $slug ) ),
			$slug === $active ? ' nav-tab-active' : '',
			esc_html( $label )
		);
	}
	echo '</nav>';

	echo '<form action="options.php" method="post" class="boi-settings__form">';
	settings_fields( 'boi_settings_group' );
	printf(
		'<input type="hidden" name="%1$s[_tab]" value="%2$s" />',
		esc_attr( BOI_OPTION_KEY ),
		esc_attr( $active )
	);

	switch ( $active ) {
		case 'schema':
			boi_render_schema_tab( $opts );
			break;
		case 'voting':
			boi_render_voting_tab( $opts );
			break;
		case 'content':
			echo '</form>';
			boi_render_content_tab();
			echo '</div>';
			return;
		case 'seo':
			boi_render_seo_tab( $opts );
			break;
		default:
			boi_render_display_tab( $opts );
	}

	submit_button();
	echo '</form>';
	echo '</div>';
}

/**
 * Display tab fields.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_display_tab( $opts ) {
	$key = BOI_OPTION_KEY;
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Rank badges', 'bestofislam' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[show_rank]" value="1" <?php checked( $opts['show_rank'], 1 ); ?> />
					<?php esc_html_e( 'Show the numbered rank badge on each entry', 'bestofislam' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="boi-image-size"><?php esc_html_e( 'Entry image size', 'bestofislam' ); ?></label></th>
			<td>
				<select id="boi-image-size" name="<?php echo esc_attr( $key ); ?>[image_size]">
					<?php foreach ( array( 'medium', 'medium_large', 'large', 'full' ) as $size ) : ?>
						<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $opts['image_size'], $size ); ?>>
							<?php echo esc_html( $size ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Colour schemes are configured in the Site Editor under Styles.', 'bestofislam' ); ?></p>
			</td>
		</tr>
		<?php foreach ( boi_follow_networks() as $network => $label ) : ?>
		<tr>
			<th scope="row"><label for="boi-follow-<?php echo esc_attr( $network ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="url" class="regular-text" id="boi-follow-<?php echo esc_attr( $network ); ?>" name="<?php echo esc_attr( $key ); ?>[follow_<?php echo esc_attr( $network ); ?>]" value="<?php echo esc_attr( $opts[ 'follow_' . $network ] ); ?>" placeholder="https://" />
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<p class="description"><?php esc_html_e( 'Channels with a link appear in the Follow links on the front page and as icons in the footer. RSS always appears.', 'bestofislam' ); ?></p>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="boi-contact-email"><?php esc_html_e( 'Contact form recipient', 'bestofislam' ); ?></label></th>
			<td>
				<input type="email" class="regular-text" id="boi-contact-email" name="<?php echo esc_attr( $key ); ?>[contact_email]" value="<?php echo esc_attr( $opts['contact_email'] ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Leave empty to use the site administrator address.', 'bestofislam' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="boi-hero-image"><?php esc_html_e( 'Hero background', 'bestofislam' ); ?></label></th>
			<td>
				<select id="boi-hero-image" name="<?php echo esc_attr( $key ); ?>[hero_image]">
					<option value="" <?php selected( $opts['hero_image'], '' ); ?>><?php esc_html_e( 'Birmingham Quran leaves (default)', 'bestofislam' ); ?></option>
					<option value="newest" <?php selected( $opts['hero_image'], 'newest' ); ?>><?php esc_html_e( 'The newest article\'s image', 'bestofislam' ); ?></option>
					<option value="none" <?php selected( $opts['hero_image'], 'none' ); ?>><?php esc_html_e( 'No image', 'bestofislam' ); ?></option>
					<optgroup label="<?php esc_attr_e( 'A particular article\'s image', 'bestofislam' ); ?>">
						<?php foreach ( get_posts( array( 'post_type' => 'post', 'posts_per_page' => 100, 'orderby' => 'title', 'order' => 'ASC', 'meta_key' => '_thumbnail_id' ) ) as $p ) : // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key ?>
							<option value="<?php echo esc_attr( $p->post_name ); ?>" <?php selected( $opts['hero_image'], $p->post_name ); ?>><?php echo esc_html( get_the_title( $p ) ); ?></option>
						<?php endforeach; ?>
					</optgroup>
				</select>
				<p class="description"><?php esc_html_e( 'The image behind the front-page headline. A dark overlay keeps the text legible, and the image is credited in the band\'s corner.', 'bestofislam' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="boi-picks"><?php esc_html_e( "Editor's picks", 'bestofislam' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" id="boi-picks" name="<?php echo esc_attr( $key ); ?>[picks]" value="<?php echo esc_attr( $opts['picks'] ); ?>" placeholder="same-god, islamic-dilemma, haman-anachronism" />
				<p class="description"><?php esc_html_e( 'Up to three post slugs, comma-separated. Leave empty to show the three longest lists.', 'bestofislam' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Schema tab fields.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_schema_tab( $opts ) {
	$key = BOI_OPTION_KEY;
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="boi-default-schema"><?php esc_html_e( 'Default schema type', 'bestofislam' ); ?></label></th>
			<td>
				<select id="boi-default-schema" name="<?php echo esc_attr( $key ); ?>[default_schema]">
					<option value="ItemList" <?php selected( $opts['default_schema'], 'ItemList' ); ?>>ItemList</option>
					<option value="none" <?php selected( $opts['default_schema'], 'none' ); ?>><?php esc_html_e( 'None', 'bestofislam' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Individual listicles may override this in the block sidebar.', 'bestofislam' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Voting tab fields.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_voting_tab( $opts ) {
	$key = BOI_OPTION_KEY;
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Voting', 'bestofislam' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[voting_enabled]" value="1" <?php checked( $opts['voting_enabled'], 1 ); ?> />
					<?php esc_html_e( 'Allow visitors to vote on entries', 'bestofislam' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Disabled by default. Argumentative content attracts coordinated voting, and a tally reads as a verdict on the argument.', 'bestofislam' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Restriction', 'bestofislam' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[voting_login_only]" value="1" <?php checked( $opts['voting_login_only'], 1 ); ?> />
					<?php esc_html_e( 'Restrict voting to logged-in users', 'bestofislam' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Enable this if coordinated voting becomes a problem.', 'bestofislam' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="boi-rate-limit"><?php esc_html_e( 'Rate limit', 'bestofislam' ); ?></label></th>
			<td>
				<input type="number" id="boi-rate-limit" min="1" max="100" name="<?php echo esc_attr( $key ); ?>[voting_rate_limit]" value="<?php echo esc_attr( $opts['voting_rate_limit'] ); ?>" />
				<p class="description"><?php esc_html_e( 'Maximum votes permitted per visitor in any ten-minute window.', 'bestofislam' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
	boi_render_vote_log();
}

/**
 * Renders the twenty most recent votes for moderation purposes.
 *
 * @return void
 */
function boi_render_vote_log() {
	global $wpdb;

	$table = boi_votes_table();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$rows = $wpdb->get_results( "SELECT post_id, entry_id, created_at FROM {$table} ORDER BY id DESC LIMIT 20" );

	echo '<h2>' . esc_html__( 'Recent votes', 'bestofislam' ) . '</h2>';

	if ( empty( $rows ) ) {
		echo '<p>' . esc_html__( 'No votes recorded yet.', 'bestofislam' ) . '</p>';
		return;
	}

	echo '<table class="widefat striped boi-settings__log"><thead><tr>';
	echo '<th>' . esc_html__( 'Post', 'bestofislam' ) . '</th>';
	echo '<th>' . esc_html__( 'Entry', 'bestofislam' ) . '</th>';
	echo '<th>' . esc_html__( 'Recorded', 'bestofislam' ) . '</th>';
	echo '</tr></thead><tbody>';

	foreach ( $rows as $row ) {
		printf(
			'<tr><td><a href="%1$s">%2$s</a></td><td><code>%3$s</code></td><td>%4$s</td></tr>',
			esc_url( (string) get_edit_post_link( $row->post_id ) ),
			esc_html( get_the_title( $row->post_id ) ),
			esc_html( $row->entry_id ),
			esc_html( $row->created_at )
		);
	}

	echo '</tbody></table>';
}

/**
 * Content tab: re-run population.
 *
 * @return void
 */
function boi_render_content_tab() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['boi_populated'] ) ) {
		printf(
			'<div class="notice notice-success"><p>%s</p></div>',
			esc_html__( 'Content population complete.', 'bestofislam' )
		);
	}

	$stamp = get_option( 'boi_content_populated' );

	echo '<p>' . esc_html__( 'Population creates the section taxonomy, a front page, a Reflections index, an about page, the primary menu, and the seeded articles. Running it again adds only what is missing; nothing already present is duplicated or overwritten.', 'bestofislam' ) . '</p>';

	if ( $stamp ) {
		printf(
			'<p><strong>%1$s</strong> %2$s</p>',
			esc_html__( 'Last run:', 'bestofislam' ),
			esc_html( $stamp )
		);
	}

	printf(
		'<form action="%1$s" method="post">%2$s<input type="hidden" name="action" value="boi_populate" />%3$s</form>',
		esc_url( admin_url( 'admin-post.php' ) ),
		wp_nonce_field( 'boi_populate', '_wpnonce', true, false ),
		get_submit_button( __( 'Populate content', 'bestofislam' ), 'secondary', 'submit', false )
	);
}

/**
 * Search engines tab.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_seo_tab( $opts ) {
	$key = BOI_OPTION_KEY;
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Meta description', 'bestofislam' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[seo_description]" value="1" <?php checked( $opts['seo_description'], 1 ); ?> />
					<?php esc_html_e( 'Emit a description meta tag on every page', 'bestofislam' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Uses the post excerpt, or the section description on archives. Steps back automatically if an SEO plugin is active.', 'bestofislam' ); ?></p>
			</td>
		</tr>
	</table>
	<p><?php esc_html_e( 'Also handled by the theme: unique titles per page, breadcrumbs with BreadcrumbList markup, ItemList markup on every listicle, search results kept out of the index, the search endpoint disallowed in robots.txt, and the section taxonomy included in the XML sitemap.', 'bestofislam' ); ?></p>
	<?php
}
