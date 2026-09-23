<?php
/**
 * Appearance toggle.
 *
 * The control flips a data-theme attribute on the document element. Token
 * overrides in assets/css/theme-toggle.css do the rest, so the toggle recolours
 * the whole page, listicle tokens included, without a second stylesheet load.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the toggle block.
 *
 * @return void
 */
function boi_register_toggle_block() {
	register_block_type(
		BOI_DIR . '/blocks/theme-toggle',
		array( 'render_callback' => 'boi_render_toggle' )
	);
}
add_action( 'init', 'boi_register_toggle_block' );

/**
 * Renders the toggle.
 *
 * Both glyphs are present in the markup. The moving block covers one of them,
 * so the control reads as a state rather than as an instruction.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function boi_render_toggle( $attributes ) {
	$show_label = ! empty( $attributes['showLabel'] );

	$sun = '<svg class="boi-appearance__glyph" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
		. '<circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="1.6" />'
		. '<path d="M12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M5.2 5.2l2.1 2.1M16.7 16.7l2.1 2.1M18.8 5.2l-2.1 2.1M7.3 16.7l-2.1 2.1" '
		. 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />'
		. '</svg>';

	$moon = '<svg class="boi-appearance__glyph" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
		. '<path d="M20 14.2A8.4 8.4 0 0 1 9.8 4a8.4 8.4 0 1 0 10.2 10.2Z" fill="none" stroke="currentColor" stroke-width="1.6" '
		. 'stroke-linejoin="round" />'
		. '</svg>';

	$label = $show_label
		? sprintf( '<span class="boi-appearance__label">%s</span>', esc_html__( 'Appearance', 'bestofislam' ) )
		: '';

	$wrapper = get_block_wrapper_attributes( array( 'class' => 'boi-appearance' ) );

	return sprintf(
		'<div %1$s>%2$s<button type="button" class="boi-appearance__frame" role="switch" aria-checked="false" aria-label="%3$s" data-boi-appearance>
			<span class="boi-appearance__block" aria-hidden="true"></span>
			<span class="boi-appearance__cell is-light">%4$s</span>
			<span class="boi-appearance__cell is-dark">%5$s</span>
		</button></div>',
		$wrapper,
		$label,
		esc_attr__( 'Switch to dark appearance', 'bestofislam' ),
		$sun,
		$moon
	);
}

/**
 * Prints the pre-paint script that applies the stored preference.
 *
 * This runs in the head, before the body renders, so the page never flashes
 * the light palette on its way to dark.
 *
 * @return void
 */
function boi_print_appearance_bootstrap() {
	?>
	<script>
	( function () {
		try {
			var stored = window.localStorage.getItem( 'boi-appearance' );
			var dark = stored ? stored === 'dark' : window.matchMedia( '(prefers-color-scheme: dark)' ).matches;
			document.documentElement.setAttribute( 'data-theme', dark ? 'dark' : 'light' );
		} catch ( error ) {
			document.documentElement.setAttribute( 'data-theme', 'light' );
		}
	} )();
	</script>
	<?php
}
add_action( 'wp_head', 'boi_print_appearance_bootstrap', 1 );
