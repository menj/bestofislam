<?php
/**
 * Sections block and featured-image fallback.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the sections block.
 *
 * @return void
 */
function boi_register_sections_block() {
	register_block_type(
		'bestofislam/sections',
		array( 'render_callback' => 'boi_render_sections' )
	);
}
add_action( 'init', 'boi_register_sections_block' );

/**
 * Renders every top-level section as a card, empty ones included.
 *
 * The core categories block hides empty terms and prints the count outside
 * the link, which on a young site shows one section of seven and a stray
 * bracket. This shows the whole structure from day one.
 *
 * @return string
 */
function boi_render_sections() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'listicle-topic',
			'hide_empty' => false,
			'parent'     => 0,
			'orderby'    => 'term_id',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	$items = '';

	foreach ( $terms as $term ) {
		$count = (int) $term->count;
		$ids   = array( $term->term_id );

		foreach ( get_term_children( $term->term_id, 'listicle-topic' ) as $child_id ) {
			$child = get_term( $child_id, 'listicle-topic' );

			if ( $child && ! is_wp_error( $child ) ) {
				$count += (int) $child->count;
				$ids[]  = $child_id;
			}
		}

		$latest = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'tax_query'      => array(
					array(
						'taxonomy' => 'listicle-topic',
						'field'    => 'term_id',
						'terms'    => $ids,
					),
				),
			)
		);

		$latest_html = $latest
			? sprintf(
				'<p class="boi-sections__latest"><span>%1$s</span> <a href="%2$s">%3$s</a></p>',
				esc_html__( 'Latest:', 'bestofislam' ),
				esc_url( get_permalink( $latest[0] ) ),
				esc_html( get_the_title( $latest[0] ) )
			)
			: sprintf( '<p class="boi-sections__latest is-empty">%s</p>', esc_html__( 'First pieces coming.', 'bestofislam' ) );

		$items .= sprintf(
			'<li class="boi-sections__item">
				<a class="boi-sections__head" href="%1$s"><span class="boi-sections__name">%2$s</span><span class="boi-sections__count">%3$s</span></a>
				<p class="boi-sections__desc">%4$s</p>
				%5$s
			</li>',
			esc_url( get_term_link( $term ) ),
			esc_html( $term->name ),
			esc_html( sprintf( /* translators: %s: count. */ _n( '%s piece', '%s pieces', $count, 'bestofislam' ), number_format_i18n( $count ) ) ),
			esc_html( $term->description ),
			$latest_html
		);
	}

	return sprintf( '<ul class="boi-sections">%s</ul>', $items );
}

/**
 * Supplies a branded placeholder when a post has no featured image.
 *
 * Grids with some images and some gaps look broken. The placeholder carries
 * the rank mark on the pale fill, so an unillustrated post keeps its shape
 * without pretending to have a photograph.
 *
 * @param string $html    Existing markup.
 * @param int    $post_id Post identifier.
 * @return string
 */
function boi_featured_image_fallback( $html, $post_id ) {
	if ( '' !== $html ) {
		return $html;
	}

	// The post being read shows no placeholder in its own hero slot.
	if ( is_singular() && (int) get_queried_object_id() === (int) $post_id && ! is_front_page() ) {
		return $html;
	}

	$svg = '<svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">'
		. '<g stroke-linecap="round">'
		. '<line x1="30" y1="38" x2="92" y2="38" stroke="var(--wp--preset--color--accent-2)" stroke-width="10"/>'
		. '<line x1="30" y1="60" x2="78" y2="60" stroke="var(--wp--preset--color--accent-1)" stroke-width="10"/>'
		. '<line x1="30" y1="82" x2="60" y2="82" stroke="currentColor" stroke-width="10"/>'
		. '</g></svg>';

	return sprintf(
		'<a class="boi-placeholder" href="%1$s" aria-hidden="true" tabindex="-1">%2$s</a>',
		esc_url( get_permalink( $post_id ) ),
		$svg
	);
}
add_filter( 'post_thumbnail_html', 'boi_featured_image_fallback', 10, 2 );
