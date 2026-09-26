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
		'login_enabled'       => 0,
		'search_pretty'       => 1,
		'search_base'         => 'search',
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
			$current['search_pretty']   = empty( $input['search_pretty'] ) ? 0 : 1;
			$base                       = isset( $input['search_base'] ) ? sanitize_title( $input['search_base'] ) : 'search';
			$current['search_base']     = '' !== $base ? $base : 'search';
			// The base changes the rewrite rules; rebuild them on the next load.
			update_option( 'boi_flush_rewrites', 1, false );
			break;

		case 'access':
			$slug    = isset( $input['login_slug'] ) ? sanitize_title( $input['login_slug'] ) : '';
			$enable  = ! empty( $input['login_enabled'] );
			$problem = $enable ? boi_login_slug_problem( $slug ) : '';

			$current['login_slug'] = $slug;

			if ( '' !== $problem ) {
				// Never enable an address that could not work: report and stay off.
				add_settings_error( BOI_OPTION_KEY, 'boi-login-slug', $problem );
				$current['login_enabled'] = 0;
			} else {
				$current['login_enabled'] = $enable ? 1 : 0;
			}
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
		'access'  => __( 'Access', 'bestofislam' ),
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
		case 'access':
			boi_render_access_tab( $opts );
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

	if ( function_exists( 'boi_image_status' ) ) {
		list( $attached, $total ) = boi_image_status();

		printf(
			'<p><strong>%1$s</strong> %2$s</p><p class="description">%3$s</p>',
			esc_html__( 'Featured images:', 'bestofislam' ),
			esc_html( sprintf( /* translators: 1: attached count, 2: total. */ __( '%1$d of %2$d copied into the media library.', 'bestofislam' ), $attached, $total ) ),
			esc_html__( 'Articles whose image is not yet in the media library show the copy bundled with the theme, so every article is illustrated either way. Copies are made three at a time as pages load. If the count does not rise, the server may be refusing uploads: check that wp-content/uploads is writable.', 'bestofislam' )
		);
	}

	if ( function_exists( 'boi_differing_articles' ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['boi_refreshed'] ) ) {
			printf( '<div class="notice notice-success inline"><p>%s</p></div>', esc_html( sprintf( /* translators: %d: number of articles. */ _n( '%d article brought up to date.', '%d articles brought up to date.', absint( $_GET['boi_refreshed'] ), 'bestofislam' ), absint( $_GET['boi_refreshed'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$differing = boi_differing_articles();

		echo '<h2>' . esc_html__( 'Seeded articles', 'bestofislam' ) . '</h2>';

		if ( empty( $differing ) ) {
			echo '<p>' . esc_html__( 'Every seeded article matches this version of the theme.', 'bestofislam' ) . '</p>';
		} else {
			echo '<p>' . esc_html__( 'These articles differ from this version of the theme. Updates refresh a seeded article automatically only when they can tell it has not been edited; for these they cannot, so they are left alone. Tick any you have not edited yourself to bring them up to date. Ticked articles are replaced with this version\'s text, and any edits in them are lost.', 'bestofislam' ) . '</p>';
			printf( '<form action="%1$s" method="post">', esc_url( admin_url( 'admin-post.php' ) ) );
			wp_nonce_field( 'boi_refresh_articles' );
			echo '<input type="hidden" name="action" value="boi_refresh_articles" /><ul>';

			foreach ( $differing as $slug => $post ) {
				printf(
					'<li><label><input type="checkbox" name="boi_articles[]" value="%1$s" /> %2$s</label> <a href="%3$s">%4$s</a></li>',
					esc_attr( $slug ),
					esc_html( get_the_title( $post ) ),
					esc_url( get_permalink( $post ) ),
					esc_html__( 'View', 'bestofislam' )
				);
			}

			echo '</ul>';
			submit_button( __( 'Update the ticked articles', 'bestofislam' ), 'secondary', 'submit', false );
			echo '</form>';
		}
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
		<tr>
			<th scope="row"><?php esc_html_e( 'Readable search addresses', 'bestofislam' ); ?></th>
			<td>
				<?php if ( function_exists( 'wpseosearch_base' ) ) : ?>
					<p class="description"><?php esc_html_e( 'The Pretty Search Permalinks plugin is active, so the theme leaves search addresses to it. Deactivate the plugin to manage them here; its base is carried over.', 'bestofislam' ); ?></p>
				<?php else : ?>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[search_pretty]" value="1" <?php checked( ! empty( $opts['search_pretty'] ) ); ?> />
						<?php esc_html_e( 'Send searches to a readable address, such as /search/Paul/, keeping any filters', 'bestofislam' ); ?>
					</label>
					<p>
						<label for="boi-search-base"><?php esc_html_e( 'Base word', 'bestofislam' ); ?></label>
						<code><?php echo esc_html( trailingslashit( home_url() ) ); ?></code><input type="text" id="boi-search-base" class="regular-text" name="<?php echo esc_attr( $key ); ?>[search_base]" value="<?php echo esc_attr( boi_search_base() ); ?>" /><code>/Paul/</code>
					</p>
					<p class="description"><?php esc_html_e( 'The chosen base is also kept out of search engines through robots.txt.', 'bestofislam' ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<p><?php esc_html_e( 'Also handled by the theme: unique titles per page; canonical links on every indexable view; breadcrumbs; Article markup on every article, with image licence metadata; Organization and WebSite markup on the front page; list and FAQ markup; search results kept out of the index and out of robots.txt; and an XML sitemap limited to articles, pages and sections.', 'bestofislam' ); ?></p>
	<?php
}

/**
 * The Access tab: the login address and the list of unlisted items.
 *
 * @param array $opts Options.
 * @return void
 */
function boi_render_access_tab( $opts ) {
	$key      = BOI_OPTION_KEY;
	$slug     = boi_login_slug();
	$active   = boi_login_enabled();
	$override = defined( 'BOI_HIDE_LOGIN' ) && ! BOI_HIDE_LOGIN;
	?>
	<h2><?php esc_html_e( 'Login address', 'bestofislam' ); ?></h2>
	<?php if ( defined( 'WPS_HIDE_LOGIN_VERSION' ) ) : ?>
		<div class="notice notice-info inline"><p><?php esc_html_e( 'The WPS Hide Login plugin is active, so the theme leaves the login address to it. Deactivate the plugin to manage the address here; its address is carried over.', 'bestofislam' ); ?></p></div>
	<?php elseif ( $override ) : ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'The custom login address is switched off in wp-config.php (BOI_HIDE_LOGIN is false). The standard wp-login.php is in use.', 'bestofislam' ); ?></p></div>
	<?php endif; ?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Custom login address', 'bestofislam' ); ?></th>
			<td>
				<label><input type="checkbox" name="<?php echo esc_attr( $key ); ?>[login_enabled]" value="1" <?php checked( ! empty( $opts['login_enabled'] ) ); ?> /> <?php esc_html_e( 'Serve the login page from a private address, and answer wp-login.php and wp-admin with the 404 page for anyone not logged in', 'bestofislam' ); ?></label>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="boi-login-slug"><?php esc_html_e( 'Address', 'bestofislam' ); ?></label></th>
			<td>
				<code><?php echo esc_html( trailingslashit( home_url() ) ); ?></code><input type="text" class="regular-text" id="boi-login-slug" name="<?php echo esc_attr( $key ); ?>[login_slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'for example, enter-archive', 'bestofislam' ); ?>" />
				<p class="description"><?php esc_html_e( 'At least six characters, and not an address any page, post or section uses. Bookmark the new address before saving.', 'bestofislam' ); ?></p>
				<?php if ( $active ) : ?>
					<p><strong><?php esc_html_e( 'In force:', 'bestofislam' ); ?></strong> <a href="<?php echo esc_url( boi_login_url_base() ); ?>"><?php echo esc_html( boi_login_url_base() ); ?></a></p>
				<?php endif; ?>
				<p class="description"><?php echo wp_kses( __( 'Locked out? Add <code>define( \'BOI_HIDE_LOGIN\', false );</code> to wp-config.php and log in at wp-login.php as usual.', 'bestofislam' ), array( 'code' => array() ) ); ?></p>
			</td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Unlisted posts and pages', 'bestofislam' ); ?></h2>
	<?php if ( class_exists( 'Unlist_Posts' ) ) : ?>
		<div class="notice notice-info inline"><p><?php esc_html_e( 'The Unlist Posts & Pages plugin is active, so the theme leaves unlisting to it. Deactivate the plugin to manage unlisting here; its list is carried over.', 'bestofislam' ); ?></p></div>
	<?php else : ?>
		<p class="description"><?php esc_html_e( 'Unlist an item from the Visibility in listings box in its editor sidebar. An unlisted item opens from its own link but appears in no listing, search, feed or sitemap, and asks search engines not to index it.', 'bestofislam' ); ?></p>
		<?php
		$ids = boi_unlisted_ids();

		if ( empty( $ids ) ) {
			echo '<p>' . esc_html__( 'Nothing is unlisted.', 'bestofislam' ) . '</p>';
		} else {
			echo '<ul class="ul-disc">';

			foreach ( $ids as $id ) {
				$post = get_post( $id );

				if ( $post ) {
					printf( '<li><a href="%1$s">%2$s</a> <span class="description">(%3$s)</span></li>', esc_url( get_edit_post_link( $id ) ), esc_html( get_the_title( $post ) ), esc_html( get_post_type_object( $post->post_type )->labels->singular_name ) );
				}
			}

			echo '</ul>';
		}
		?>
	<?php endif; ?>
	<?php
}

/**
 * Rebuilds the rewrite rules once after the search base changes.
 *
 * @return void
 */
function boi_maybe_flush_rewrites() {
	if ( get_option( 'boi_flush_rewrites' ) ) {
		delete_option( 'boi_flush_rewrites' );
		flush_rewrite_rules( false );
	}
}
add_action( 'init', 'boi_maybe_flush_rewrites', 99 );
