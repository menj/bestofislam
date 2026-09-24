<?php
/**
 * Icons.
 *
 * Social and platform icons from the Minimalist Social & Platform Icons Pack
 * (GPL), bundled in assets/icons/ as single-colour 24x24 SVGs. RSS and email
 * are drawn to the same grid for this theme. The LinkedIn and Scribd icons
 * are deliberately omitted: they derive from Font Awesome under CC BY 4.0,
 * which would oblige a visible credit.
 *
 * Icons are inlined, filled with currentColor, so each takes the colour of
 * the text around it in either appearance.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns an icon as inline SVG, decorative by default.
 *
 * @param string $slug  Icon file name without extension.
 * @param string $class Extra class for the SVG.
 * @return string Empty when the icon does not exist.
 */
function boi_icon( $slug, $class = '' ) {
	static $cache = array();

	$slug = sanitize_key( $slug );

	if ( ! isset( $cache[ $slug ] ) ) {
		$file = BOI_DIR . '/assets/icons/' . $slug . '.svg';
		$svg  = file_exists( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		// Decorative: the link around the icon carries the accessible name.
		$svg = preg_replace( '#<title>.*?</title>#s', '', $svg );
		$svg = preg_replace( '#\srole="img"#', '', $svg );

		$cache[ $slug ] = trim( $svg );
	}

	if ( '' === $cache[ $slug ] ) {
		return '';
	}

	return preg_replace(
		'#^<svg\b#',
		'<svg class="boi-icon' . ( '' !== $class ? ' ' . esc_attr( $class ) : '' ) . '" fill="currentColor" aria-hidden="true" focusable="false" width="1em" height="1em"',
		$cache[ $slug ],
		1
	);
}

/**
 * The networks a reader can follow the site on, in display order. Each key
 * matches an icon and a follow_{key} setting.
 *
 * @return array Key => label.
 */
function boi_follow_networks() {
	return array(
		'youtube'   => 'YouTube',
		'x'         => 'X',
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'threads'   => 'Threads',
		'bluesky'   => 'Bluesky',
		'mastodon'  => 'Mastodon',
		'telegram'  => 'Telegram',
		'whatsapp'  => 'WhatsApp',
	);
}

/**
 * The follow links in use: RSS always, then every network with a URL set.
 *
 * @return array List of array( key, label, url ).
 */
function boi_follow_links() {
	$options = boi_get_options();
	$links   = array( array( 'rss', __( 'RSS feed', 'bestofislam' ), get_feed_link() ) );

	foreach ( boi_follow_networks() as $key => $label ) {
		if ( ! empty( $options[ 'follow_' . $key ] ) ) {
			$links[] = array( $key, $label, $options[ 'follow_' . $key ] );
		}
	}

	return $links;
}

/**
 * Registers the footer icon row.
 *
 * @return void
 */
function boi_register_icon_blocks() {
	register_block_type( 'bestofislam/social-icons', array( 'render_callback' => 'boi_render_social_icons' ) );
}
add_action( 'init', 'boi_register_icon_blocks' );

/**
 * The icon-only row in the footer. Mastodon links carry rel="me", which lets
 * a Mastodon profile verify the site.
 *
 * @return string
 */
function boi_render_social_icons() {
	$items = '';

	foreach ( boi_follow_links() as $link ) {
		$items .= sprintf(
			'<li><a class="boi-social__link" href="%1$s" rel="%2$s" aria-label="%3$s" title="%3$s">%4$s</a></li>',
			esc_url( $link[2] ),
			'mastodon' === $link[0] ? 'me noopener' : 'noopener',
			esc_attr( $link[1] ),
			boi_icon( $link[0] )
		);
	}

	return '<ul class="boi-social">' . $items . '</ul>';
}
