<?php
/**
 * Rank Math compatibility.
 *
 * With Rank Math active the theme does not repeat what it does: titles,
 * descriptions, canonical links, robots directives, the XML sitemap and the
 * site-wide structured data belong to the plugin. The theme keeps only what
 * Rank Math has no way to know, and supplies it through the plugin's own
 * hooks:
 *
 *  - the licence and credit of each image, added to Rank Math's graph;
 *  - the images set in the text of an article, as ImageObjects;
 *  - a description for views where Rank Math has none;
 *  - noindex for search results and paginated archives;
 *  - the exclusion of unlisted articles from its sitemap.
 *
 * The theme's own ItemList and question markup are separate JSON-LD blocks
 * that Rank Math does not produce, so they stay. Every hook below is
 * registered only when Rank Math is present and touches nothing otherwise.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Rank Math is active.
 *
 * @return bool
 */
function boi_rank_math_active() {
	return defined( 'RANK_MATH_VERSION' );
}

/**
 * Whether Rank Math is active and printing structured data. Its Schema module
 * can be switched off, and then the theme's own graph is needed.
 *
 * @return bool
 */
function boi_rank_math_schema_active() {
	if ( ! boi_rank_math_active() ) {
		return false;
	}

	if ( class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'is_module_active' ) ) {
		return (bool) \RankMath\Helper::is_module_active( 'rich-snippet' );
	}

	return true;
}

/**
 * Whether Rank Math will print a BreadcrumbList of its own.
 *
 * @return bool
 */
function boi_rank_math_breadcrumb_schema() {
	return boi_rank_math_schema_active()
		&& class_exists( '\RankMath\Helper' )
		&& method_exists( '\RankMath\Helper', 'is_breadcrumbs_enabled' )
		&& \RankMath\Helper::is_breadcrumbs_enabled();
}

/**
 * Adds the image licence data to Rank Math's graph on an article.
 *
 * @param array $data   Rank Math's entities, keyed by name.
 * @param mixed $jsonld The plugin's JSON-LD object.
 * @return array
 */
function boi_rank_math_json_ld( $data, $jsonld = null ) {
	if ( ! is_array( $data ) || ! is_singular( 'post' ) ) {
		return $data;
	}

	$post = get_post();

	if ( ! $post ) {
		return $data;
	}

	$image = boi_article_image_object( $post );

	if ( $image ) {
		$fields = array_diff_key( $image, array_flip( array( '@type', 'url', 'contentUrl', 'width', 'height' ) ) );

		foreach ( $data as $key => $node ) {
			// Plugins spell the id #primaryImage or #primaryimage; match either.
			if ( is_array( $node ) && isset( $node['@type'], $node['@id'] ) && 'ImageObject' === $node['@type'] && '#primaryimage' === strtolower( substr( (string) $node['@id'], -13 ) ) ) {
				$data[ $key ] = array_merge( $node, $fields );
			}
		}
	}

	$refs = array();

	foreach ( boi_inline_image_objects( $post ) as $i => $node ) {
		$data[ 'boi_inline_image_' . ( $i + 1 ) ] = $node;
		$refs[]                                   = array( '@id' => $node['@id'] );
	}

	// Tie the in-text images to the article, as the theme's own graph does.
	if ( $refs ) {
		foreach ( $data as $key => $node ) {
			if ( ! is_array( $node ) || ! isset( $node['@type'] ) || ! array_intersect( (array) $node['@type'], array( 'Article', 'BlogPosting', 'NewsArticle' ) ) ) {
				continue;
			}

			$existing = isset( $node['image'] ) ? $node['image'] : array();

			if ( ! is_array( $existing ) || isset( $existing['@id'] ) || isset( $existing['url'] ) ) {
				$existing = array( $existing );
			}

			$data[ $key ]['image'] = array_values( array_filter( array_merge( $existing, $refs ) ) );
		}
	}

	return $data;
}

/**
 * Offers the theme's description where Rank Math has none.
 *
 * @param string $description Rank Math's description.
 * @return string
 */
function boi_rank_math_description( $description ) {
	if ( '' !== trim( (string) $description ) ) {
		return $description;
	}

	$ours = boi_meta_description();

	return '' !== $ours ? $ours : $description;
}

/**
 * Keeps search results and paginated archives out of the index.
 *
 * @param array $robots Rank Math's robots directives.
 * @return array
 */
function boi_rank_math_robots( $robots ) {
	$unlisted = is_singular() && function_exists( 'boi_is_unlisted' ) && boi_is_unlisted( get_queried_object_id() );

	if ( $unlisted || is_search() || ( is_paged() && ( is_home() || is_archive() ) ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}

	return $robots;
}

/**
 * Leaves unlisted articles out of Rank Math's sitemap.
 *
 * @param array|false $url    Sitemap entry.
 * @param string      $type   Entry type.
 * @param mixed       $object The post, term or user behind the entry.
 * @return array|false
 */
function boi_rank_math_sitemap_entry( $url, $type = '', $object = null ) {
	if ( 'post' === $type && $object instanceof WP_Post && function_exists( 'boi_is_unlisted' ) && boi_is_unlisted( $object ) ) {
		return false;
	}

	return $url;
}

if ( boi_rank_math_active() ) {
	add_filter( 'rank_math/json_ld', 'boi_rank_math_json_ld', 99, 2 );
	add_filter( 'rank_math/frontend/description', 'boi_rank_math_description' );
	add_filter( 'rank_math/frontend/robots', 'boi_rank_math_robots' );
	add_filter( 'rank_math/sitemap/entry', 'boi_rank_math_sitemap_entry', 10, 3 );
}
