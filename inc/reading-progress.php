<?php
/**
 * Reading progress bar.
 *
 * A thin bar along the top or bottom edge of a single post or page that
 * fills as the reader scrolls. Colour, height, edge, the post types it runs
 * on and a master switch live in the Display tab of Theme Options.
 *
 * Adapted from the Reading Progress Bar plugin by Jean-Baptiste Audras
 * (GPLv2). The plugin needed jQuery and read the page height once on load;
 * this version is plain JavaScript, measures again when the page resizes,
 * updates once per animation frame from a passive scroll listener, and draws
 * the bar in the theme's palette.
 *
 * The bar is aria-hidden. It repeats what the browser's scrollbar already
 * tells every reader, it cannot be focused or operated, and a progressbar
 * role would make a screen reader announce a new value on every scroll.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Colours the bar can take: palette slugs from theme.json, so the bar
 * follows the light and dark appearance.
 *
 * @return array<string, string> Slug => label.
 */
function boi_progress_palette() {
	return array(
		'accent-2' => __( 'Gold', 'bestofislam' ),
		'accent-1' => __( 'Azure', 'bestofislam' ),
		'accent-4' => __( 'Sky', 'bestofislam' ),
		'contrast' => __( 'Navy', 'bestofislam' ),
	);
}

/**
 * Whether the theme's bar runs. It stands aside while the original plugin
 * is active, so a page never shows two bars.
 *
 * @return bool
 */
function boi_progress_enabled() {
	if ( function_exists( 'rp_show_it' ) ) {
		return false;
	}

	$options = boi_get_options();

	return ! empty( $options['progress_enabled'] );
}

/**
 * Post types the bar runs on, from the saved setting.
 *
 * @return string[]
 */
function boi_progress_post_types() {
	$options = boi_get_options();
	$types   = is_array( $options['progress_types'] ) ? $options['progress_types'] : explode( ',', (string) $options['progress_types'] );

	return array_values( array_filter( array_map( 'sanitize_key', $types ) ) );
}

/**
 * Whether the current view gets the bar: a single post or page of a chosen
 * type, never the front page.
 *
 * @return bool
 */
function boi_progress_applies() {
	if ( ! boi_progress_enabled() || is_admin() || is_front_page() || ! is_singular() ) {
		return false;
	}

	return in_array( get_post_type( get_queried_object_id() ), boi_progress_post_types(), true );
}

/**
 * The CSS colour value for the saved setting: a palette variable, or a hex
 * colour chosen with the picker.
 *
 * @return string
 */
function boi_progress_color() {
	$options = boi_get_options();
	$color   = (string) $options['progress_color'];

	if ( isset( boi_progress_palette()[ $color ] ) ) {
		return 'var(--wp--preset--color--' . $color . ')';
	}

	$hex = sanitize_hex_color( $color );

	return $hex ? $hex : 'var(--wp--preset--color--accent-2)';
}

/**
 * Loads the bar's style and script on the views that show it.
 *
 * @return void
 */
function boi_progress_assets() {
	if ( ! boi_progress_applies() ) {
		return;
	}

	$options = boi_get_options();

	wp_enqueue_style( 'boi-reading-progress', BOI_URI . '/assets/css/reading-progress.css', array(), boi_asset_version( '/assets/css/reading-progress.css' ) );
	wp_add_inline_style(
		'boi-reading-progress',
		sprintf(
			'.boi-progress{--boi-progress-color:%1$s;--boi-progress-height:%2$dpx}',
			boi_progress_color(),
			max( 2, min( 12, (int) $options['progress_height'] ) )
		)
	);
	wp_enqueue_script( 'boi-reading-progress', BOI_URI . '/assets/js/reading-progress.js', array(), boi_asset_version( '/assets/js/reading-progress.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'boi_progress_assets' );

/**
 * Prints the bar at the start of the body.
 *
 * @return void
 */
function boi_progress_render() {
	if ( ! boi_progress_applies() ) {
		return;
	}

	$options  = boi_get_options();
	$position = 'bottom' === $options['progress_position'] ? 'bottom' : 'top';

	printf( '<div class="boi-progress boi-progress--%s" aria-hidden="true"><div class="boi-progress__fill"></div></div>', esc_attr( $position ) );
}
add_action( 'wp_body_open', 'boi_progress_render', 5 );

/**
 * The Reading progress card on the Display tab of Theme Options: a switch,
 * the edge, a slider for the height, colour cards with a custom picker, and
 * the post types.
 *
 * @param array $opts Current options.
 * @return void
 */
function boi_render_progress_card( $opts ) {
	boi_opt_card_open( __( 'Reading progress bar', 'bestofislam' ), __( 'A thin bar on single posts and pages that fills as the reader scrolls.', 'bestofislam' ) );

	if ( function_exists( 'rp_show_it' ) ) {
		echo '<p class="description">' . esc_html__( 'The Reading Progress Bar plugin is active, so the theme leaves the bar to it. Deactivate the plugin to manage the bar here.', 'bestofislam' ) . '</p>';
		boi_opt_card_close();
		return;
	}

	$key   = BOI_OPTION_KEY;
	$color = (string) $opts['progress_color'];
	$is_palette = isset( boi_progress_palette()[ $color ] );
	$custom     = $is_palette ? '#e8b33c' : (string) sanitize_hex_color( $color );

	boi_opt_toggle(
		array(
			'key'     => 'progress_enabled',
			'label'   => __( 'Progress bar', 'bestofislam' ),
			'text'    => __( 'Show the bar on the post types ticked below', 'bestofislam' ),
			'checked' => ! empty( $opts['progress_enabled'] ),
		)
	);
	boi_opt_choices(
		array(
			'key'     => 'progress_position',
			'label'   => __( 'Edge', 'bestofislam' ),
			'value'   => 'bottom' === $opts['progress_position'] ? 'bottom' : 'top',
			'choices' => array(
				'top'    => array( __( 'Top', 'bestofislam' ), __( 'Along the top of the window, below the admin bar.', 'bestofislam' ) ),
				'bottom' => array( __( 'Bottom', 'bestofislam' ), __( 'Along the bottom of the window.', 'bestofislam' ) ),
			),
			'help'    => __( 'The bar never covers the admin bar.', 'bestofislam' ),
		)
	);
	boi_opt_range(
		array(
			'key'     => 'progress_height',
			'label'   => __( 'Height', 'bestofislam' ),
			'value'   => (int) $opts['progress_height'],
			'min'     => 2,
			'max'     => 12,
			'unit'    => __( 'px', 'bestofislam' ),
			'help'    => __( 'From 2 to 12 pixels.', 'bestofislam' ),
			'depends' => 'boi-progress-enabled',
		)
	);
	?>
	<fieldset class="boi-field boi-field--choices" data-boi-depends="boi-progress-enabled" aria-describedby="boi-progress-color-help">
		<legend class="boi-field__label"><?php esc_html_e( 'Colour', 'bestofislam' ); ?></legend>
		<div class="boi-field__control">
			<div class="boi-choices">
				<?php foreach ( boi_progress_palette() as $slug => $label ) : ?>
					<label class="boi-choice">
						<input type="radio" name="<?php echo esc_attr( $key ); ?>[progress_color]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $color, $slug ); ?> />
						<span class="boi-choice__title"><span class="boi-swatch" style="background:var(--boi-admin-<?php echo esc_attr( $slug ); ?>)" aria-hidden="true"></span><?php echo esc_html( $label ); ?></span>
						<span class="boi-choice__desc"><?php esc_html_e( 'From the palette; follows the light and dark appearance.', 'bestofislam' ); ?></span>
					</label>
				<?php endforeach; ?>
				<label class="boi-choice">
					<input type="radio" name="<?php echo esc_attr( $key ); ?>[progress_color]" value="custom" <?php checked( ! $is_palette ); ?> />
					<span class="boi-choice__title"><?php esc_html_e( 'Custom', 'bestofislam' ); ?></span>
					<span class="boi-choice__desc"><input type="color" class="boi-color" name="<?php echo esc_attr( $key ); ?>[progress_color_custom]" value="<?php echo esc_attr( $custom ? $custom : '#e8b33c' ); ?>" aria-label="<?php esc_attr_e( 'Custom colour', 'bestofislam' ); ?>" /></span>
				</label>
			</div>
			<p class="description" id="boi-progress-color-help"><?php esc_html_e( 'A custom colour stays the same in both appearances.', 'bestofislam' ); ?></p>
		</div>
	</fieldset>
	<?php
	$types = boi_progress_post_types();
	boi_opt_row_open( __( 'Post types', 'bestofislam' ) );
	echo '<ul class="boi-checklist">';
	foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
		if ( 'attachment' === $type->name ) {
			continue;
		}
		printf(
			'<li><label><input type="checkbox" name="%1$s[progress_types][]" value="%2$s"%3$s /> %4$s</label></li>',
			esc_attr( $key ),
			esc_attr( $type->name ),
			checked( in_array( $type->name, $types, true ), true, false ),
			esc_html( $type->labels->name )
		);
	}
	echo '</ul>';
	boi_opt_row_close();
	boi_opt_card_close();
}
