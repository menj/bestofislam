<?php
/**
 * Listicle block registration and rendering.
 *
 * Both blocks render server-side so that numbering and vote tallies are
 * always derived from current state rather than from saved markup.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the container and entry blocks.
 *
 * @return void
 */
function boi_register_blocks() {
	register_block_type(
		BOI_DIR . '/blocks/listicle',
		array( 'render_callback' => 'boi_render_listicle' )
	);

	register_block_type(
		BOI_DIR . '/blocks/entry',
		array( 'render_callback' => 'boi_render_entry' )
	);
}
add_action( 'init', 'boi_register_blocks' );

/**
 * Holds the running entry index and ordering for the listicle being rendered.
 *
 * @var array{index:int,total:int,order:string}
 */
$GLOBALS['boi_render_state'] = array(
	'index' => 0,
	'total' => 0,
	'order' => 'descending',
);

/**
 * Renders the listicle container.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Inner block markup.
 * @param object $block      Block instance.
 * @return string
 */
function boi_render_listicle( $attributes, $content, $block ) {
	$order = isset( $attributes['order'] ) ? $attributes['order'] : 'descending';
	$total = 0;

	if ( isset( $block->inner_blocks ) ) {
		foreach ( $block->inner_blocks as $inner ) {
			if ( 'bestofislam/entry' === $inner->name ) {
				++$total;
			}
		}
	}

	$GLOBALS['boi_render_state'] = array(
		'index' => 0,
		'total' => $total,
		'order' => $order,
	);

	// Inner blocks are rendered lazily; force rendering now that state is set.
	$rendered = '';
	if ( isset( $block->inner_blocks ) ) {
		foreach ( $block->inner_blocks as $inner ) {
			$rendered .= $inner->render();
		}
	} else {
		$rendered = $content;
	}

	$wrapper = get_block_wrapper_attributes(
		array(
			'class'      => 'boi-listicle is-order-' . sanitize_html_class( $order ),
			'data-total' => (string) $total,
		)
	);

	$jump = '';

	if ( ! isset( $attributes['showJumpList'] ) || $attributes['showJumpList'] ) {
		$jump = boi_render_jump_list( $block, $order, $total );
	}

	return sprintf( '<div %1$s>%2$s%3$s</div>', $wrapper, $jump, $rendered );
}

/**
 * Computes the displayed rank for the current entry.
 *
 * @return int
 */
function boi_next_rank() {
	$state = $GLOBALS['boi_render_state'];
	++$state['index'];
	$GLOBALS['boi_render_state'] = $state;

	if ( 'ascending' === $state['order'] || $state['total'] < 1 ) {
		return $state['index'];
	}

	return $state['total'] - $state['index'] + 1;
}

/**
 * Renders a single listicle entry.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Inner block markup.
 * @return string
 */
function boi_render_entry( $attributes, $content ) {
	$defaults = array(
		'entryId'  => '',
		'title'    => '',
		'imageId'  => 0,
		'imageUrl' => '',
		'imageAlt' => '',
		'claim'    => '',
		'claimSource' => '',
		'linkUrl'  => '',
		'linkText' => '',
	);

	$atts    = wp_parse_args( $attributes, $defaults );
	$options = boi_get_options();
	$rank    = boi_next_rank();

	$parts = array();

	if ( ! empty( $options['show_rank'] ) ) {
		$parts[] = sprintf(
			'<div class="boi-entry__rank" aria-hidden="true">%s</div>',
			esc_html( number_format_i18n( $rank ) )
		);
	}

	$media = '';

	if ( $atts['imageId'] ) {
		$media = sprintf(
			'<figure class="boi-entry__media">%s</figure>',
			wp_get_attachment_image(
				(int) $atts['imageId'],
				$options['image_size'],
				false,
				array( 'loading' => 'lazy' )
			)
		);
	} elseif ( '' !== $atts['imageUrl'] ) {
		$media = sprintf(
			'<figure class="boi-entry__media"><img src="%1$s" alt="%2$s" loading="lazy" /></figure>',
			esc_url( $atts['imageUrl'] ),
			esc_attr( $atts['imageAlt'] )
		);
	}

	$body = '<div class="boi-entry__body">' . $media;

	if ( '' !== $atts['title'] ) {
		$body .= sprintf(
			'<h2 class="boi-entry__title"><span class="boi-entry__rank-label screen-reader-text">%1$s </span>%2$s</h2>',
			esc_html( sprintf( /* translators: %s: rank number. */ __( 'Number %s:', 'bestofislam' ), number_format_i18n( $rank ) ) ),
			esc_html( $atts['title'] )
		);
	}

	if ( '' !== $atts['claim'] ) {
		$cite = '' !== $atts['claimSource']
			? sprintf( '<cite class="boi-entry__claim-source">%s</cite>', esc_html( $atts['claimSource'] ) )
			: '';

		$body .= sprintf(
			'<blockquote class="boi-entry__claim"><p class="boi-entry__claim-label">%1$s</p><p class="boi-entry__claim-text">%2$s</p>%3$s</blockquote>',
			esc_html__( 'The objection', 'bestofislam' ),
			esc_html( $atts['claim'] ),
			$cite
		);
	}

	$body .= '<div class="boi-entry__content">' . $content . '</div>';

	if ( '' !== $atts['linkUrl'] ) {
		$label = '' !== $atts['linkText'] ? $atts['linkText'] : __( 'Read the full treatment', 'bestofislam' );
		$body .= sprintf(
			'<p class="boi-entry__cta"><a class="boi-entry__link" href="%1$s" rel="noopener">%2$s</a></p>',
			esc_url( $atts['linkUrl'] ),
			esc_html( $label )
		);
	}

	if ( boi_voting_enabled() && '' !== $atts['entryId'] ) {
		$body .= boi_render_vote_widget( get_the_ID(), $atts['entryId'] );
	}

	$body  .= '</div>';
	$parts[] = $body;

	$wrapper = get_block_wrapper_attributes(
		array(
			'class'         => 'boi-entry',
			'id'            => boi_entry_anchor( $atts ),
			'data-entry-id' => $atts['entryId'],
		)
	);

	return sprintf(
		'<article %1$s>%2$s</article>',
		$wrapper,
		implode( '', $parts )
	);
}



/**
 * Builds the anchor list that precedes the entries.
 *
 * List-format results compete for the featured snippet, and an anchor list
 * gives the crawler an explicit outline of the entry titles.
 *
 * @param object $block Block instance.
 * @param string $order Numbering order.
 * @param int    $total Entry count.
 * @return string
 */
function boi_render_jump_list( $block, $order, $total ) {
	if ( ! isset( $block->inner_blocks ) || $total < 3 ) {
		return '';
	}

	$items  = '';
	$offset = 0;

	foreach ( $block->inner_blocks as $inner ) {
		if ( 'bestofislam/entry' !== $inner->name ) {
			continue;
		}

		$title = isset( $inner->attributes['title'] ) ? wp_strip_all_tags( $inner->attributes['title'] ) : '';

		if ( '' === $title ) {
			++$offset;
			continue;
		}

		$rank = 'ascending' === $order ? $offset + 1 : $total - $offset;

		$items .= sprintf(
			'<li class="boi-jump__item"><a href="#%1$s"><span class="boi-jump__rank">%2$s</span>%3$s</a></li>',
			esc_attr( boi_entry_anchor( $inner->attributes ) ),
			esc_html( number_format_i18n( $rank ) ),
			esc_html( $title )
		);

		++$offset;
	}

	if ( '' === $items ) {
		return '';
	}

	return sprintf(
		'<nav class="boi-jump" aria-label="%1$s"><ol class="boi-jump__list">%2$s</ol></nav>',
		esc_attr__( 'In this list', 'bestofislam' ),
		$items
	);
}

/**
 * Returns the fragment identifier for an entry.
 *
 * @param array $attributes Entry attributes.
 * @return string
 */
function boi_entry_anchor( $attributes ) {
	$title = isset( $attributes['title'] ) ? wp_strip_all_tags( $attributes['title'] ) : '';
	$slug  = sanitize_title( $title );

	if ( '' === $slug ) {
		$slug = isset( $attributes['entryId'] ) ? sanitize_title( $attributes['entryId'] ) : 'entry';
	}

	return 'entry-' . $slug;
}
