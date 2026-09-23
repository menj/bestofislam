<?php
/**
 * Structured data output for listicles.
 *
 * Schema is derived from the parsed block tree so that it cannot diverge from
 * the content a visitor actually sees.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints ItemList JSON-LD in the document head for singular listicle views.
 *
 * @return void
 */
function boi_print_schema() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_post();

	if ( ! $post || ! has_block( 'bestofislam/listicle', $post ) ) {
		return;
	}

	$graphs = array();

	foreach ( boi_find_listicles( parse_blocks( $post->post_content ) ) as $listicle ) {
		$graph = boi_build_item_list( $listicle, $post );

		if ( $graph ) {
			$graphs[] = $graph;
		}
	}

	if ( empty( $graphs ) ) {
		return;
	}

	$payload = 1 === count( $graphs ) ? $graphs[0] : $graphs;

	echo "\n<script type=\"application/ld+json\">" .
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) .
		"</script>\n";
}
add_action( 'wp_head', 'boi_print_schema', 20 );

/**
 * Recursively collects listicle blocks from a parsed block tree.
 *
 * @param array $blocks Parsed blocks.
 * @return array
 */
function boi_find_listicles( $blocks ) {
	$found = array();

	foreach ( $blocks as $block ) {
		if ( 'bestofislam/listicle' === $block['blockName'] ) {
			$found[] = $block;
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = array_merge( $found, boi_find_listicles( $block['innerBlocks'] ) );
		}
	}

	return $found;
}

/**
 * Builds the ItemList graph for one listicle block.
 *
 * @param array   $listicle Parsed listicle block.
 * @param WP_Post $post     Host post.
 * @return array|null
 */
function boi_build_item_list( $listicle, $post ) {
	$attrs = isset( $listicle['attrs'] ) ? $listicle['attrs'] : array();
	$type = isset( $attrs['schemaType'] ) ? $attrs['schemaType'] : 'ItemList';

	// Review and Rating are deliberately absent. Attaching review markup to a
	// proposition invites a structured-data penalty, since a theological
	// argument is not a reviewable entity.
	if ( 'none' === $type ) {
		return null;
	}

	$entries = array();

	foreach ( $listicle['innerBlocks'] as $inner ) {
		if ( 'bestofislam/entry' === $inner['blockName'] ) {
			$entries[] = isset( $inner['attrs'] ) ? $inner['attrs'] : array();
		}
	}

	$total = count( $entries );

	if ( 0 === $total ) {
		return null;
	}

	$order    = isset( $attrs['order'] ) ? $attrs['order'] : 'descending';
	$elements = array();

	foreach ( $entries as $offset => $entry ) {
		$position = 'ascending' === $order ? $offset + 1 : $total - $offset;
		$name     = isset( $entry['title'] ) ? wp_strip_all_tags( $entry['title'] ) : '';

		if ( '' === $name ) {
			continue;
		}

		$item = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $name,
		);

		if ( ! empty( $entry['linkUrl'] ) ) {
			$item['url'] = esc_url_raw( $entry['linkUrl'] );
		}

		if ( ! empty( $entry['imageId'] ) ) {
			$src = wp_get_attachment_image_url( (int) $entry['imageId'], 'large' );

			if ( $src ) {
				$item['image'] = $src;
			}
		} elseif ( ! empty( $entry['imageUrl'] ) ) {
			$item['image'] = esc_url_raw( $entry['imageUrl'] );
		}

		$elements[] = $item;
	}

	if ( empty( $elements ) ) {
		return null;
	}

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'ItemList',
		'name'            => wp_strip_all_tags( get_the_title( $post ) ),
		'url'             => get_permalink( $post ),
		'numberOfItems'   => count( $elements ),
		'itemListOrder'   => 'ascending' === $order
			? 'https://schema.org/ItemListOrderAscending'
			: 'https://schema.org/ItemListOrderDescending',
		'itemListElement' => $elements,
	);
}
