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
		'verify_google'       => '',
		'verify_bing'         => '',
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
		'security_headers'    => 1,
		'security_hsts'       => 0,
		'search_pretty'       => 1,
		'search_base'         => 'search',
		'errors_pages'        => 1,
		'errors_alerts'       => 1,
		'errors_email'        => '',
		'errors_cooldown'     => 30,
		'sec_xmlrpc_off'        => 1,
		'sec_app_passwords_off' => 1,
		'sec_rest_lock'         => 1,
		'sec_spam_gate'         => 1,
		'sec_gone'              => 1,
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
		__( 'Theme Options', 'bestofislam' ),
		__( 'Theme Options', 'bestofislam' ),
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

			// Accept the bare code or the whole meta tag pasted from the console.
			foreach ( array( 'verify_google', 'verify_bing' ) as $field ) {
				// With an SEO plugin active the fields are not shown: keep what is saved.
				if ( ! isset( $input[ $field ] ) ) {
					continue;
				}

				$code = trim( (string) $input[ $field ] );

				if ( preg_match( '/content=["\']([^"\']+)["\']/', $code, $match ) ) {
					$code = $match[1];
				}

				$current[ $field ] = preg_match( '/^[A-Za-z0-9_\-]{10,100}$/', $code ) ? $code : '';
			}
			// With the plugin active these inputs are not shown, so absent
			// values mean "not offered", not "switched off": keep what is saved.
			if ( ! function_exists( 'wpseosearch_base' ) ) {
				$current['search_pretty'] = empty( $input['search_pretty'] ) ? 0 : 1;
				$base                     = isset( $input['search_base'] ) ? sanitize_title( $input['search_base'] ) : 'search';
				$current['search_base']   = '' !== $base ? $base : 'search';
				// The base changes the rewrite rules; rebuild them on the next load.
				update_option( 'boi_flush_rewrites', 1, false );
			}
			break;

		case 'access':
			$slug    = isset( $input['login_slug'] ) ? sanitize_title( $input['login_slug'] ) : '';
			$enable  = ! empty( $input['login_enabled'] );
			$problem = $enable ? boi_login_slug_problem( $slug ) : '';

			$current['login_slug']       = $slug;
			$current['security_headers'] = empty( $input['security_headers'] ) ? 0 : 1;
			$current['security_hsts']    = empty( $input['security_hsts'] ) ? 0 : 1;

			if ( '' !== $problem ) {
				// Never enable an address that could not work: report and stay off.
				add_settings_error( BOI_OPTION_KEY, 'boi-login-slug', $problem );
				$current['login_enabled'] = 0;
			} else {
				$current['login_enabled'] = $enable ? 1 : 0;
			}
			break;

		case 'errors':
			$current['errors_pages']    = empty( $input['errors_pages'] ) ? 0 : 1;
			$current['errors_alerts']   = empty( $input['errors_alerts'] ) ? 0 : 1;
			$current['errors_email']    = isset( $input['errors_email'] ) && is_email( $input['errors_email'] ) ? sanitize_email( $input['errors_email'] ) : '';
			$current['errors_cooldown'] = max( 5, min( 1440, (int) ( isset( $input['errors_cooldown'] ) ? $input['errors_cooldown'] : 30 ) ) );
			break;

		case 'security':
			foreach ( array( 'sec_xmlrpc_off', 'sec_app_passwords_off', 'sec_rest_lock', 'sec_spam_gate', 'sec_gone' ) as $field ) {
				$current[ $field ] = empty( $input[ $field ] ) ? 0 : 1;
			}
			break;

		case 'voting':
			$current['voting_enabled'] = empty( $input['voting_enabled'] ) ? 0 : 1;
			// The two dependent fields are disabled, and so not submitted, while
			// voting is off. Keep their saved values rather than resetting them.
			if ( $current['voting_enabled'] ) {
				$current['voting_login_only'] = empty( $input['voting_login_only'] ) ? 0 : 1;
				$current['voting_rate_limit'] = max( 1, min( 100, (int) ( isset( $input['voting_rate_limit'] ) ? $input['voting_rate_limit'] : 10 ) ) );
			}
			break;
	}

	return $current;
}

/**
 * Enqueues settings screen assets, on the Theme Options screen only.
 *
 * @param string $hook Current admin page.
 * @return void
 */
function boi_settings_assets( $hook ) {
	if ( 'appearance_page_boi-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'boi-admin-settings', BOI_URI . '/assets/css/admin-settings.css', array( 'dashicons' ), BOI_VERSION );
	wp_enqueue_script( 'boi-admin-settings', BOI_URI . '/assets/js/admin-settings.js', array(), BOI_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'boi_settings_assets' );

/**
 * The tabs of the Theme Options screen, in order.
 *
 * Each has a label, a Dashicons icon and the usage text shown at the top of
 * its panel.
 *
 * @return array<string, array{label: string, icon: string, usage: string}>
 */
function boi_settings_tabs() {
	return array(
		'display'  => array(
			'label' => __( 'Display', 'bestofislam' ),
			'icon'  => 'dashicons-admin-appearance',
			'usage' => __( 'How entries and the front page look. Choose the image size for list entries, the picture behind the front-page headline and up to 3 editor\'s picks. The follow links you fill in appear on the front page and in the footer. Colours live in the Site Editor, under Styles.', 'bestofislam' ),
		),
		'schema'   => array(
			'label' => __( 'Schema', 'bestofislam' ),
			'icon'  => 'dashicons-editor-code',
			'usage' => __( 'The list markup search engines read. ItemList describes each numbered list as a ranked list of its entries. A single listicle can choose differently in its block sidebar.', 'bestofislam' ),
		),
		'voting'   => array(
			'label' => __( 'Voting', 'bestofislam' ),
			'icon'  => 'dashicons-thumbs-up',
			'usage' => __( 'Voting is off until you switch it on. Once it is on, you can limit it to logged-in users and set how many votes one visitor may cast in 10 minutes. The 20 latest votes are listed below the settings.', 'bestofislam' ),
		),
		'content'  => array(
			'label' => __( 'Content', 'bestofislam' ),
			'icon'  => 'dashicons-admin-page',
			'usage' => __( 'Population builds the sections, the pages, the menu and the 151 seeded articles. It adds what is missing and leaves alone what is there. If a seeded article differs from this release, it is listed here, and you choose whether to replace it.', 'bestofislam' ),
		),
		'seo'      => array(
			'label' => __( 'Search engines', 'bestofislam' ),
			'icon'  => 'dashicons-search',
			'usage' => __( 'Meta descriptions, the verification codes for Google and Bing, and readable search addresses. With Rank Math or another SEO plugin active, the theme hands those jobs to the plugin and says so here.', 'bestofislam' ),
		),
		'access'   => array(
			'label' => __( 'Access', 'bestofislam' ),
			'icon'  => 'dashicons-lock',
			'usage' => __( 'Move the login page to a private address, send the browser security headers and see which items are unlisted. Bookmark a new login address before you save it.', 'bestofislam' ),
		),
		'security' => array(
			'label' => __( 'Security', 'bestofislam' ),
			'icon'  => 'dashicons-shield',
			'usage' => __( 'The switches at the top close the routes spam tools use to publish from outside the site. Below them are the spam scan, the clean-up buttons, the attack reports and the security log. Each button asks before it changes anything.', 'bestofislam' ),
		),
		'errors'   => array(
			'label' => __( 'Errors', 'bestofislam' ),
			'icon'  => 'dashicons-warning',
			'usage' => __( 'Branded pages for maintenance, database failures and PHP errors, and the email alert when one happens. The slider sets the quiet time between 2 alerts of the same kind.', 'bestofislam' ),
		),
	);
}

/**
 * Tabs whose panels run costly queries (the article fingerprints, the
 * security reports). They render only when they are the open tab; from
 * another tab they are reached with a full load of ?tab=.
 *
 * @return string[]
 */
function boi_settings_heavy_tabs() {
	return array( 'content', 'security' );
}

/**
 * The hidden fields options.php needs, for one form among several on the
 * screen. settings_fields() prints them with fixed IDs, which would repeat
 * once per tab, so they are printed here without IDs. The referer brings
 * the browser back to the same tab after saving.
 *
 * @param string $tab Tab slug.
 * @return void
 */
function boi_settings_hidden_fields( $tab ) {
	printf(
		'<input type="hidden" name="option_page" value="boi_settings_group" /><input type="hidden" name="action" value="update" /><input type="hidden" name="_wpnonce" value="%1$s" /><input type="hidden" name="_wp_http_referer" value="%2$s" /><input type="hidden" name="%3$s[_tab]" value="%4$s" />',
		esc_attr( wp_create_nonce( 'boi_settings_group-options' ) ),
		esc_attr( wp_make_link_relative( admin_url( 'themes.php?page=boi-settings&tab=' . $tab ) ) ),
		esc_attr( BOI_OPTION_KEY ),
		esc_attr( $tab )
	);
}

/**
 * Opens a settings card.
 *
 * @param string $title Card heading.
 * @param string $intro Optional line under the heading.
 * @return void
 */
function boi_opt_card_open( $title, $intro = '' ) {
	echo '<div class="boi-card">';
	echo '<h2 class="boi-card__title">' . esc_html( $title ) . '</h2>';

	if ( '' !== $intro ) {
		echo '<p class="boi-card__intro">' . esc_html( $intro ) . '</p>';
	}
}

/**
 * Closes a settings card.
 *
 * @return void
 */
function boi_opt_card_close() {
	echo '</div>';
}

/**
 * One switch row for a boolean setting.
 *
 * @param array  $args {
 *     @type string $key     Option key inside boi_settings.
 *     @type string $label   Row label.
 *     @type string $text    What switching it on does.
 *     @type string $help    Optional further help.
 *     @type bool   $checked Whether it is on.
 *     @type string $depends Optional ID of a switch this row depends on.
 * }
 * @return void
 */
function boi_opt_toggle( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'key'     => '',
			'label'   => '',
			'text'    => '',
			'help'    => '',
			'checked' => false,
			'depends' => '',
		)
	);
	$id   = 'boi-' . str_replace( '_', '-', $args['key'] );
	$desc = '' !== $args['help'] ? $id . '-help' : '';
	?>
	<div class="boi-field boi-field--toggle"<?php echo '' !== $args['depends'] ? ' data-boi-depends="' . esc_attr( $args['depends'] ) . '"' : ''; ?>>
		<div class="boi-field__label"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $args['label'] ); ?></label></div>
		<div class="boi-field__control">
			<label class="boi-toggle">
				<input type="checkbox" role="switch" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( BOI_OPTION_KEY . '[' . $args['key'] . ']' ); ?>" value="1" <?php checked( (bool) $args['checked'] ); ?><?php echo '' !== $desc ? ' aria-describedby="' . esc_attr( $desc ) . '"' : ''; ?> />
				<?php if ( '' !== $args['text'] ) : ?>
					<span class="boi-toggle__text"><?php echo esc_html( $args['text'] ); ?></span>
				<?php endif; ?>
			</label>
			<?php if ( '' !== $args['help'] ) : ?>
				<p class="description" id="<?php echo esc_attr( $id ); ?>-help"><?php echo esc_html( $args['help'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * One slider row for a bounded number, with its value shown as it moves.
 *
 * @param array $args {
 *     @type string $key     Option key.
 *     @type string $label   Row label.
 *     @type int    $value   Current value.
 *     @type int    $min     Lowest value.
 *     @type int    $max     Highest value.
 *     @type string $unit    Unit after the number, already translated.
 *     @type string $help    Help text.
 *     @type string $depends Optional ID of a switch this row depends on.
 * }
 * @return void
 */
function boi_opt_range( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'key'     => '',
			'label'   => '',
			'value'   => 0,
			'min'     => 0,
			'max'     => 100,
			'unit'    => '',
			'help'    => '',
			'depends' => '',
		)
	);
	$id   = 'boi-' . str_replace( '_', '-', $args['key'] );
	?>
	<div class="boi-field boi-field--range"<?php echo '' !== $args['depends'] ? ' data-boi-depends="' . esc_attr( $args['depends'] ) . '"' : ''; ?>>
		<div class="boi-field__label"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $args['label'] ); ?></label></div>
		<div class="boi-field__control">
			<div class="boi-range">
				<input type="range" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( BOI_OPTION_KEY . '[' . $args['key'] . ']' ); ?>" min="<?php echo esc_attr( (string) $args['min'] ); ?>" max="<?php echo esc_attr( (string) $args['max'] ); ?>" step="1" value="<?php echo esc_attr( (string) (int) $args['value'] ); ?>" data-boi-unit="<?php echo esc_attr( $args['unit'] ); ?>" aria-describedby="<?php echo esc_attr( $id ); ?>-help" />
				<output class="boi-range__value" for="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>-value"><?php echo esc_html( trim( (int) $args['value'] . ' ' . $args['unit'] ) ); ?></output>
			</div>
			<p class="description" id="<?php echo esc_attr( $id ); ?>-help"><?php echo esc_html( $args['help'] ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * One row of visual selector cards: a radio group drawn as cards.
 *
 * @param array $args {
 *     @type string $key     Option key.
 *     @type string $label   Group label (the legend).
 *     @type string $value   Current value.
 *     @type array  $choices value => array( title, description ).
 *     @type string $help    Help text.
 * }
 * @return void
 */
function boi_opt_choices( $args ) {
	$id = 'boi-' . str_replace( '_', '-', $args['key'] );
	?>
	<fieldset class="boi-field boi-field--choices" aria-describedby="<?php echo esc_attr( $id ); ?>-help">
		<legend class="boi-field__label"><?php echo esc_html( $args['label'] ); ?></legend>
		<div class="boi-field__control">
			<div class="boi-choices">
				<?php foreach ( $args['choices'] as $value => $choice ) : ?>
					<label class="boi-choice">
						<input type="radio" name="<?php echo esc_attr( BOI_OPTION_KEY . '[' . $args['key'] . ']' ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" <?php checked( (string) $args['value'], (string) $value ); ?> />
						<span class="boi-choice__title"><?php echo esc_html( $choice[0] ); ?></span>
						<span class="boi-choice__desc"><?php echo esc_html( $choice[1] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
			<p class="description" id="<?php echo esc_attr( $id ); ?>-help"><?php echo esc_html( $args['help'] ); ?></p>
		</div>
	</fieldset>
	<?php
}

/**
 * Opens a plain row: label on the left, control on the right.
 *
 * @param string $label Row label.
 * @param string $for   ID of the control the label names, or '' for none.
 * @return void
 */
function boi_opt_row_open( $label, $for = '' ) {
	echo '<div class="boi-field"><div class="boi-field__label">';

	if ( '' !== $for ) {
		echo '<label for="' . esc_attr( $for ) . '">' . esc_html( $label ) . '</label>';
	} else {
		echo '<span class="boi-field__heading">' . esc_html( $label ) . '</span>';
	}

	echo '</div><div class="boi-field__control">';
}

/**
 * Closes a plain row.
 *
 * @return void
 */
function boi_opt_row_close() {
	echo '</div></div>';
}

/**
 * Renders the Theme Options screen: a header, the tab list, one panel per
 * tab and a footer. Every panel is in the page, so the tabs switch without
 * a reload; each panel that saves settings has its own form, which saves
 * only that tab's fields, as before. Without JavaScript the tabs are
 * ordinary links and the server opens the chosen one.
 *
 * @return void
 */
function boi_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$tabs = boi_settings_tabs();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- screen choice only.
	$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'display';
	$active = isset( $tabs[ $active ] ) ? $active : 'display';
	$opts   = boi_get_options();
	?>
	<div class="wrap boi-settings">
		<header class="boi-opt__header">
			<span class="boi-opt__icon dashicons dashicons-admin-appearance" aria-hidden="true"></span>
			<div class="boi-opt__heading">
				<h1><?php esc_html_e( 'Theme Options', 'bestofislam' ); ?></h1>
				<p class="boi-opt__lede"><?php esc_html_e( 'Settings for the Best of Islam theme. Each tab saves on its own.', 'bestofislam' ); ?></p>
			</div>
			<span class="boi-opt__badge">
				<?php
				/* translators: %s: theme version number. */
				echo esc_html( sprintf( __( 'Version %s', 'bestofislam' ), BOI_VERSION ) );
				?>
			</span>
		</header>

		<?php settings_errors(); ?>
		<hr class="wp-header-end" />

		<nav class="boi-opt__tabs" aria-label="<?php esc_attr_e( 'Theme Options sections', 'bestofislam' ); ?>">
			<div class="boi-opt__tablist" role="tablist" aria-label="<?php esc_attr_e( 'Theme Options sections', 'bestofislam' ); ?>">
				<?php foreach ( $tabs as $slug => $tab ) : ?>
					<a
						class="boi-opt__tab<?php echo $slug === $active ? ' is-active' : ''; ?>"
						id="boi-tab-<?php echo esc_attr( $slug ); ?>"
						href="<?php echo esc_url( admin_url( 'themes.php?page=boi-settings&tab=' . $slug ) ); ?>"
						role="tab"
						aria-controls="boi-panel-<?php echo esc_attr( $slug ); ?>"
						aria-selected="<?php echo $slug === $active ? 'true' : 'false'; ?>"
						tabindex="<?php echo $slug === $active ? '0' : '-1'; ?>"
						data-boi-tab="<?php echo esc_attr( $slug ); ?>"
						<?php echo ( $slug !== $active && in_array( $slug, boi_settings_heavy_tabs(), true ) ) ? 'data-boi-reload="1"' : ''; ?>
					><span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span><span class="boi-opt__tab-label"><?php echo esc_html( $tab['label'] ); ?></span></a>
				<?php endforeach; ?>
			</div>
		</nav>

		<?php foreach ( $tabs as $slug => $tab ) : ?>
			<section
				class="boi-opt__panel"
				id="boi-panel-<?php echo esc_attr( $slug ); ?>"
				role="tabpanel"
				aria-labelledby="boi-tab-<?php echo esc_attr( $slug ); ?>"
				tabindex="0"
				<?php echo $slug === $active ? '' : 'hidden'; ?>
			>
				<div class="boi-card boi-card--usage">
					<h2 class="boi-card__title"><?php esc_html_e( 'How this tab works', 'bestofislam' ); ?></h2>
					<p><?php echo esc_html( $tab['usage'] ); ?></p>
				</div>
				<?php
				if ( $slug === $active || ! in_array( $slug, boi_settings_heavy_tabs(), true ) ) {
					boi_render_settings_panel( $slug, $opts );
				} else {
					// Heavy panels run their queries only when opened.
					printf(
						'<p><a class="button" href="%1$s">%2$s</a></p>',
						esc_url( admin_url( 'themes.php?page=boi-settings&tab=' . $slug ) ),
						/* translators: %s: tab name. */
						esc_html( sprintf( __( 'Open the %s tab', 'bestofislam' ), $tab['label'] ) )
					);
				}
				?>
			</section>
		<?php endforeach; ?>

		<footer class="boi-opt__footer">
			<a href="<?php echo esc_url( BOI_URI . '/readme.txt' ); ?>"><?php esc_html_e( 'Theme readme', 'bestofislam' ); ?></a>
			<a href="<?php echo esc_url( BOI_URI . '/CHANGELOG.md' ); ?>"><?php esc_html_e( 'Changelog', 'bestofislam' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><?php esc_html_e( 'Site Health', 'bestofislam' ); ?></a>
		</footer>
	</div>
	<?php
}

/**
 * The body of one tab panel.
 *
 * @param string $slug Tab slug.
 * @param array  $opts Current options.
 * @return void
 */
function boi_render_settings_panel( $slug, $opts ) {
	if ( 'content' === $slug ) {
		// Population and the article refresh post to admin-post.php.
		boi_render_content_tab();
		return;
	}

	echo '<form action="' . esc_url( admin_url( 'options.php' ) ) . '" method="post" class="boi-settings__form">';
	boi_settings_hidden_fields( $slug );

	switch ( $slug ) {
		case 'schema':
			boi_render_schema_tab( $opts );
			break;
		case 'voting':
			boi_render_voting_tab( $opts );
			break;
		case 'seo':
			boi_render_seo_tab( $opts );
			break;
		case 'access':
			boi_render_access_tab( $opts );
			break;
		case 'security':
			boi_sec_render_settings( $opts );
			break;
		case 'errors':
			boi_render_errors_tab( $opts );
			break;
		default:
			boi_render_display_tab( $opts );
	}

	echo '<div class="boi-opt__actions">';
	submit_button( __( 'Save changes', 'bestofislam' ), 'primary', 'submit', false, array( 'id' => 'boi-save-' . $slug ) );
	echo '</div></form>';

	if ( 'voting' === $slug ) {
		boi_render_vote_log();
	} elseif ( 'security' === $slug ) {
		// The tools and reports post to admin-post.php and admin-ajax.php,
		// so they sit outside the settings form.
		boi_sec_render_tools();
	}
}

/**
 * Display tab fields.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_display_tab( $opts ) {
	$key   = BOI_OPTION_KEY;
	$sizes = array();

	foreach ( array( 'medium', 'medium_large', 'large', 'full' ) as $size ) {
		$width = (int) get_option( $size . '_size_w' );
		/* translators: %d: image width in pixels. */
		$sizes[ $size ] = array( $size, 'full' === $size ? __( 'The original upload', 'bestofislam' ) : sprintf( __( 'Up to %d pixels wide', 'bestofislam' ), $width ? $width : 768 ) );
	}

	boi_opt_card_open( __( 'Entries', 'bestofislam' ) );
	boi_opt_toggle(
		array(
			'key'     => 'show_rank',
			'label'   => __( 'Rank badges', 'bestofislam' ),
			'text'    => __( 'Show the numbered rank badge on each entry', 'bestofislam' ),
			'checked' => ! empty( $opts['show_rank'] ),
		)
	);
	boi_opt_choices(
		array(
			'key'     => 'image_size',
			'label'   => __( 'Entry image size', 'bestofislam' ),
			'value'   => $opts['image_size'],
			'choices' => $sizes,
			'help'    => __( 'Colour schemes are configured in the Site Editor under Styles.', 'bestofislam' ),
		)
	);
	boi_opt_card_close();

	boi_opt_card_open( __( 'Front page', 'bestofislam' ) );
	boi_opt_row_open( __( 'Hero background', 'bestofislam' ), 'boi-hero-image' );
	?>
	<select id="boi-hero-image" name="<?php echo esc_attr( $key ); ?>[hero_image]" aria-describedby="boi-hero-image-help">
		<option value="" <?php selected( $opts['hero_image'], '' ); ?>><?php esc_html_e( 'Birmingham Quran leaves (default)', 'bestofislam' ); ?></option>
		<option value="newest" <?php selected( $opts['hero_image'], 'newest' ); ?>><?php esc_html_e( 'The newest article\'s image', 'bestofislam' ); ?></option>
		<option value="none" <?php selected( $opts['hero_image'], 'none' ); ?>><?php esc_html_e( 'No image', 'bestofislam' ); ?></option>
		<optgroup label="<?php esc_attr_e( 'A particular article\'s image', 'bestofislam' ); ?>">
			<?php foreach ( get_posts( array( 'post_type' => 'post', 'posts_per_page' => 100, 'orderby' => 'title', 'order' => 'ASC', 'meta_key' => '_thumbnail_id' ) ) as $p ) : // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key ?>
				<option value="<?php echo esc_attr( $p->post_name ); ?>" <?php selected( $opts['hero_image'], $p->post_name ); ?>><?php echo esc_html( get_the_title( $p ) ); ?></option>
			<?php endforeach; ?>
		</optgroup>
	</select>
	<p class="description" id="boi-hero-image-help"><?php esc_html_e( 'The image behind the front-page headline. A dark overlay keeps the text legible, and the image is credited in the band\'s corner.', 'bestofislam' ); ?></p>
	<?php
	boi_opt_row_close();
	boi_opt_row_open( __( "Editor's picks", 'bestofislam' ), 'boi-picks' );
	?>
	<input type="text" class="regular-text" id="boi-picks" name="<?php echo esc_attr( $key ); ?>[picks]" value="<?php echo esc_attr( $opts['picks'] ); ?>" placeholder="same-god, islamic-dilemma, haman-anachronism" aria-describedby="boi-picks-help" />
	<p class="description" id="boi-picks-help"><?php esc_html_e( 'Up to three post slugs, comma-separated. Leave empty to show the three longest lists.', 'bestofislam' ); ?></p>
	<?php
	boi_opt_row_close();
	boi_opt_card_close();

	boi_opt_card_open( __( 'Follow links', 'bestofislam' ), __( 'Channels with a link appear in the Follow links on the front page and as icons in the footer. RSS always appears.', 'bestofislam' ) );
	echo '<div class="boi-grid">';
	foreach ( boi_follow_networks() as $network => $label ) {
		?>
		<p class="boi-grid__item">
			<label for="boi-follow-<?php echo esc_attr( $network ); ?>"><?php echo esc_html( $label ); ?></label>
			<input type="url" class="regular-text" id="boi-follow-<?php echo esc_attr( $network ); ?>" name="<?php echo esc_attr( $key ); ?>[follow_<?php echo esc_attr( $network ); ?>]" value="<?php echo esc_attr( $opts[ 'follow_' . $network ] ); ?>" placeholder="https://" />
		</p>
		<?php
	}
	echo '</div>';
	boi_opt_card_close();

	boi_opt_card_open( __( 'Contact form', 'bestofislam' ) );
	boi_opt_row_open( __( 'Contact form recipient', 'bestofislam' ), 'boi-contact-email' );
	?>
	<input type="email" class="regular-text" id="boi-contact-email" name="<?php echo esc_attr( $key ); ?>[contact_email]" value="<?php echo esc_attr( $opts['contact_email'] ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" aria-describedby="boi-contact-email-help" />
	<p class="description" id="boi-contact-email-help"><?php esc_html_e( 'Leave empty to use the site administrator address.', 'bestofislam' ); ?></p>
	<?php
	boi_opt_row_close();
	boi_opt_card_close();
}

/**
 * Schema tab fields.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_schema_tab( $opts ) {
	boi_opt_card_open( __( 'List markup', 'bestofislam' ) );
	boi_opt_choices(
		array(
			'key'     => 'default_schema',
			'label'   => __( 'Default schema type', 'bestofislam' ),
			'value'   => $opts['default_schema'],
			'choices' => array(
				'ItemList' => array( 'ItemList', __( 'Each list is marked up as a ranked list of its entries.', 'bestofislam' ) ),
				'none'     => array( __( 'None', 'bestofislam' ), __( 'Lists carry no list markup of their own.', 'bestofislam' ) ),
			),
			'help'    => __( 'Individual listicles may override this in the block sidebar.', 'bestofislam' ),
		)
	);
	boi_opt_card_close();
}

/**
 * Voting tab fields.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_voting_tab( $opts ) {
	boi_opt_card_open( __( 'Visitor voting', 'bestofislam' ) );
	boi_opt_toggle(
		array(
			'key'     => 'voting_enabled',
			'label'   => __( 'Voting', 'bestofislam' ),
			'text'    => __( 'Allow visitors to vote on entries', 'bestofislam' ),
			'help'    => __( 'Disabled by default. Argumentative content attracts coordinated voting, and a tally reads as a verdict on the argument.', 'bestofislam' ),
			'checked' => ! empty( $opts['voting_enabled'] ),
		)
	);
	boi_opt_toggle(
		array(
			'key'     => 'voting_login_only',
			'label'   => __( 'Restriction', 'bestofislam' ),
			'text'    => __( 'Restrict voting to logged-in users', 'bestofislam' ),
			'help'    => __( 'Enable this if coordinated voting becomes a problem.', 'bestofislam' ),
			'checked' => ! empty( $opts['voting_login_only'] ),
			'depends' => 'boi-voting-enabled',
		)
	);
	boi_opt_range(
		array(
			'key'     => 'voting_rate_limit',
			'label'   => __( 'Rate limit', 'bestofislam' ),
			'value'   => (int) $opts['voting_rate_limit'],
			'min'     => 1,
			'max'     => 100,
			'unit'    => __( 'votes', 'bestofislam' ),
			'help'    => __( 'Maximum votes permitted per visitor in any ten-minute window.', 'bestofislam' ),
			'depends' => 'boi-voting-enabled',
		)
	);
	boi_opt_card_close();
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

	echo '<div class="boi-card"><h2 class="boi-card__title">' . esc_html__( 'Recent votes', 'bestofislam' ) . '</h2>';

	if ( empty( $rows ) ) {
		echo '<p>' . esc_html__( 'No votes recorded yet.', 'bestofislam' ) . '</p></div>';
		return;
	}

	echo '<div class="boi-scroll" tabindex="0"><table class="widefat striped boi-settings__log"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Post', 'bestofislam' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Entry', 'bestofislam' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Recorded', 'bestofislam' ) . '</th>';
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

	echo '</tbody></table></div></div>';
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
			'<div class="notice notice-success is-dismissible inline"><p>%s</p></div>',
			esc_html__( 'Content population complete.', 'bestofislam' )
		);
	}

	$stamp = get_option( 'boi_content_populated' );

	boi_opt_card_open( __( 'Population', 'bestofislam' ) );
	echo '<p>' . esc_html__( 'Population creates the section taxonomy, a front page, a Reflections index, an about page, the primary menu, and the seeded articles. Running it again adds only what is missing; nothing already present is duplicated or overwritten.', 'bestofislam' ) . '</p>';

	if ( $stamp ) {
		printf(
			'<p><strong>%1$s</strong> %2$s</p>',
			esc_html__( 'Last run:', 'bestofislam' ),
			esc_html( $stamp )
		);
	}

	printf(
		'<form action="%1$s" method="post"><input type="hidden" name="_wpnonce" value="%2$s" />%4$s<input type="hidden" name="action" value="boi_populate" />%3$s</form>',
		esc_url( admin_url( 'admin-post.php' ) ),
		esc_attr( wp_create_nonce( 'boi_populate' ) ),
		get_submit_button( __( 'Populate content', 'bestofislam' ), 'secondary', 'submit', false, array( 'id' => 'boi-populate' ) ),
		wp_referer_field( false ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
	);
	boi_opt_card_close();

	if ( function_exists( 'boi_image_status' ) ) {
		list( $attached, $total ) = boi_image_status();

		boi_opt_card_open( __( 'Featured images', 'bestofislam' ) );
		printf(
			'<p><strong>%1$s</strong></p><p class="description">%2$s</p>',
			esc_html( sprintf( /* translators: 1: attached count, 2: total. */ __( '%1$d of %2$d copied into the media library.', 'bestofislam' ), $attached, $total ) ),
			esc_html__( 'Articles whose image is not yet in the media library show the copy bundled with the theme, so every article is illustrated either way. Copies are made three at a time as pages load. If the count does not rise, the server may be refusing uploads: check that wp-content/uploads is writable.', 'bestofislam' )
		);
		boi_opt_card_close();
	}

	if ( function_exists( 'boi_differing_articles' ) ) {
		boi_opt_card_open( __( 'Seeded articles', 'bestofislam' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['boi_refreshed'] ) ) {
			printf( '<div class="notice notice-success is-dismissible inline"><p>%s</p></div>', esc_html( sprintf( /* translators: %d: number of articles. */ _n( '%d article brought up to date.', '%d articles brought up to date.', absint( $_GET['boi_refreshed'] ), 'bestofislam' ), absint( $_GET['boi_refreshed'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$differing = boi_differing_articles();

		if ( empty( $differing ) ) {
			echo '<p>' . esc_html__( 'Every seeded article matches this version of the theme.', 'bestofislam' ) . '</p>';
		} else {
			echo '<p>' . esc_html__( 'These articles differ from this version of the theme. Updates refresh a seeded article automatically only when they can tell it has not been edited; for these they cannot, so they are left alone. Tick any you have not edited yourself to bring them up to date. Ticked articles are replaced with this version\'s text, and any edits in them are lost.', 'bestofislam' ) . '</p>';
			printf( '<form action="%1$s" method="post">', esc_url( admin_url( 'admin-post.php' ) ) );
			echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'boi_refresh_articles' ) ) . '" />';
			wp_referer_field();
			echo '<input type="hidden" name="action" value="boi_refresh_articles" /><ul class="boi-checklist">';

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
			submit_button( __( 'Update the ticked articles', 'bestofislam' ), 'secondary', 'submit', false, array( 'id' => 'boi-refresh-articles' ) );
			echo '</form>';
		}

		boi_opt_card_close();
	}
}

/**
 * Search engines tab.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_seo_tab( $opts ) {
	$key = BOI_OPTION_KEY;

	boi_opt_card_open( __( 'Descriptions and verification', 'bestofislam' ) );
	boi_opt_toggle(
		array(
			'key'     => 'seo_description',
			'label'   => __( 'Meta description', 'bestofislam' ),
			'text'    => __( 'Emit a description meta tag on every page', 'bestofislam' ),
			'help'    => __( 'Uses the post excerpt, or the section description on archives. Steps back automatically if an SEO plugin is active.', 'bestofislam' ),
			'checked' => ! empty( $opts['seo_description'] ),
		)
	);

	if ( function_exists( 'boi_seo_plugin_active' ) && boi_seo_plugin_active() ) {
		boi_opt_row_open( __( 'Webmaster tools', 'bestofislam' ) );
		echo '<p class="description">' . esc_html__( 'An SEO plugin is active, so verification codes are entered there.', 'bestofislam' ) . '</p>';
		boi_opt_row_close();
	} else {
		boi_opt_row_open( __( 'Google Search Console', 'bestofislam' ), 'boi-verify-google' );
		?>
		<input type="text" id="boi-verify-google" class="regular-text" name="<?php echo esc_attr( $key ); ?>[verify_google]" value="<?php echo esc_attr( $opts['verify_google'] ); ?>" aria-describedby="boi-verify-help" />
		<?php
		boi_opt_row_close();
		boi_opt_row_open( __( 'Bing Webmaster Tools', 'bestofislam' ), 'boi-verify-bing' );
		?>
		<input type="text" id="boi-verify-bing" class="regular-text" name="<?php echo esc_attr( $key ); ?>[verify_bing]" value="<?php echo esc_attr( $opts['verify_bing'] ); ?>" aria-describedby="boi-verify-help" />
		<p class="description" id="boi-verify-help"><?php esc_html_e( 'Paste the verification code, or the whole meta tag, from the console. The sitemap to submit is at /wp-sitemap.xml.', 'bestofislam' ); ?></p>
		<?php
		boi_opt_row_close();
	}
	boi_opt_card_close();

	boi_opt_card_open( __( 'Rank Math', 'bestofislam' ) );
	if ( function_exists( 'boi_rank_math_active' ) && boi_rank_math_active() ) {
		echo '<p>' . esc_html__( 'Rank Math is active. It owns titles, descriptions, canonical links, robots directives, the XML sitemap and the site-wide structured data. The theme adds only what Rank Math cannot know: each image\'s licence and credit, the images in the text of an article, search results and paged archives kept out of the index, unlisted articles kept out of the sitemap, and its list and question markup.', 'bestofislam' ) . '</p>';
	} else {
		echo '<p class="description">' . esc_html__( 'If you install Rank Math, the theme steps back from everything it would repeat and adds its image credits to Rank Math\'s structured data.', 'bestofislam' ) . '</p>';
	}
	boi_opt_card_close();

	boi_opt_card_open( __( 'Readable search addresses', 'bestofislam' ) );
	if ( function_exists( 'wpseosearch_base' ) ) {
		echo '<p class="description">' . esc_html__( 'The Pretty Search Permalinks plugin is active, so the theme leaves search addresses to it. Deactivate the plugin to manage them here; its base is carried over.', 'bestofislam' ) . '</p>';
	} else {
		boi_opt_toggle(
			array(
				'key'     => 'search_pretty',
				'label'   => __( 'Readable addresses', 'bestofislam' ),
				'text'    => __( 'Send searches to a readable address, such as /search/Paul/, keeping any filters', 'bestofislam' ),
				'checked' => ! empty( $opts['search_pretty'] ),
			)
		);
		boi_opt_row_open( __( 'Base word', 'bestofislam' ), 'boi-search-base' );
		?>
		<span class="boi-affix"><code><?php echo esc_html( trailingslashit( home_url() ) ); ?></code><input type="text" id="boi-search-base" class="regular-text" name="<?php echo esc_attr( $key ); ?>[search_base]" value="<?php echo esc_attr( boi_search_base() ); ?>" aria-describedby="boi-search-base-help" /><code>/Paul/</code></span>
		<p class="description" id="boi-search-base-help"><?php esc_html_e( 'The chosen base is also kept out of search engines through robots.txt.', 'bestofislam' ); ?></p>
		<?php
		boi_opt_row_close();
	}
	boi_opt_card_close();

	echo '<p class="boi-opt__note">' . esc_html__( 'Also handled by the theme: unique titles per page; canonical links on every indexable view; breadcrumbs; Article markup on every article, with licence metadata on its featured image and the images in its text; Organization and WebSite markup on the front page; ProfilePage on author pages; list and FAQ markup; search results kept out of the index and out of robots.txt; and an XML sitemap limited to articles, pages and sections.', 'bestofislam' ) . '</p>';
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

	boi_opt_card_open( __( 'Login address', 'bestofislam' ) );
	if ( defined( 'WPS_HIDE_LOGIN_VERSION' ) ) {
		echo '<div class="notice notice-info inline"><p>' . esc_html__( 'The WPS Hide Login plugin is active, so the theme leaves the login address to it. Deactivate the plugin to manage the address here; its address is carried over.', 'bestofislam' ) . '</p></div>';
	} elseif ( $override ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'The custom login address is switched off in wp-config.php (BOI_HIDE_LOGIN is false). The standard wp-login.php is in use.', 'bestofislam' ) . '</p></div>';
	}
	boi_opt_toggle(
		array(
			'key'     => 'login_enabled',
			'label'   => __( 'Custom login address', 'bestofislam' ),
			'text'    => __( 'Serve the login page from a private address, and answer wp-login.php and wp-admin with the 404 page for anyone not logged in', 'bestofislam' ),
			'checked' => ! empty( $opts['login_enabled'] ),
		)
	);
	boi_opt_row_open( __( 'Address', 'bestofislam' ), 'boi-login-slug' );
	?>
	<span class="boi-affix"><code><?php echo esc_html( trailingslashit( home_url() ) ); ?></code><input type="text" class="regular-text" id="boi-login-slug" name="<?php echo esc_attr( $key ); ?>[login_slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'for example, enter-archive', 'bestofislam' ); ?>" aria-describedby="boi-login-slug-help" /></span>
	<p class="description" id="boi-login-slug-help"><?php esc_html_e( 'At least six characters, and not an address any page, post or section uses. Bookmark the new address before saving.', 'bestofislam' ); ?></p>
	<?php if ( $active ) : ?>
		<p><strong><?php esc_html_e( 'In force:', 'bestofislam' ); ?></strong> <a href="<?php echo esc_url( boi_login_url_base() ); ?>"><?php echo esc_html( boi_login_url_base() ); ?></a></p>
	<?php endif; ?>
	<p class="description"><?php echo wp_kses( __( 'Locked out? Add <code>define( \'BOI_HIDE_LOGIN\', false );</code> to wp-config.php and log in at wp-login.php as usual.', 'bestofislam' ), array( 'code' => array() ) ); ?></p>
	<?php
	boi_opt_row_close();
	boi_opt_card_close();

	boi_opt_card_open( __( 'Security headers', 'bestofislam' ) );
	boi_opt_toggle(
		array(
			'key'     => 'security_headers',
			'label'   => __( 'Framing, file types, referrers', 'bestofislam' ),
			'text'    => __( 'Send X-Frame-Options, a frame-ancestors policy, Cross-Origin-Opener-Policy, X-Content-Type-Options and Referrer-Policy', 'bestofislam' ),
			'help'    => __( 'Other sites can no longer show these pages in a frame. Scripts, styles and embeds are not restricted.', 'bestofislam' ),
			'checked' => ! empty( $opts['security_headers'] ),
		)
	);
	boi_opt_toggle(
		array(
			'key'     => 'security_hsts',
			'label'   => __( 'HTTPS only (HSTS)', 'bestofislam' ),
			'text'    => __( 'Send Strict-Transport-Security for one year', 'bestofislam' ),
			'help'    => __( 'Switch this on only when every address on the site works over HTTPS. Browsers that have seen it refuse plain HTTP for a year, even after it is switched off here.', 'bestofislam' ),
			'checked' => ! empty( $opts['security_hsts'] ),
		)
	);
	boi_opt_card_close();

	boi_opt_card_open( __( 'Unlisted posts and pages', 'bestofislam' ) );
	if ( class_exists( 'Unlist_Posts' ) ) {
		echo '<div class="notice notice-info inline"><p>' . esc_html__( 'The Unlist Posts & Pages plugin is active, so the theme leaves unlisting to it. Deactivate the plugin to manage unlisting here; its list is carried over.', 'bestofislam' ) . '</p></div>';
	} else {
		echo '<p class="description">' . esc_html__( 'Unlist an item from the Visibility in listings box in its editor sidebar. An unlisted item opens from its own link but appears in no listing, search, feed or sitemap, and asks search engines not to index it.', 'bestofislam' ) . '</p>';

		$ids = boi_unlisted_ids();

		if ( empty( $ids ) ) {
			echo '<p>' . esc_html__( 'Nothing is unlisted.', 'bestofislam' ) . '</p>';
		} else {
			echo '<ul class="ul-disc">';

			foreach ( $ids as $id ) {
				$post = get_post( $id );

				if ( $post ) {
					printf( '<li><a href="%1$s">%2$s</a> <span class="description">(%3$s)</span></li>', esc_url( (string) get_edit_post_link( $id ) ), esc_html( get_the_title( $post ) ), esc_html( get_post_type_object( $post->post_type )->labels->singular_name ) );
				}
			}

			echo '</ul>';
		}
	}
	boi_opt_card_close();
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
