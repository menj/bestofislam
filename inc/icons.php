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

/**
 * Interface icons from Lucide (ISC licence, assets/icons/ui/LICENSE.txt),
 * stroked in currentColor. Used beside headings and labels.
 *
 * @param string $slug Icon file name in assets/icons/ui/, without extension.
 * @return string Inline SVG, hidden from assistive technology, or empty.
 */
function boi_ui_icon( $slug ) {
	static $cache = array();

	$slug = sanitize_key( $slug );

	if ( ! isset( $cache[ $slug ] ) ) {
		$file = BOI_DIR . '/assets/icons/ui/' . $slug . '.svg';
		$svg  = file_exists( $file ) ? trim( (string) file_get_contents( $file ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( '' !== $svg ) {
			$svg = preg_replace( '#\sclass="[^"]*"#', '', $svg, 1 );
			$svg = preg_replace( '#\s(width|height)="24"#', '', $svg );
			$svg = str_replace( '<svg', '<svg class="boi-hicon" aria-hidden="true" focusable="false"', $svg );
		}

		$cache[ $slug ] = $svg;
	}

	return $cache[ $slug ];
}

/**
 * The icon that belongs to a heading or label, by its text.
 *
 * @param string $text Heading text.
 * @return string Icon slug, or empty.
 */
function boi_heading_icon_slug( $text ) {
	$map = array(
		'the bottom line'        => 'scale',
		'notes'                  => 'notebook-pen',
		'the reading order'      => 'list-ordered',
		'sections'               => 'layout-grid',
		'start'                  => 'compass',
		'follow'                 => 'rss',
		'related'                => 'link',
		'terms'                  => 'tags',
		'read more'              => 'book-open',
		'people'                 => 'users',
		'often misunderstood as' => 'circle-alert',
		'sources'                => 'library',
		'further reading'        => 'library',
		'questions answered'     => 'message-circle-question',
		'latest'                 => 'newspaper',
		'start here'             => 'compass',
		'a first visit, in three steps' => 'compass',
		'browse by section'      => 'layout-grid',
		'recent'                 => 'newspaper',
		'recently published'     => 'newspaper',
		'the newest argument'    => 'sparkles',
		'the questions most often put to muslims' => 'message-circle-question',
		'where to begin if you read only three' => 'lightbulb',
		'reflections'            => 'notebook-pen',
	);

	$key = strtolower( trim( wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES ) ) ) );

	return isset( $map[ $key ] ) ? $map[ $key ] : '';
}

/**
 * Adds the matching icon to a heading element's markup.
 *
 * @param string $html Markup containing one heading or label element.
 * @return string
 */
function boi_add_heading_icon( $html ) {
	return preg_replace_callback(
		'#(<(h[1-6]|p)\b[^>]*>)(.*?)(</\2>)#s',
		function ( $m ) {
			if ( false !== strpos( $m[3], 'boi-hicon' ) ) {
				return $m[0];
			}

			$slug = boi_heading_icon_slug( $m[3] );
			$icon = '' !== $slug ? boi_ui_icon( $slug ) : '';

			if ( '' === $icon ) {
				return $m[0];
			}

			$open = preg_match( '#\bclass="#', $m[1] ) ? preg_replace( '#\bclass="#', 'class="has-hicon ', $m[1], 1 ) : str_replace( '>', ' class="has-hicon">', $m[1] );

			return $open . $icon . '<span>' . $m[3] . '</span>' . $m[4];
		},
		$html,
		1
	);
}

/**
 * Heading blocks: icons for the standing headings (The bottom line, Notes,
 * the footer's column labels).
 *
 * @param string $content Block markup.
 * @param array  $block   Parsed block.
 * @return string
 */
function boi_heading_block_icons( $content, $block ) {
	if ( 'core/heading' !== $block['blockName'] || is_admin() ) {
		return $content;
	}

	return boi_add_heading_icon( $content );
}
add_filter( 'render_block', 'boi_heading_block_icons', 10, 2 );
