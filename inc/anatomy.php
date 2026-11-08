<?php
/**
 * Article anatomy.
 *
 * The pieces a single article adds to what the template already prints (the
 * breadcrumbs, the excerpt, the date, the reading time, the questions panel,
 * the Read next card, the related cards and the notes the schema cites):
 *
 *   bestofislam/article-dates  the updated date, when it differs, and the
 *                              review note, in the meta row
 *   bestofislam/answer-box     "In short": the key points, paired with the
 *                              questions the piece answers
 *   bestofislam/contents       the sections, from three level-two headings,
 *                              with the level-three ones under them, on an
 *                              article or page without a jump list; the
 *                              [wp_toc] and [auto_toc] shortcodes place it in
 *                              the text instead (adapted from WP TOC)
 *   the_content 12             heading ids, the notes anchor, the placed
 *                              contents and the mid-article next step
 *   is-style-faq               the FAQ style for a Details block, read by the
 *                              FAQPage data only when it is on the page
 *
 * A next step is only ever keep reading, check the sources or ask a question.
 * The author card keeps the WordPress display name; nothing here adds a name.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The article a piece is about: the queried post, when it has no password.
 *
 * @return WP_Post|null
 */
function boi_anatomy_post() {
	if ( ! is_singular( 'post' ) ) {
		return null;
	}

	$post = get_queried_object();

	return ( $post instanceof WP_Post && '' === $post->post_password ) ? $post : null;
}

/**
 * Whether a the_content call is the main text of the queried article.
 *
 * @return bool
 */
function boi_anatomy_is_main() {
	$post = boi_anatomy_post();

	return $post && ! is_admin() && ! is_feed() && is_main_query() && ! doing_filter( 'get_the_excerpt' ) && (int) get_the_ID() === $post->ID;
}

/**
 * A switch on the Display tab, on by default.
 *
 * @param string $key Option key.
 * @return bool
 */
function boi_anatomy_on( $key ) {
	$options = boi_get_options();

	return ! isset( $options[ $key ] ) || ! empty( $options[ $key ] );
}

/* Headings ------------------------------------------------------------------ */

/**
 * Plain text of a piece of HTML, on one line.
 *
 * @param string $html HTML.
 * @return string
 */
function boi_anatomy_text( $html ) {
	return trim( (string) preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES, 'UTF-8' ) ) );
}

/**
 * The pattern the heading functions read the text with: a template block,
 * whose content is never shown and is passed over whole, or a level-two or
 * level-three heading. Groups: 1 the tag, 2 its attributes, 3 its inner HTML.
 */
const BOI_TOC_PATTERN = '#<template\b.*?</template>|<(h[23])(\s[^>]*)?>((?:(?!</\1>).)*)</\1>#is';

/**
 * An id made from heading text. sanitize_title() percent-encodes letters
 * outside ASCII ("%d9%85"); a "%" in an id is ambiguous in an address, so
 * the slug is decoded back to readable letters, which ids and fragments
 * both accept: an Arabic heading keeps an Arabic id.
 *
 * @param string $text Heading text.
 * @return string
 */
function boi_toc_slug( $text ) {
	$slug    = sanitize_title( $text );
	$decoded = rawurldecode( $slug );
	$slug    = ( $decoded !== $slug && preg_match( '//u', $decoded ) ) ? $decoded : $slug;
	$slug    = trim( (string) preg_replace( '/\s+/u', '-', $slug ), '-' );

	return '' !== $slug ? $slug : 'section';
}

/**
 * The level-two and level-three headings of a piece of HTML, in order, each
 * with the id it has or the id made from its text.
 *
 * Every id already in the text is reserved first, a heading's or any other
 * element's, so a made id never repeats one; a repeat is numbered -2, -3.
 * A heading marked no-toc (class) or data-no-toc is passed over and gets no
 * id, as does an empty one, and so is anything inside a template element.
 *
 * @param string $html Stored or rendered text.
 * @return array List of array( 'level', 'id', 'text', 'own', 'skip' ).
 */
function boi_toc_headings( $html ) {
	$html = (string) $html;

	if ( ! preg_match_all( BOI_TOC_PATTERN, $html, $found, PREG_SET_ORDER ) ) {
		return array();
	}

	// Ids outside template elements, of every element.
	$seen = array();
	$bare = (string) preg_replace( '#<template\b.*?</template>#is', '', $html );

	if ( preg_match_all( '/<[a-z][^>]*\sid\s*=\s*["\']([^"\']+)["\']/i', $bare, $ids ) ) {
		foreach ( $ids[1] as $id ) {
			$seen[ trim( $id ) ] = true;
		}
	}

	$out = array();

	foreach ( $found as $m ) {
		if ( empty( $m[1] ) ) {
			continue; // A template block.
		}

		$attrs = (string) ( $m[2] ?? '' );
		$text  = boi_anatomy_text( $m[3] );
		$own   = preg_match( '/\sid\s*=\s*["\']([^"\']+)["\']/i', $attrs, $idm ) ? trim( $idm[1] ) : '';
		$skip  = '' === $text
			|| (bool) preg_match( '/\sclass\s*=\s*["\'][^"\']*\bno-toc\b/i', $attrs )
			|| (bool) preg_match( '/\sdata-no-toc\b/i', $attrs );
		$id    = $own;

		if ( '' === $id && ! $skip ) {
			$base = boi_toc_slug( $text );
			$id   = $base;

			for ( $n = 2; isset( $seen[ $id ] ); $n++ ) {
				$id = $base . '-' . $n;
			}

			$seen[ $id ] = true;
		}

		$out[] = array(
			'level' => (int) substr( strtolower( $m[1] ), 1 ),
			'id'    => $skip ? $own : $id,
			'text'  => $text,
			'own'   => '' !== $own,
			'skip'  => $skip,
		);
	}

	return $out;
}

/**
 * The level-two headings of a piece of HTML, as the contents and the
 * mid-article next step count them.
 *
 * @param string $html Stored or rendered text.
 * @return array List of array( id, text, whether the heading already has the id ).
 */
function boi_anatomy_heading_list( $html ) {
	$out = array();

	foreach ( boi_toc_headings( $html ) as $h ) {
		if ( 2 === $h['level'] ) {
			$out[] = array( $h['id'], $h['text'], $h['own'] );
		}
	}

	return $out;
}

/**
 * Gives each level-two and level-three heading without an id the one
 * boi_toc_headings() made for it, so the contents can link to it.
 *
 * @param string $html Rendered text.
 * @return string
 */
function boi_anatomy_heading_ids( $html ) {
	$list = boi_toc_headings( $html );
	$i    = 0;

	return (string) preg_replace_callback(
		BOI_TOC_PATTERN,
		static function ( $m ) use ( $list, &$i ) {
			if ( empty( $m[1] ) ) {
				return $m[0];
			}

			$h = $list[ $i++ ] ?? null;

			if ( ! $h || $h['own'] || '' === $h['id'] ) {
				return $m[0];
			}

			return '<' . $m[1] . ' id="' . esc_attr( $h['id'] ) . '"' . (string) ( $m[2] ?? '' ) . '>' . $m[3] . '</' . $m[1] . '>';
		},
		(string) $html
	);
}

/**
 * The contents as a tree: each heading a child of the nearest heading above
 * it with a lower level, so a level-three heading before any level-two one
 * stays at the top.
 *
 * @param array $headings From boi_toc_headings().
 * @param array $levels   Heading levels to include.
 * @param int   $depth    Deepest nesting, 0 for no limit.
 * @return array Nodes: array( 'level', 'id', 'text', 'children' ).
 */
function boi_toc_tree( $headings, $levels = array( 2, 3 ), $depth = 0 ) {
	$nodes = array();
	$roots = array();
	$stack = array();

	foreach ( $headings as $h ) {
		if ( $h['skip'] || '' === $h['id'] || ! in_array( $h['level'], $levels, true ) ) {
			continue;
		}

		while ( $stack && $nodes[ end( $stack ) ]['level'] >= $h['level'] ) {
			array_pop( $stack );
		}

		if ( $depth > 0 && count( $stack ) >= $depth ) {
			continue;
		}

		$index           = count( $nodes );
		$nodes[ $index ] = array(
			'level'    => $h['level'],
			'id'       => $h['id'],
			'text'     => $h['text'],
			'children' => array(),
		);

		if ( $stack ) {
			$nodes[ end( $stack ) ]['children'][] = $index;
		} else {
			$roots[] = $index;
		}

		$stack[] = $index;
	}

	$nest = static function ( $indices ) use ( &$nest, $nodes ) {
		$out = array();

		foreach ( $indices as $index ) {
			$node             = $nodes[ $index ];
			$node['children'] = $nest( $node['children'] );
			$out[]            = $node;
		}

		return $out;
	};

	return $nest( $roots );
}

/**
 * How many entries a tree has, at every level.
 *
 * @param array $tree From boi_toc_tree().
 * @return int
 */
function boi_toc_count( $tree ) {
	$count = 0;

	foreach ( $tree as $node ) {
		$count += 1 + boi_toc_count( $node['children'] );
	}

	return $count;
}

/**
 * Gives the Notes heading the id "notes", when it has none, so the next step
 * can point to it. Only the first heading that reads "Notes" is touched.
 *
 * @param string $html Rendered text.
 * @return string
 */
function boi_anatomy_notes_anchor( $html ) {
	if ( false !== strpos( (string) $html, 'id="notes"' ) ) {
		return $html;
	}

	$done = false;

	return (string) preg_replace_callback(
		'#<(h[23])(\s[^>]*)?>((?:(?!</\1>).)*)</\1>#is',
		static function ( $m ) use ( &$done ) {
			if ( $done || 'Notes' !== boi_anatomy_text( $m[3] ) || preg_match( '/\sid\s*=/i', (string) ( $m[2] ?? '' ) ) ) {
				return $m[0];
			}

			$done = true;

			return '<' . $m[1] . ' id="notes"' . (string) ( $m[2] ?? '' ) . '>' . $m[3] . '</' . $m[1] . '>';
		},
		(string) $html
	);
}

/**
 * The id of the Notes heading, whichever id it carries: "notes" when
 * boi_anatomy_notes_anchor() gave it one, or the author's own.
 *
 * @param string $html Rendered text.
 * @return string The id, or '' when the text has no Notes heading with one.
 */
function boi_anatomy_notes_id( $html ) {
	if ( ! preg_match_all( '#<(h[23])(\s[^>]*)?>((?:(?!</\1>).)*)</\1>#is', (string) $html, $found, PREG_SET_ORDER ) ) {
		return '';
	}

	foreach ( $found as $m ) {
		if ( 'Notes' === boi_anatomy_text( $m[3] ) && preg_match( '/\sid\s*=\s*["\']([^"\']+)["\']/i', (string) ( $m[2] ?? '' ), $idm ) ) {
			return $idm[1];
		}
	}

	return '';
}

/**
 * Whether an article shows its own contents: a listicle with its jump list
 * already has one.
 *
 * @param WP_Post $post Article.
 * @return bool
 */
function boi_anatomy_has_jump_list( $post ) {
	if ( ! function_exists( 'boi_find_listicles' ) || ! has_block( 'bestofislam/listicle', $post ) ) {
		return false;
	}

	foreach ( boi_find_listicles( parse_blocks( $post->post_content ) ) as $listicle ) {
		// The block's default is true: the jump list shows unless the attribute turns it off.
		if ( ! isset( $listicle['attrs']['showJumpList'] ) || ! empty( $listicle['attrs']['showJumpList'] ) ) {
			return true;
		}
	}

	return false;
}

/* The blocks ---------------------------------------------------------------- */

/**
 * Registers the anatomy blocks and the FAQ style.
 *
 * @return void
 */
function boi_register_anatomy_blocks() {
	register_block_type( 'bestofislam/article-dates', array( 'render_callback' => 'boi_render_article_dates' ) );
	register_block_type(
		'bestofislam/answer-box',
		array(
			'render_callback' => 'boi_render_answer_box',
			'attributes'      => array(
				'summary' => array(
					'type'    => 'string',
					'default' => '',
				),
				'points'  => array(
					'type'    => 'string',
					'default' => '',
				),
			),
		)
	);
	register_block_type( 'bestofislam/contents', array( 'render_callback' => 'boi_render_contents' ) );

	register_block_style(
		'core/details',
		array(
			'name'  => 'faq',
			'label' => __( 'FAQ', 'bestofislam' ),
		)
	);

	$meta = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => static function () {
			return current_user_can( 'edit_posts' );
		},
	);

	register_post_meta( 'post', '_boi_takeaways', $meta + array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
	register_post_meta( 'post', '_boi_reviewed', $meta + array( 'sanitize_callback' => 'boi_anatomy_sanitize_date' ) );
}
add_action( 'init', 'boi_register_anatomy_blocks' );

/**
 * A review date as Y-m-d, or '' when it is not a real date.
 *
 * @param mixed $value Value.
 * @return string
 */
function boi_anatomy_sanitize_date( $value ) {
	$value = trim( (string) $value );

	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) || ! checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ) {
		return '';
	}

	return $value;
}

/**
 * The meta row's extra dates: "Updated" when the article changed at least a
 * day after it was published, and "Reviewed" when a review date is set.
 *
 * @return string
 */
function boi_render_article_dates() {
	$post = boi_anatomy_post();

	if ( ! $post ) {
		return '';
	}

	$out       = '';
	$published = (int) get_post_time( 'U', true, $post );
	$modified  = (int) get_post_modified_time( 'U', true, $post );

	if ( $modified - $published >= DAY_IN_SECONDS ) {
		$out .= sprintf(
			'<span class="boi-dates__item">%1$s <time datetime="%2$s">%3$s</time></span>',
			esc_html__( 'Updated', 'bestofislam' ),
			esc_attr( (string) get_post_modified_time( 'c', true, $post ) ),
			esc_html( (string) get_post_modified_time( (string) get_option( 'date_format' ), false, $post, true ) )
		);
	}

	$reviewed = boi_anatomy_sanitize_date( get_post_meta( $post->ID, '_boi_reviewed', true ) );

	if ( '' !== $reviewed ) {
		$out .= sprintf(
			'<span class="boi-dates__item boi-dates__item--reviewed">%1$s <time datetime="%2$s">%3$s</time></span>',
			esc_html__( 'Reviewed', 'bestofislam' ),
			esc_attr( $reviewed ),
			esc_html( wp_date( (string) get_option( 'date_format' ), (int) strtotime( $reviewed . ' 12:00:00 UTC' ), new DateTimeZone( 'UTC' ) ) )
		);
	}

	// Each date is a child of the meta row of its own, so the row's dividers fall between them.
	return $out;
}

/**
 * Key points from text: one a line, or separated by ";;".
 *
 * @param string $text Text.
 * @return string[]
 */
function boi_anatomy_points( $text ) {
	return array_values( array_filter( array_map( 'trim', (array) preg_split( '/\r\n|\r|\n|\s*;;\s*/', (string) $text ) ), 'strlen' ) );
}

/**
 * "In short", at the head of the article: the key points from the Key points
 * and review box (or the block's own summary and points attributes), paired
 * with the questions the piece answers. Without key points it is the
 * questions panel as before.
 *
 * @param array $attributes Block attributes: summary, points.
 * @return string
 */
function boi_render_answer_box( $attributes = array() ) {
	$post = boi_anatomy_post();

	if ( ! $post ) {
		return '';
	}

	$questions = function_exists( 'boi_render_article_questions' ) ? boi_render_article_questions() : '';
	$summary   = trim( (string) ( $attributes['summary'] ?? '' ) );
	$points    = boi_anatomy_points( $attributes['points'] ?? '' );

	if ( '' === $summary && ! $points ) {
		$points = boi_anatomy_points( get_post_meta( $post->ID, '_boi_takeaways', true ) );
	}

	if ( '' === $summary && ! $points ) {
		return $questions;
	}

	// Each box on the page gets its own heading id: the template's and one placed in the text can both show.
	static $count = 0;
	++$count;
	$hid = 1 === $count ? 'boi-answer-title' : 'boi-answer-title-' . $count;

	$out = '<section class="boi-answer" aria-labelledby="' . $hid . '"><h2 id="' . $hid . '" class="boi-answer__title">' . esc_html__( 'In short', 'bestofislam' ) . '</h2>';

	if ( '' !== $summary ) {
		$out .= '<p class="boi-answer__summary">' . esc_html( $summary ) . '</p>';
	}

	if ( $points ) {
		$out .= '<ul class="boi-answer__points"><li>' . implode( '</li><li>', array_map( 'esc_html', $points ) ) . '</li></ul>';
	}

	// Inside the box the questions panel is part of it, so it is no landmark of its own.
	$questions = (string) preg_replace( array( '#^<aside class="boi-answers"[^>]*>#', '#</aside>$#' ), array( '<div class="boi-answers">', '</div>' ), $questions );

	return $out . $questions . '</section>';
}

/**
 * Whether the "In short" box shows on an article.
 *
 * @param WP_Post $post Article.
 * @return bool
 */
function boi_anatomy_answer_shows( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( boi_anatomy_points( get_post_meta( $post->ID, '_boi_takeaways', true ) ) ) {
		return true;
	}

	// A box placed in the text shows with its own summary or points.
	$found = false;
	$walk  = static function ( $blocks ) use ( &$walk, &$found ) {
		foreach ( $blocks as $block ) {
			if ( 'bestofislam/answer-box' === $block['blockName'] && ( '' !== trim( (string) ( $block['attrs']['summary'] ?? '' ) ) || boi_anatomy_points( $block['attrs']['points'] ?? '' ) ) ) {
				$found = true;
				return;
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'] );
			}
		}
	};

	$walk( parse_blocks( $post->post_content ) );

	return $found;
}

/**
 * The post whose contents the current view can show: the queried article or
 * page, when it has no password.
 *
 * @return WP_Post|null
 */
function boi_toc_post() {
	// The front page is a page, but it is a landing page with its own sections.
	if ( ! is_singular( array( 'post', 'page' ) ) || is_front_page() ) {
		return null;
	}

	$post = get_queried_object();

	return ( $post instanceof WP_Post && '' === $post->post_password ) ? $post : null;
}

/**
 * Whether the WP TOC plugin is running. The theme then leaves the contents,
 * the shortcodes and the contents schema to the plugin, so a page never gets
 * two lists.
 *
 * @return bool
 */
function boi_toc_plugin_active() {
	return defined( 'WP_TOC_VERSION' );
}

/**
 * The settings of a contents list, from shortcode attributes or the
 * defaults. The attributes are those of the WP TOC plugin, so a text written
 * for it reads the same here.
 *
 * @param array|string $atts Attributes.
 * @return array( 'levels', 'title', 'depth', 'min', 'collapsible', 'collapsed' ).
 */
function boi_toc_settings( $atts = array() ) {
	$atts = is_array( $atts ) ? array_change_key_case( $atts, CASE_LOWER ) : array();
	$bool = static function ( $v ) {
		return in_array( strtolower( trim( (string) $v ) ), array( '1', 'true', 'yes', 'on' ), true );
	};

	$levels = array();

	foreach ( explode( ',', (string) ( $atts['tags'] ?? 'h2,h3' ) ) as $tag ) {
		if ( preg_match( '/^h([1-6])$/', strtolower( trim( $tag ) ), $m ) ) {
			$levels[] = (int) $m[1];
		}
	}

	// The theme reads level-two and level-three headings only.
	$levels = array_values( array_intersect( array_unique( $levels ), array( 2, 3 ) ) );

	return array(
		'levels'      => $levels ? $levels : array( 2, 3 ),
		'title'       => isset( $atts['title'] ) ? trim( wp_strip_all_tags( (string) $atts['title'] ) ) : __( 'In this article', 'bestofislam' ),
		'depth'       => max( 0, (int) ( $atts['depth'] ?? 0 ) ),
		'min'         => max( 1, (int) ( $atts['min'] ?? 1 ) ),
		'collapsible' => $bool( $atts['collapsible'] ?? '' ),
		'collapsed'   => $bool( $atts['collapsed'] ?? '' ),
	);
}

/**
 * One level of the contents list.
 *
 * @param array $nodes Nodes.
 * @return string
 */
function boi_toc_list( $nodes ) {
	$html = '';

	foreach ( $nodes as $node ) {
		$html .= '<li><a href="#' . esc_attr( $node['id'] ) . '">' . esc_html( $node['text'] ) . '</a>';

		if ( $node['children'] ) {
			$html .= '<ol class="boi-contents__sublist">' . boi_toc_list( $node['children'] ) . '</ol>';
		}

		$html .= '</li>';
	}

	return $html;
}

/**
 * The contents list: a navigation region named by its label, the sections
 * in order with their subsections nested, folded into a details element
 * when asked. No script is needed.
 *
 * @param array $tree     From boi_toc_tree().
 * @param array $settings From boi_toc_settings().
 * @return string
 */
function boi_toc_html( $tree, $settings ) {
	static $shown = 0;
	++$shown;

	$lid   = 1 === $shown ? 'boi-contents-title' : 'boi-contents-title-' . $shown;
	$name  = '' !== $settings['title'] ? $settings['title'] : __( 'In this article', 'bestofislam' );
	$list  = '<ol class="boi-contents__list">' . boi_toc_list( $tree ) . '</ol>';
	$label = '<span id="' . $lid . '" class="boi-contents__label">' . esc_html( $name ) . '</span>';

	if ( $settings['collapsible'] ) {
		$body = '<details class="boi-contents__details"' . ( $settings['collapsed'] ? '' : ' open' ) . '><summary class="boi-contents__summary">' . $label . '</summary>' . $list . '</details>';
	} elseif ( '' === $settings['title'] ) {
		return '<nav class="boi-contents" aria-label="' . esc_attr( $name ) . '">' . $list . '</nav>';
	} else {
		$body = $label . $list;
	}

	return '<nav class="boi-contents" aria-labelledby="' . $lid . '">' . $body . '</nav>';
}

/**
 * The text of a post rendered to read its headings. A contents list in the
 * text would render itself again inside that pass, so the contents block and
 * the shortcodes print nothing while it runs.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function boi_toc_rendered( $post ) {
	static $cache = array();

	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$GLOBALS['boi_toc_busy'] = true;
	$html                    = do_shortcode( do_blocks( $post->post_content ) );
	$GLOBALS['boi_toc_busy'] = false;

	return $cache[ $post->ID ] = $html;
}

/**
 * Whether a post places its own contents with [wp_toc] or [auto_toc].
 *
 * @param WP_Post $post Post.
 * @return bool
 */
function boi_toc_has_shortcode( $post ) {
	return has_shortcode( $post->post_content, 'wp_toc' ) || has_shortcode( $post->post_content, 'auto_toc' );
}

/**
 * The contents the current view shows at its head, as a tree: an article or
 * page with three or more level-two headings, the Contents switch on, no
 * jump list of its own, no shortcode placing the list in the text and no WP
 * TOC plugin.
 *
 * @return array Tree, empty when the view shows no contents.
 */
function boi_toc_view_tree() {
	static $tree = null;

	if ( null !== $tree ) {
		return $tree;
	}

	$tree = array();
	$post = boi_toc_post();

	if ( ! $post || boi_toc_plugin_active() || ! boi_anatomy_on( 'article_contents' ) || boi_toc_has_shortcode( $post ) || boi_anatomy_has_jump_list( $post ) ) {
		return $tree;
	}

	$headings = boi_toc_headings( boi_toc_rendered( $post ) );
	$sections = 0;

	foreach ( $headings as $h ) {
		$sections += ( 2 === $h['level'] && ! $h['skip'] ) ? 1 : 0;
	}

	if ( $sections >= 3 ) {
		$tree = boi_toc_tree( $headings );
	}

	return $tree;
}

/**
 * The contents block: the sections of the article or page, from three
 * level-two headings up, with the level-three headings under them.
 *
 * @return string
 */
function boi_render_contents() {
	if ( ! empty( $GLOBALS['boi_toc_busy'] ) ) {
		return '';
	}

	$tree = boi_toc_view_tree();

	return $tree ? boi_toc_html( $tree, boi_toc_settings() ) : '';
}

/**
 * The [wp_toc] and [auto_toc] shortcodes place the contents in the text.
 * The headings get their ids only later, in the_content, so the shortcode
 * leaves a marker carrying its settings, and boi_toc_fill() builds the list
 * there from the finished text. Outside the main text of an article or page
 * (an excerpt, a feed, a widget) it prints nothing.
 *
 * @param array|string $atts Attributes.
 * @return string
 */
function boi_toc_shortcode( $atts ) {
	if ( ! empty( $GLOBALS['boi_toc_busy'] ) || ! boi_toc_post() || is_feed() || doing_filter( 'get_the_excerpt' ) ) {
		return '';
	}

	return '<!--boi-toc:' . bin2hex( (string) wp_json_encode( boi_toc_settings( $atts ) ) ) . '-->';
}

/**
 * Replaces each contents marker with its list, built from the headings of
 * the finished text. A marker wpautop wrapped in a paragraph loses the
 * paragraph too.
 *
 * @param string $html Rendered text, with heading ids.
 * @return string
 */
function boi_toc_fill( $html ) {
	if ( false === strpos( (string) $html, '<!--boi-toc:' ) ) {
		return $html;
	}

	$headings = boi_toc_headings( $html );

	return (string) preg_replace_callback(
		'#(<p>\s*)?<!--boi-toc:([0-9a-f]*)-->(?(1)\s*</p>)#',
		static function ( $m ) use ( $headings ) {
			$settings = json_decode( (string) hex2bin( strlen( $m[2] ) % 2 ? '' : $m[2] ), true );
			$settings = is_array( $settings ) ? array_merge( boi_toc_settings(), $settings ) : boi_toc_settings();
			$tree     = boi_toc_tree( $headings, array_map( 'intval', (array) $settings['levels'] ), (int) $settings['depth'] );

			return boi_toc_count( $tree ) >= (int) $settings['min'] ? boi_toc_html( $tree, $settings ) : '';
		},
		(string) $html
	);
}

/**
 * Registers the shortcodes, unless the WP TOC plugin runs and has its own.
 *
 * @return void
 */
function boi_toc_register_shortcodes() {
	if ( boi_toc_plugin_active() ) {
		return;
	}

	add_shortcode( 'wp_toc', 'boi_toc_shortcode' );
	add_shortcode( 'auto_toc', 'boi_toc_shortcode' );
}
add_action( 'init', 'boi_toc_register_shortcodes' );

/**
 * The contents in the page's graph: a SiteNavigationElement, part of the
 * WebPage, with one WebPageElement per section. Each section's @id is the
 * address of its heading, as the WP TOC plugin writes it.
 *
 * @param array $nodes Graph nodes.
 * @return array
 */
function boi_toc_schema_nodes( $nodes ) {
	$tree = boi_toc_view_tree();
	$post = boi_toc_post();

	if ( ! $tree || ! $post || ! function_exists( 'boi_schema_page_url' ) ) {
		return $nodes;
	}

	$url     = boi_schema_page_url();
	$page_id = boi_schema_page_id( $url );
	$parts   = static function ( $tree ) use ( &$parts, $url ) {
		$out = array();

		foreach ( $tree as $node ) {
			$address = $url . '#' . rawurlencode( $node['id'] );
			$part    = array(
				'@type' => 'WebPageElement',
				'@id'   => $address,
				'name'  => $node['text'],
				'url'   => $address,
			);

			if ( $node['children'] ) {
				$part['hasPart'] = $parts( $node['children'] );
			}

			$out[] = $part;
		}

		return $out;
	};

	$nodes[] = array(
		'@type'    => 'SiteNavigationElement',
		'@id'      => $url . '#contents',
		'name'     => __( 'In this article', 'bestofislam' ),
		'isPartOf' => array( '@id' => $page_id ),
		'hasPart'  => $parts( $tree ),
	);
	$nodes[] = array(
		'@id'     => $page_id,
		'hasPart' => array( array( '@id' => $url . '#contents' ) ),
	);

	return $nodes;
}
add_filter( 'boi_schema_nodes', 'boi_toc_schema_nodes' );

/* The mid-article next step ---------------------------------------------- */

/**
 * The Contact page, published, for "Ask a question".
 *
 * @return string Its address, or ''.
 */
function boi_anatomy_contact_url() {
	$page = get_page_by_path( 'contact' );

	return ( $page instanceof WP_Post && 'publish' === $page->post_status ) ? (string) get_permalink( $page ) : '';
}

/**
 * The next step in the middle of an article: the notes at the end, the next
 * piece in the reading order and the Contact page. Nothing else.
 *
 * @param WP_Post $post    Article.
 * @param string  $content Rendered text, to see whether it has notes.
 * @return string
 */
function boi_anatomy_next_step( $post, $content ) {
	$items = array();

	$notes = boi_anatomy_notes_id( $content );

	if ( '' !== $notes ) {
		$items[] = sprintf(
			/* translators: %s: link to the notes. */
			esc_html__( 'Check each claim against its source: %s are at the end.', 'bestofislam' ),
			'<a href="#' . esc_attr( $notes ) . '">' . esc_html__( 'the notes', 'bestofislam' ) . '</a>'
		);
	}

	$next = function_exists( 'boi_next_in_order' ) ? boi_next_in_order( $post->post_name ) : null;

	if ( $next ) {
		$items[] = sprintf(
			/* translators: %s: link to the next article. */
			esc_html__( 'Next in the reading order: %s', 'bestofislam' ),
			'<a href="' . esc_url( get_permalink( $next[0] ) ) . '">' . esc_html( get_the_title( $next[0] ) ) . '</a>'
		);
	}

	$contact = boi_anatomy_contact_url();

	if ( '' !== $contact ) {
		$items[] = sprintf(
			/* translators: %s: link to the Contact page. */
			esc_html__( 'A question this piece leaves open? %s.', 'bestofislam' ),
			'<a href="' . esc_url( $contact ) . '">' . esc_html__( 'Ask it here', 'bestofislam' ) . '</a>'
		);
	}

	if ( ! $items ) {
		return '';
	}

	return '<aside class="boi-step" aria-label="' . esc_attr__( 'Next step', 'bestofislam' ) . '"><p class="boi-step__label">' . esc_html__( 'Next step', 'bestofislam' ) . '</p><ul class="boi-step__list"><li>' . implode( '</li><li>', $items ) . '</li></ul></aside>';
}

/**
 * Puts the next step before the third section: the third entry of a
 * listicle, or else the third level-two heading.
 *
 * @param string $html  Rendered text.
 * @param string $aside The next step.
 * @return string
 */
function boi_anatomy_insert_mid( $html, $aside ) {
	if ( '' === $aside ) {
		return $html;
	}

	// An entry's wrapper carries boi-entry among other classes, in any order.
	foreach ( array( '#<article\b[^>]*\bclass="(?:[^"]*\s)?boi-entry(?:\s[^"]*)?"#', '#<h2[\s>]#i' ) as $pattern ) {
		if ( preg_match_all( $pattern, $html, $found, PREG_OFFSET_CAPTURE ) && count( $found[0] ) >= 3 ) {
			$at = (int) $found[0][2][1];

			return substr( $html, 0, $at ) . $aside . substr( $html, $at );
		}
	}

	return $html;
}

/**
 * The main text of an article: heading ids, the notes anchor, any contents
 * the text places with a shortcode and the mid-article next step. A page
 * gets the heading ids and the contents.
 *
 * @param string $content Rendered text.
 * @return string
 */
function boi_anatomy_filter_content( $content ) {
	if ( ! boi_anatomy_is_main() ) {
		// A page gets heading ids and any contents its text places, nothing else.
		$page = boi_toc_post();

		if ( $page && 'page' === $page->post_type && ! is_admin() && ! is_feed() && is_main_query() && (int) get_the_ID() === $page->ID ) {
			return boi_toc_fill( boi_anatomy_heading_ids( $content ) );
		}

		return $content;
	}

	$post    = boi_anatomy_post();
	$content = boi_toc_fill( boi_anatomy_notes_anchor( boi_anatomy_heading_ids( $content ) ) );

	if ( boi_anatomy_on( 'article_mid_next' ) ) {
		$content = boi_anatomy_insert_mid( $content, boi_anatomy_next_step( $post, $content ) );
	}

	return $content;
}
add_filter( 'the_content', 'boi_anatomy_filter_content', 12 );

/**
 * A table in an article scrolls on its own on a phone: a focusable region
 * named by its caption, so a keyboard can scroll it too.
 *
 * @param string $html Block markup.
 * @return string
 */
function boi_anatomy_table_region( $html ) {
	if ( ! boi_anatomy_post() || false !== strpos( (string) $html, 'role="region"' ) ) {
		return $html;
	}

	$label = preg_match( '#<figcaption[^>]*>(.*?)</figcaption>#is', (string) $html, $m ) ? boi_anatomy_text( $m[1] ) : '';
	$label = '' !== $label ? $label : __( 'Table', 'bestofislam' );

	return (string) preg_replace( '#<figure\b([^>]*)>#i', '<figure$1 role="region" tabindex="0" aria-label="' . esc_attr( wp_html_excerpt( $label, 120, '…' ) ) . '">', (string) $html, 1 );
}
add_filter( 'render_block_core/table', 'boi_anatomy_table_region' );

/* FAQ ------------------------------------------------------------------------ */

/**
 * The questions of the article's FAQ-style Details blocks, with their
 * answers, as the reader sees them.
 *
 * @param WP_Post $post Article.
 * @return array List of array( question, answer ).
 */
function boi_anatomy_faq_pairs( $post ) {
	$pairs = array();
	$walk  = static function ( $blocks ) use ( &$walk, &$pairs ) {
		foreach ( $blocks as $block ) {
			if ( 'core/details' === $block['blockName'] && preg_match( '/(?:^|\s)is-style-faq(?:\s|$)/', (string) ( $block['attrs']['className'] ?? '' ) ) ) {
				$html = render_block( $block );

				if ( preg_match( '#<summary[^>]*>(.*?)</summary>(.*)</details>#is', $html, $m ) ) {
					$question = boi_anatomy_text( $m[1] );
					$answer   = boi_anatomy_text( $m[2] );

					if ( '' !== $question && '' !== $answer ) {
						$pairs[] = array( $question, $answer );
					}
				}
				continue;
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'] );
			}
		}
	};

	$walk( parse_blocks( $post->post_content ) );

	return $pairs;
}

/**
 * On an article with FAQ-style Details blocks, an FAQPage node, part of the
 * page, lists each question it shows as a Question node. The "In short" box joins
 * the parts read aloud when it shows.
 *
 * @param array $nodes Graph nodes.
 * @return array
 */
function boi_anatomy_schema_nodes( $nodes ) {
	$post = boi_anatomy_post();

	if ( ! $post ) {
		return $nodes;
	}

	$pairs  = boi_anatomy_faq_pairs( $post );
	$answer = boi_anatomy_answer_shows( $post );

	if ( ! $pairs && ! $answer ) {
		return $nodes;
	}

	$url   = get_permalink( $post );
	$found = array();

	foreach ( $pairs as $i => $pair ) {
		$found[] = array(
			'@type'          => 'Question',
			'@id'            => $url . '#faq-' . ( $i + 1 ),
			'name'           => $pair[0],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $pair[1],
			),
		);
	}

	$page_id = '';

	foreach ( $nodes as $k => $node ) {
		if ( ! in_array( 'WebPage', (array) ( $node['@type'] ?? array() ), true ) ) {
			continue;
		}

		$page_id = (string) ( $node['@id'] ?? '' );

		if ( $answer && isset( $node['speakable']['cssSelector'] ) ) {
			$nodes[ $k ]['speakable']['cssSelector'][] = '.boi-answer';
		}
		break;
	}

	// The FAQ is a node of its own, part of the page, so the page keeps the
	// Article as its main entity and the FAQPage lists Questions only.
	if ( $found ) {
		$faq = array(
			'@type'      => 'FAQPage',
			'@id'        => $url . '#faq',
			'mainEntity' => array_map(
				static function ( $q ) {
					return array( '@id' => $q['@id'] );
				},
				$found
			),
		);

		if ( '' !== $page_id ) {
			$faq['isPartOf'] = array( '@id' => $page_id );
		}

		$found[] = $faq;
	}

	return array_merge( $nodes, $found );
}
add_filter( 'boi_schema_nodes', 'boi_anatomy_schema_nodes' );

/* The edit screen: key points and review ------------------------------------ */

/**
 * Adds the Key points and review box to the post screen.
 *
 * @return void
 */
function boi_anatomy_add_meta_box() {
	add_meta_box( 'boi_anatomy', __( 'Key points and review', 'bestofislam' ), 'boi_anatomy_meta_box', 'post', 'normal', 'default' );
}
add_action( 'add_meta_boxes_post', 'boi_anatomy_add_meta_box' );

/**
 * The Key points and review box.
 *
 * @param WP_Post $post Post.
 * @return void
 */
function boi_anatomy_meta_box( $post ) {
	wp_nonce_field( 'boi_anatomy', 'boi_anatomy_nonce' );
	?>
	<p>
		<label for="boi-takeaways"><strong><?php esc_html_e( 'Key points, one a line', 'bestofislam' ); ?></strong></label>
		<textarea id="boi-takeaways" name="boi_takeaways" rows="4" class="widefat"><?php echo esc_textarea( (string) get_post_meta( $post->ID, '_boi_takeaways', true ) ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Shown in the "In short" box at the head of the article, above the questions it answers.', 'bestofislam' ); ?></span>
	</p>
	<p>
		<label for="boi-reviewed"><strong><?php esc_html_e( 'Last reviewed', 'bestofislam' ); ?></strong></label><br>
		<input type="date" id="boi-reviewed" name="boi_reviewed" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, '_boi_reviewed', true ) ); ?>">
		<span class="description"><?php esc_html_e( 'The day the article was last checked against its sources. It shows under the title. Leave it empty to show nothing.', 'bestofislam' ); ?></span>
	</p>
	<?php
}

/**
 * Saves the Key points and review box.
 *
 * @param int $post_id Post.
 * @return void
 */
function boi_anatomy_save( $post_id ) {
	if ( ! isset( $_POST['boi_anatomy_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['boi_anatomy_nonce'] ) ), 'boi_anatomy' ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$values = array(
		'_boi_takeaways' => implode( "\n", boi_anatomy_points( sanitize_textarea_field( wp_unslash( $_POST['boi_takeaways'] ?? '' ) ) ) ),
		'_boi_reviewed'  => boi_anatomy_sanitize_date( sanitize_text_field( wp_unslash( $_POST['boi_reviewed'] ?? '' ) ) ),
	);

	foreach ( $values as $key => $value ) {
		if ( '' !== $value ) {
			update_post_meta( $post_id, $key, $value );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}
add_action( 'save_post_post', 'boi_anatomy_save' );

/* Assets ---------------------------------------------------------------------- */

/**
 * Loads the anatomy styles on single articles, and on pages for the
 * contents.
 *
 * @return void
 */
function boi_anatomy_assets() {
	if ( is_singular( 'post' ) || ( is_page() && ! is_front_page() ) ) {
		wp_enqueue_style( 'boi-anatomy', BOI_URI . '/assets/css/anatomy.css', array( 'boi-article' ), boi_asset_version( '/assets/css/anatomy.css' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'boi_anatomy_assets', 20 );
