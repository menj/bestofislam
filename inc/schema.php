<?php
/**
 * Listicle block lookup.
 *
 * The theme printed an ItemList block for every listicle until 1.93.0. Google
 * shows ItemList only as a carousel of recipes, courses, restaurants or
 * movies, and a listicle of arguments is none of these, so the markup was
 * removed. The article graph in inc/structured-data.php describes the page.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

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
