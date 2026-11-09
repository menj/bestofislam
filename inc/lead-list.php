<?php
/**
 * Lead and list: the newest piece large on the left, the next five as a
 * stacked list on the right, above the grid on the first page of Reflections,
 * of each section and of each archive. The bestofislam/lead-list block draws
 * it; with fewer than five pieces, or a lead with neither a featured image nor
 * a bundled one, it draws nothing and the grid shows as before. The image
 * helpers are the ones the front page's featured lead and "Keep reading" use
 * (inc/features.php), and the credit is the image-credit block's
 * (inc/images.php).
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/** How many pieces the list beside the lead holds. */
const BOI_LEAD_LIST_MAX = 5;

/** The fewest pieces the layout needs, the lead included. */
const BOI_LEAD_LIST_MIN = 5;

/**
 * Draw the lead and list.
 *
 * @param WP_Post[] $posts Pieces, newest first.
 * @param array     $args  {
 *     @type string $heading    Section heading.
 *     @type string $heading_id ID of the heading, for aria-labelledby.
 *     @type int    $lead_id    Piece to lead with; the newest leads when it is 0 or not usable.
 *     @type int    $max        Pieces in the list (at most five).
 * }
 * @return array { html: string, ids: int[] } html is '' when the layout does not apply.
 */
function boi_lead_list( array $posts, array $args = array() ) {
	$args  = wp_parse_args(
		$args,
		array(
			'heading'    => __( 'Latest reflections', 'bestofislam' ),
			'heading_id' => 'boi-lead-list-heading',
			'lead_id'    => 0,
			'max'        => BOI_LEAD_LIST_MAX,
		)
	);
	$empty = array(
		'html' => '',
		'ids'  => array(),
	);

	$posts = array_values(
		array_filter(
			array_map( 'get_post', $posts ),
			static function ( $p ) {
				return $p instanceof WP_Post && 'publish' === $p->post_status && ! ( function_exists( 'boi_is_unlisted' ) && boi_is_unlisted( $p ) );
			}
		)
	);

	$lead    = null;
	$lead_id = (int) $args['lead_id'];
	if ( $lead_id ) {
		foreach ( $posts as $i => $p ) {
			if ( $p->ID === $lead_id ) {
				$lead = $p;
				unset( $posts[ $i ] );
				break;
			}
		}
		if ( ! $lead ) {
			$chosen = get_post( $lead_id );
			if ( $chosen instanceof WP_Post && 'publish' === $chosen->post_status && ! ( function_exists( 'boi_is_unlisted' ) && boi_is_unlisted( $chosen ) ) ) {
				$lead = $chosen;
			}
		}
	}
	if ( ! $lead ) {
		$lead = array_shift( $posts );
	}
	$list = array_slice( array_values( $posts ), 0, max( 1, min( BOI_LEAD_LIST_MAX, (int) $args['max'] ) ) );

	// The bundled image counts as the lead's photograph; the drawn placeholder does not.
	if ( ! $lead || 1 + count( $list ) < BOI_LEAD_LIST_MIN || ! ( has_post_thumbnail( $lead ) || ( function_exists( 'boi_bundled_image' ) && boi_bundled_image( $lead ) ) ) ) {
		return $empty;
	}

	$heading_id = sanitize_html_class( $args['heading_id'] );
	$lead_img   = boi_lead_image( $lead, '(max-width: 781px) calc(100vw - 2rem), 60vw', array( 'class' => 'boi-lead-list__img' ) );
	if ( '' === $lead_img || false !== strpos( $lead_img, 'boi-placeholder' ) ) {
		return $empty;
	}
	$credit = boi_image_credit_markup( $lead );
	$set    = boi_lead_list_settings();

	$classes = 'boi-lead-list alignwide' . ( $set['motion'] ? '' : ' boi-lead-list--calm' );
	$style   = '' !== $set['accent'] ? ' style="--boi-ll-accent:' . esc_attr( $set['accent'] ) . '"' : '';

	$html  = '<section class="' . esc_attr( $classes ) . '" aria-labelledby="' . esc_attr( $heading_id ) . '"' . $style . '>';
	$html .= '<h2 class="boi-lead-list__heading" id="' . esc_attr( $heading_id ) . '">' . esc_html( $args['heading'] ) . '</h2>';
	$html .= '<div class="boi-lead-list__grid">';
	$html .= '<article class="boi-lead-list__lead"><figure class="boi-lead-list__figure">';
	$html .= '<div class="boi-lead-list__media">' . $lead_img;
	$html .= '<div class="boi-lead-list__overlay"><span class="boi-lead-list__rule" aria-hidden="true"></span>';
	$html .= $set['tabs'] ? boi_lead_list_tab( $lead ) : '';
	$html .= '<h3 class="boi-lead-list__title"><a class="boi-lead-list__link" href="' . esc_url( get_permalink( $lead ) ) . '">' . esc_html( get_the_title( $lead ) ) . '</a></h3>';
	$html .= boi_lead_list_meta( $lead );
	$html .= '<span class="boi-lead-list__read" aria-hidden="true">' . esc_html__( 'Read', 'bestofislam' ) . ' <span class="boi-lead-list__arrow">&rarr;</span></span>';
	$html .= '</div></div>';
	if ( '' !== $credit ) {
		$html .= '<figcaption class="boi-lead-list__credit">' . $credit . '</figcaption>';
	}
	$html .= '</figure></article>';

	$html .= '<ul class="boi-lead-list__list">';
	foreach ( $list as $n => $item ) {
		$thumb = boi_list_thumbnail(
			$item,
			array(
				'class' => 'boi-lead-list__thumb-img',
				'alt'   => '',
				'sizes' => '(max-width: 781px) 40vw, 12rem',
			)
		);
		$html .= '<li class="boi-lead-list__item' . ( 0 === $n ? ' is-current' : '' ) . '" style="--boi-ll-i:' . (int) $n . '">';
		$html .= '<span class="boi-lead-list__thumb" aria-hidden="true">' . $thumb . '</span>';
		$html .= '<div class="boi-lead-list__text">';
		$html .= $set['tabs'] ? boi_lead_list_tab( $item ) : '';
		$html .= '<h3 class="boi-lead-list__item-title"><a class="boi-lead-list__link" href="' . esc_url( get_permalink( $item ) ) . '">' . esc_html( get_the_title( $item ) ) . '</a></h3>';
		$html .= boi_lead_list_meta( $item );
		$html .= '</div>';
		$html .= '<span class="boi-lead-list__arrow" aria-hidden="true">&rarr;</span>';
		$html .= '</li>';
	}
	$html .= '</ul></div></section>';

	return array(
		'html' => $html,
		'ids'  => array_merge( array( $lead->ID ), wp_list_pluck( $list, 'ID' ) ),
	);
}

/**
 * The lead and list settings: the accent colour override, the category tabs
 * and the arrows and motion. The Display tab of the Theme Options screen
 * saves them; each has a filter.
 *
 * @return array { accent: string, tabs: bool, motion: bool }
 */
function boi_lead_list_settings() {
	$opts   = function_exists( 'boi_get_options' ) ? boi_get_options() : array();
	$accent = isset( $opts['ll_accent'] ) ? (string) $opts['ll_accent'] : '';
	$accent = (string) apply_filters( 'boi_lead_list_accent', $accent );

	return array(
		'accent' => preg_match( '/^#[0-9a-f]{6}$/i', $accent ) ? strtolower( $accent ) : '',
		'tabs'   => (bool) apply_filters( 'boi_lead_list_show_tabs', ! isset( $opts['ll_tabs'] ) || ! empty( $opts['ll_tabs'] ) ),
		'motion' => (bool) apply_filters( 'boi_lead_list_motion', ! isset( $opts['ll_motion'] ) || ! empty( $opts['ll_motion'] ) ),
	);
}

/**
 * Relative luminance of a hex colour.
 *
 * @param string $hex Colour, #rrggbb.
 * @return float
 */
function boi_lead_list_luminance( $hex ) {
	$hex = ltrim( $hex, '#' );
	$out = 0.0;
	foreach ( array( array( 0.2126, 0 ), array( 0.7152, 2 ), array( 0.0722, 4 ) ) as list( $weight, $at ) ) {
		$c    = hexdec( substr( $hex, $at, 2 ) ) / 255;
		$out += $weight * ( $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 ) );
	}
	return $out;
}

/**
 * Mix two hex colours, the first at $share percent.
 *
 * @param string $a     First colour.
 * @param string $b     Second colour.
 * @param int    $share Percent of the first.
 * @return string
 */
function boi_lead_list_mix( $a, $b, $share ) {
	$a   = ltrim( $a, '#' );
	$b   = ltrim( $b, '#' );
	$out = '#';
	foreach ( array( 0, 2, 4 ) as $at ) {
		$out .= str_pad( dechex( (int) round( hexdec( substr( $a, $at, 2 ) ) * $share / 100 + hexdec( substr( $b, $at, 2 ) ) * ( 100 - $share ) / 100 ) ), 2, '0', STR_PAD_LEFT );
	}
	return $out;
}

/**
 * The contrast ratio of two hex colours.
 *
 * @param string $a First colour.
 * @param string $b Second colour.
 * @return float
 */
function boi_lead_list_ratio( $a, $b ) {
	$la = boi_lead_list_luminance( $a );
	$lb = boi_lead_list_luminance( $b );
	return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
}

/**
 * Whether an accent override can carry the hovered title at 4.5:1 on the
 * hover tint, in the light and the dark appearance. The stylesheet mixes the
 * accent 70% with the contrast colour for text, and 9% with the base for the
 * tint, so this does the same sums with the theme's own values.
 *
 * @param string $hex Colour, #rrggbb.
 * @return bool
 */
function boi_lead_list_accent_ok( $hex ) {
	foreach ( array( array( '#ffffff', '#0d2b4e' ), array( '#0a2038', '#eaf2fb' ) ) as $mode ) {
		$text = boi_lead_list_mix( $hex, $mode[1], 70 );
		$tint = boi_lead_list_mix( $hex, $mode[0], 9 );
		if ( boi_lead_list_ratio( $text, $tint ) < 4.5 ) {
			return false;
		}
	}
	return true;
}

/** How many colours the category tabs rotate through (see lead-list.css). */
const BOI_LEAD_LIST_TAB_COLOURS = 5;

/**
 * The category tab of a piece: a pill that names its first category and
 * links to that category's archive. The ten sections have a fixed colour, two
 * to a colour; any other category takes its slug's hash. A category keeps
 * one colour everywhere.
 *
 * @param WP_Post $post Piece.
 * @return string Markup, or '' when the piece has no category.
 */
function boi_lead_list_tab( $post ) {
	$cats = get_the_category( $post->ID );
	$cats = is_array( $cats ) ? $cats : array();
	$cat  = null;
	foreach ( $cats as $c ) {
		if ( 'uncategorized' !== $c->slug ) {
			$cat = $c;
			break;
		}
	}
	if ( ! $cat ) {
		return '';
	}
	$map    = array(
		'theology'          => 0,
		'politics-today'    => 0,
		'scripture'         => 1,
		'archaeology'       => 1,
		'history'           => 2,
		'interfaith-studies' => 2,
		'religion'          => 3,
		'philosophy'        => 3,
		'culture'           => 4,
		'science-and-reason' => 4,
	);
	$colour = isset( $map[ $cat->slug ] ) ? $map[ $cat->slug ] : abs( crc32( (string) $cat->slug ) ) % BOI_LEAD_LIST_TAB_COLOURS;

	return '<a class="boi-lead-list__tab boi-lead-list__tab--c' . (int) $colour . '" href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
}

/**
 * The line under a title: the date and the reading time at 220 words a
 * minute, the rate the reading-time block uses.
 *
 * @param WP_Post $post Piece.
 * @return string
 */
function boi_lead_list_meta( $post ) {
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) ) );
	$minutes = max( 1, (int) round( $words / 220 ) );

	return '<p class="boi-lead-list__meta"><time datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( 'j M Y', $post ) ) . '</time>'
		. '<span class="boi-lead-list__sep" aria-hidden="true">&middot;</span>'
		/* translators: %d: minutes. */
		. '<span>' . esc_html( sprintf( _n( '%d min read', '%d min read', $minutes, 'bestofislam' ), $minutes ) ) . '</span></p>';
}

/**
 * Pieces the lead and list showed on this request, so the grid below can
 * leave them out.
 *
 * @param int[]|null $ids IDs to record, or null to read.
 * @return int[]
 */
function boi_lead_list_shown( $ids = null ) {
	static $shown = array();
	if ( null !== $ids ) {
		$shown = array_map( 'intval', $ids );
	}
	return $shown;
}

/**
 * Render callback of the bestofislam/lead-list block: the first page of the
 * Reflections page, of a section and of an archive, from the pieces the main query already holds.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function boi_lead_list_block_render( $attributes ) {
	global $wp_query;
	if ( is_admin() || is_paged() || ! ( is_home() || is_archive() ) || ! $wp_query instanceof WP_Query || empty( $wp_query->posts ) ) {
		return '';
	}
	$attributes = is_array( $attributes ) ? $attributes : array();
	$heading    = isset( $attributes['heading'] ) ? trim( (string) $attributes['heading'] ) : '';
	if ( '' === $heading ) {
		$term = is_category() || is_tag() || is_tax() ? get_queried_object() : null;
		/* translators: %s: section, category or tag name. */
		$heading = $term instanceof WP_Term ? sprintf( __( 'Latest in %s', 'bestofislam' ), $term->name ) : ( is_home() ? __( 'Latest reflections', 'bestofislam' ) : __( 'Latest pieces', 'bestofislam' ) );
	}
	$out        = boi_lead_list(
		array_slice( $wp_query->posts, 0, 1 + BOI_LEAD_LIST_MAX ),
		array(
			'heading' => $heading,
			'lead_id' => isset( $attributes['leadId'] ) ? (int) $attributes['leadId'] : 0,
		)
	);
	if ( '' === $out['html'] ) {
		return '';
	}
	boi_lead_list_shown( $out['ids'] );
	return $out['html'];
}

/**
 * Register the block. It is dynamic and has no editor script: the site editor
 * shows it as a placeholder, and the page draws it from the main query.
 */
function boi_lead_list_register() {
	register_block_type(
		'bestofislam/lead-list',
		array(
			'api_version'     => 3,
			'title'           => __( 'Lead and list', 'bestofislam' ),
			'category'        => 'theme',
			'attributes'      => array(
				'heading' => array(
					'type'    => 'string',
					'default' => '',
				),
				'leadId'  => array(
					'type'    => 'integer',
					'default' => 0,
				),
			),
			'render_callback' => 'boi_lead_list_block_render',
		)
	);
}
add_action( 'init', 'boi_lead_list_register' );

/**
 * The grid of the main query leaves out the pieces the lead and list
 * already showed: its post template runs on the remaining ones, then the
 * main query is restored for pagination and everything after it.
 *
 * @param string|null   $pre    Short-circuit output.
 * @param array         $parsed Parsed block.
 * @param WP_Block|null $parent Parent block.
 * @return string|null
 */
function boi_lead_list_grid_before( $pre, $parsed, $parent = null ) {
	global $wp_query;
	$shown = boi_lead_list_shown();
	if ( null !== $pre || ! $shown || 'core/post-template' !== ( $parsed['blockName'] ?? '' ) || ! $parent instanceof WP_Block || empty( $parent->parsed_block['attrs']['query']['inherit'] ) || ! $wp_query instanceof WP_Query ) {
		return $pre;
	}
	$GLOBALS['boi_lead_list_saved'] = array( $wp_query->posts, $wp_query->post_count, $wp_query->post, $GLOBALS['post'] ?? null );
	$rest                           = array_values(
		array_filter(
			$wp_query->posts,
			static function ( $p ) use ( $shown ) {
				return ! in_array( (int) ( is_object( $p ) ? $p->ID : $p ), $shown, true );
			}
		)
	);
	$wp_query->posts      = $rest;
	$wp_query->post_count = count( $rest );
	$wp_query->rewind_posts();
	return $pre;
}
add_filter( 'pre_render_block', 'boi_lead_list_grid_before', 10, 3 );

/**
 * Restore the main query, its current post and the global post after the
 * grid's post template.
 *
 * @param string $content Block HTML.
 * @param array  $block   Parsed block.
 * @return string
 */
function boi_lead_list_grid_after( $content, $block ) {
	global $wp_query;
	if ( 'core/post-template' === ( $block['blockName'] ?? '' ) && isset( $GLOBALS['boi_lead_list_saved'] ) && $wp_query instanceof WP_Query ) {
		list( $wp_query->posts, $wp_query->post_count, $wp_query->post, $GLOBALS['post'] ) = $GLOBALS['boi_lead_list_saved'];
		unset( $GLOBALS['boi_lead_list_saved'] );
		$wp_query->rewind_posts();
		// The loop moved the global post on; put it back, with the template tags' data.
		if ( $GLOBALS['post'] instanceof WP_Post ) {
			setup_postdata( $GLOBALS['post'] );
		}
	}
	return $content;
}
add_filter( 'render_block', 'boi_lead_list_grid_after', 10, 2 );

/**
 * The stylesheet, on the first page of Reflections, of the sections and of the archives (tags have tag.html). It is
 * enqueued with the theme's own styles rather than while the block or list
 * renders: an enqueue made during rendering prints the theme stylesheet it
 * depends on above the core block styles, and they then win over it.
 */
function boi_lead_list_assets() {
	if ( is_paged() || ! ( is_home() || ( is_archive() && ! is_tag() ) ) ) {
		return;
	}
	wp_enqueue_style( 'boi-lead-list', BOI_URI . '/assets/css/lead-list.css', array( 'boi-front' ), boi_asset_version( '/assets/css/lead-list.css' ) );
	wp_enqueue_script( 'boi-lead-list', BOI_URI . '/assets/js/lead-list.js', array(), boi_asset_version( '/assets/js/lead-list.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'boi_lead_list_assets', 20 );
