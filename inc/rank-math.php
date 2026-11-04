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
 *  - the exclusion of unlisted articles from its sitemap;
 *  - the title rule: "<page> | <site name>", under 60 characters.
 *
 * The rest of the theme's page graph (inc/schema-graph.php: the lists, the
 * questions, the Glossary, the citations, the header and footer) goes into
 * Rank Math's graph through the same filter, merged by @id, and the theme
 * prints no JSON-LD of its own. Every hook below is registered only when
 * Rank Math is present and touches nothing otherwise.
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
 * Adds the image licence data to Rank Math's graph on an article, then the
 * theme's page graph on every page.
 *
 * @param array $data   Rank Math's entities, keyed by name.
 * @param mixed $jsonld The plugin's JSON-LD object.
 * @return array
 */
function boi_rank_math_json_ld( $data, $jsonld = null ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}

	$post = is_singular( 'post' ) ? get_post() : null;

	if ( ! $post ) {
		return boi_schema_merge_into_rank_math( $data, boi_schema_graph_nodes( true ) );
	}

	$image = boi_article_image_object( $post );

	if ( $image ) {
		$fields = array_diff_key( $image, array_flip( array( '@type', '@id', 'url', 'contentUrl', 'width', 'height' ) ) );

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

	// Then every other node of the theme's page graph, merged by @id, so the
	// page still has one graph.
	return boi_schema_merge_into_rank_math( $data, boi_schema_graph_nodes( true ) );
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

	$duplicate = function_exists( 'boi_is_duplicate_archive' ) && boi_is_duplicate_archive();

	if ( $unlisted || $duplicate || is_search() || is_404() || ( is_paged() && ( is_home() || is_archive() ) ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}

	return $robots;
}

/**
 * Points a seeded category's canonical link at the section it mirrors.
 *
 * @param string $canonical Rank Math's canonical URL.
 * @return string
 */
function boi_rank_math_canonical( $canonical ) {
	$topic = function_exists( 'boi_category_canonical_topic' ) ? boi_category_canonical_topic() : null;

	if ( ! $topic ) {
		return $canonical;
	}

	$link = get_term_link( $topic );

	return is_wp_error( $link ) ? $canonical : $link;
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
	if ( 'post' === $type && $object instanceof WP_Post && function_exists( 'boi_is_unlisted_anywhere' ) && boi_is_unlisted_anywhere( $object ) ) {
		return false;
	}

	// A seeded category repeats its section, and a one-article tag is thin.
	if ( 'term' === $type && $object instanceof WP_Term ) {
		if ( 'category' === $object->taxonomy && function_exists( 'boi_topic_for_category' ) && boi_topic_for_category( $object ) ) {
			return false;
		}

		if ( 'post_tag' === $object->taxonomy && (int) $object->count < 2 ) {
			return false;
		}
	}

	return $url;
}

/**
 * Holds Rank Math's title tag to the site's rule, "<page> | <site name>" under
 * 60 characters. A title the owner set in Rank Math for one post keeps its
 * wording as the page part; everything else is built by boi_title_parts(), as
 * it is without the plugin.
 *
 * @param string $title Title from Rank Math.
 * @return string
 */
function boi_rank_math_title( $title ) {
	$site  = wp_strip_all_tags( get_bloginfo( 'name', 'display' ) );
	$parts = array( 'title' => '' );

	if ( is_singular() ) {
		$post   = get_queried_object();
		$custom = $post instanceof WP_Post ? (string) get_post_meta( $post->ID, 'rank_math_title', true ) : '';

		if ( '' !== trim( $custom ) ) {
			$page = trim( html_entity_decode( wp_strip_all_tags( (string) $title ), ENT_QUOTES, 'UTF-8' ) );
			$tail = ' ' . boi_title_separator() . ' ' . $site;

			if ( '' !== $site && substr( $page, -strlen( $tail ) ) === $tail ) {
				$page = substr( $page, 0, -strlen( $tail ) );
			}

			$parts['title']      = $page;
			$parts['boi_custom'] = true;
		} else {
			$parts['title'] = single_post_title( '', false );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$parts['title'] = single_term_title( '', false );
	} elseif ( is_post_type_archive() ) {
		$parts['title'] = post_type_archive_title( '', false );
	} elseif ( is_day() ) {
		$parts['title'] = get_the_date();
	} elseif ( is_month() ) {
		$parts['title'] = get_the_date( 'F Y' );
	} elseif ( is_year() ) {
		$parts['title'] = get_the_date( 'Y' );
	} elseif ( is_author() && get_queried_object() instanceof WP_User ) {
		$parts['title'] = get_queried_object()->display_name;
	} elseif ( is_search() ) {
		/* translators: %s: search phrase. */
		$parts['title'] = sprintf( __( 'Search results for "%s"', 'bestofislam' ), get_search_query() );
	} elseif ( is_404() ) {
		$parts['title'] = __( 'Page not found', 'bestofislam' );
	} elseif ( is_home() && ! is_front_page() ) {
		$parts['title'] = single_post_title( '', false );
	}

	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	if ( $paged > 1 && ! is_404() ) {
		/* translators: %s: page number. */
		$parts['page'] = sprintf( __( 'Page %s', 'bestofislam' ), $paged );
	}

	$parts = boi_title_parts( $parts );

	return $parts['title'] . ' ' . boi_title_separator() . ' ' . $parts['site'];
}

if ( boi_rank_math_active() ) {
	add_filter( 'rank_math/frontend/title', 'boi_rank_math_title', 99 );
	add_filter( 'rank_math/json_ld', 'boi_rank_math_json_ld', 99, 2 );
	add_filter( 'rank_math/frontend/description', 'boi_rank_math_description' );
	add_filter( 'rank_math/frontend/robots', 'boi_rank_math_robots' );
	add_filter( 'rank_math/sitemap/entry', 'boi_rank_math_sitemap_entry', 10, 3 );
	add_filter( 'rank_math/frontend/canonical', 'boi_rank_math_canonical' );
}
