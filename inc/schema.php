<?php
/**
 * Structured data for listicles: the ItemList nodes the page graph takes.
 *
 * Schema is derived from the parsed block tree so that it cannot diverge from
 * the content a visitor actually sees.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The ItemList nodes of every listicle in a post, for the page graph that
 * inc/schema-graph.php prints. Each list has its own @id, so the article can
 * point to it with hasPart.
 *
 * @param WP_Post $post Host post.
 * @return array Graph nodes.
 */
function boi_listicle_schema_nodes( $post ) {
	if ( ! $post || ! has_block( 'bestofislam/listicle', $post ) ) {
		return array();
	}

	$nodes = array();

	foreach ( boi_find_listicles( parse_blocks( $post->post_content ) ) as $n => $listicle ) {
		$node = boi_build_item_list( $listicle, $post );

		if ( $node ) {
			unset( $node['@context'] );
			$node['@id'] = get_permalink( $post ) . '#itemlist' . ( $n ? '-' . ( $n + 1 ) : '' );
			$nodes[]     = $node;
		}
	}

	return $nodes;
}

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
	$bodies  = array();

	foreach ( $listicle['innerBlocks'] as $inner ) {
		if ( 'bestofislam/entry' === $inner['blockName'] ) {
			$entries[] = isset( $inner['attrs'] ) ? $inner['attrs'] : array();
			$bodies[]  = $inner;
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
		$name     = isset( $entry['title'] ) ? boi_plain( $entry['title'] ) : '';

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

		// The entry itself, at its anchor on the page, with its opening
		// paragraph as the description a reader sees first.
		$thing = array(
			'@type' => 'CreativeWork',
			'@id'   => get_permalink( $post ) . '#' . boi_entry_anchor( $entry ),
			'name'  => $name,
			'url'   => get_permalink( $post ) . '#' . boi_entry_anchor( $entry ),
		);
		$lede  = boi_entry_lede( $bodies[ $offset ] );

		if ( '' !== $lede ) {
			$item['description']  = $lede;
			$thing['description'] = $lede;
		}

		if ( ! empty( $entry['claim'] ) ) {
			$thing['about'] = array(
				'@type' => 'Claim',
				'text'  => boi_plain( $entry['claim'] ),
			);
		}

		$item['item'] = $thing;

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
		'name'            => boi_plain( get_the_title( $post ) ),
		'url'             => get_permalink( $post ),
		'numberOfItems'   => count( $elements ),
		'itemListOrder'   => 'ascending' === $order
			? 'https://schema.org/ItemListOrderAscending'
			: 'https://schema.org/ItemListOrderDescending',
		'itemListElement' => $elements,
	);
}

/**
 * The opening paragraph of a listicle entry as plain text, cut at a word
 * near 300 characters.
 *
 * @param array $entry Parsed entry block.
 * @return string
 */
function boi_entry_lede( $entry ) {
	foreach ( isset( $entry['innerBlocks'] ) ? $entry['innerBlocks'] : array() as $inner ) {
		if ( 'core/paragraph' !== $inner['blockName'] ) {
			continue;
		}

		$text = boi_plain( preg_replace( '#<sup>.*?</sup>#s', '', (string) $inner['innerHTML'] ) );

		if ( '' === $text ) {
			continue;
		}

		if ( mb_strlen( $text ) > 300 ) {
			$cut  = mb_substr( $text, 0, 300 );
			$space = mb_strrpos( $cut, ' ' );
			// With no space to break at, cut at 300 rather than return "…".
			$text  = rtrim( false === $space ? $cut : mb_substr( $cut, 0, $space ), ' ,;:' ) . '…';
		}

		return $text;
	}

	return '';
}
