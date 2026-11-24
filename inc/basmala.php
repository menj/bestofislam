<?php
/**
 * The Basmala at the head of every article.
 *
 * "In the name of God, the Most Merciful, the Most Compassionate" (1:1), as
 * the owner's public-domain calligraphy (assets/images/basmala.webp, black
 * lettering with the word Allah in red; docs/CREDITS.md), above the
 * abstract and the opening paragraph. In the dark appearance the image is
 * inverted with its hue turned back, so the black reads light and the red
 * stays red, a lighter rose (assets/css/basmala.css). The alt text gives the meaning.
 *
 * Where it goes. On a single post, at the very top of the main text, above
 * the abstract box: inc/abstract.php runs at priority 30 and this at 31, and
 * each prepends. Never on a page. The wrapper is a <div>, so the drop cap,
 * which takes the first <p> child of the content, stays on the opening
 * paragraph.
 *
 * Setting. Theme Options, Display: on or off (`basmala`, on by default).
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Basmala.
 *
 * @return string
 */
function boi_basmala_html() {
	return '<div class="boi-basmala">'
		. '<img class="boi-basmala__image" src="' . esc_url( BOI_URI . '/assets/images/basmala.webp' ) . '" width="270" height="32" alt="' . esc_attr__( 'In the name of God, the Most Merciful, the Most Compassionate', 'bestofislam' ) . '" decoding="async" />'
		. '</div>';
}

/**
 * Whether the Basmala is shown on this view.
 *
 * @return bool
 */
function boi_basmala_on() {
	$opts = boi_get_options();
	return is_singular( 'post' ) && ( ! isset( $opts['basmala'] ) || ! empty( $opts['basmala'] ) );
}

/**
 * Puts the Basmala at the top of the main text of an article.
 *
 * @param string $content Content.
 * @return string
 */
function boi_basmala_filter( $content ) {
	if ( ! boi_basmala_on() || ! function_exists( 'boi_anatomy_is_main' ) || ! boi_anatomy_is_main() || false !== strpos( $content, 'class="boi-basmala"' ) ) {
		return $content;
	}
	return boi_basmala_html() . $content;
}
add_filter( 'the_content', 'boi_basmala_filter', 31 );

/** The stylesheet, on single posts. */
function boi_basmala_assets() {
	if ( ! boi_basmala_on() ) {
		return;
	}
	wp_enqueue_style( 'boi-basmala', BOI_URI . '/assets/css/basmala.css', array( 'boi-article' ), boi_asset_version( '/assets/css/basmala.css' ) );
}
add_action( 'wp_enqueue_scripts', 'boi_basmala_assets', 20 );
