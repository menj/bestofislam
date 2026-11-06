<?php
/**
 * Structured data for every view: the page itself, the site's menus, and the
 * site-wide entities every page belongs to.
 *
 * structured-data.php describes articles and the author profile, glossary.php
 * the Glossary, schema.php the numbered entries of an article, questions.php
 * the front-page questions, and seo.php the breadcrumb trail. This file gives
 * every other view its schema.org page type and ties all of them together:
 *
 *  - Organization and WebSite on every page, so each page can name what it
 *    is part of. The WebSite carries the site search as a SearchAction, and
 *    the Organization its contact page and its publishing standards.
 *  - The header and footer menus as lists of SiteNavigationElement.
 *  - One page node per view, of the type that describes it: WebPage,
 *    AboutPage, ContactPage, FAQPage, CollectionPage or SearchResultsPage.
 *    Section archives, Reflections and search results list what they show.
 *
 * Every node is joined by @id. With Rank Math's schema active none of this is
 * printed, since Rank Math prints its own page graph.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The address of the current view, as its canonical link gives it.
 *
 * @return string
 */
function boi_schema_current_url() {
	if ( function_exists( 'boi_share_url' ) ) {
		$url = boi_share_url();

		if ( '' !== $url ) {
			$paged = (int) get_query_var( 'paged' );

			if ( $paged > 1 && ! is_singular() ) {
				$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' );
			}

			return $url;
		}
	}

	if ( is_search() ) {
		return get_search_link();
	}

	global $wp;

	return home_url( isset( $wp->request ) ? user_trailingslashit( $wp->request ) : '/' );
}

/**
 * The @id of the page node for the current view.
 *
 * @return string
 */
function boi_schema_page_id() {
	return boi_schema_current_url() . '#webpage';
}

/**
 * The @id of the breadcrumb trail for the current view.
 *
 * @return string
 */
function boi_schema_breadcrumb_id() {
	return boi_schema_current_url() . '#breadcrumb';
}

/**
 * Whether the current view prints a breadcrumb trail with its BreadcrumbList.
 *
 * @return bool
 */
function boi_schema_has_breadcrumb() {
	if ( is_front_page() || ! function_exists( 'boi_breadcrumb_trail' ) ) {
		return false;
	}

	if ( function_exists( 'boi_rank_math_breadcrumb_schema' ) && boi_rank_math_breadcrumb_schema() ) {
		return false;
	}

	return count( boi_breadcrumb_trail() ) >= 2;
}

/**
 * The site search as a SearchAction on the WebSite.
 *
 * @return array
 */
function boi_schema_search_action() {
	$base   = function_exists( 'boi_search_base' ) ? boi_search_base() : 'search';
	$target = get_option( 'permalink_structure' )
		? home_url( '/' . $base . '/{search_term_string}/' )
		: home_url( '/?s={search_term_string}' );

	return array(
		'@type'       => 'SearchAction',
		'target'      => array(
			'@type'       => 'EntryPoint',
			'urlTemplate' => $target,
		),
		'query-input' => 'required name=search_term_string',
	);
}

/**
 * A published page by its slug, for linking the site-wide entities to the
 * pages that describe them.
 *
 * @param string $slug Page slug.
 * @return string Permalink, or empty.
 */
function boi_schema_page_url( $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );

	return ( $page && 'publish' === $page->post_status ) ? (string) get_permalink( $page ) : '';
}

/**
 * Organization and WebSite, completed for every page: the site search, the
 * contact page and the publishing standards.
 *
 * @return array Graph nodes.
 */
function boi_schema_site_nodes() {
	$nodes = boi_site_entities();

	foreach ( $nodes as $i => $node ) {
		if ( 'Organization' === $node['@type'] ) {
			$contact = boi_schema_page_url( 'contact' );
			$sources = boi_schema_page_url( 'sources-and-standards' );
			$about   = boi_schema_page_url( 'about' );

			if ( '' !== $contact ) {
				$nodes[ $i ]['contactPoint'] = array(
					'@type'             => 'ContactPoint',
					'contactType'       => 'editorial',
					'url'               => $contact,
					'availableLanguage' => get_bloginfo( 'language' ),
				);
			}

			if ( '' !== $sources ) {
				// The page that sets out how sources are chosen, cited and
				// corrected: schema.org's publishing principles.
				$nodes[ $i ]['publishingPrinciples']           = $sources;
				$nodes[ $i ]['correctionsPolicy']              = $sources;
				$nodes[ $i ]['verificationFactCheckingPolicy'] = $sources;
			}

			if ( '' !== $about ) {
				$nodes[ $i ]['mainEntityOfPage'] = $about;
			}
		}

		if ( 'WebSite' === $node['@type'] ) {
			$nodes[ $i ]['potentialAction'] = boi_schema_search_action();

			$tagline = boi_plain( get_bloginfo( 'description' ) );

			if ( '' !== $tagline ) {
				$nodes[ $i ]['description'] = $tagline;
			}
		}
	}

	return $nodes;
}

/**
 * The links of the navigation blocks in a template part.
 *
 * The part as the owner last saved it in the Site Editor is read first, the
 * theme's file otherwise.
 *
 * @param string $part Template part slug: 'header' or 'footer'.
 * @return array[] Array( name, url ) pairs, in order.
 */
function boi_schema_menu_links( $part ) {
	$content  = '';
	$template = function_exists( 'get_block_template' ) ? get_block_template( get_stylesheet() . '//' . $part, 'wp_template_part' ) : null;

	if ( $template && ! empty( $template->content ) ) {
		$content = $template->content;
	} elseif ( file_exists( BOI_DIR . '/parts/' . $part . '.html' ) ) {
		$content = (string) file_get_contents( BOI_DIR . '/parts/' . $part . '.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	$links = array();
	$seen  = array();

	$walk = function ( $blocks ) use ( &$walk, &$links, &$seen ) {
		foreach ( $blocks as $block ) {
			if ( in_array( $block['blockName'], array( 'core/navigation-link', 'core/navigation-submenu' ), true ) && ! empty( $block['attrs']['url'] ) && ! empty( $block['attrs']['label'] ) ) {
				$url = $block['attrs']['url'];
				$url = 0 === strpos( $url, '/' ) ? home_url( $url ) : $url;

				if ( ! isset( $seen[ $url ] ) ) {
					$seen[ $url ] = true;
					$links[]      = array( boi_plain( $block['attrs']['label'] ), $url );
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'] );
			}
		}
	};

	$walk( parse_blocks( $content ) );

	return $links;
}

/**
 * The header and footer menus as ItemLists of SiteNavigationElement.
 *
 * @return array Graph nodes.
 */
function boi_schema_navigation_nodes() {
	$nodes = array();
	$menus = array(
		'header' => __( 'Main menu', 'bestofislam' ),
		'footer' => __( 'Footer menu', 'bestofislam' ),
	);

	foreach ( $menus as $part => $label ) {
		$links = boi_schema_menu_links( $part );

		if ( ! $links ) {
			continue;
		}

		$items = array();

		foreach ( $links as $i => $link ) {
			$items[] = array(
				'@type'    => 'SiteNavigationElement',
				'position' => $i + 1,
				'name'     => $link[0],
				'url'      => $link[1],
			);
		}

		$nodes[] = array(
			'@type'           => 'ItemList',
			'@id'             => boi_entity_id( 'navigation-' . $part ),
			'name'            => $label,
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		);
	}

	return $nodes;
}

/**
 * The common fields of a page node.
 *
 * @param string|string[] $type schema.org type.
 * @param string          $name Name of the page.
 * @return array
 */
function boi_schema_page_node( $type, $name ) {
	$page = array(
		'@type'      => $type,
		'@id'        => boi_schema_page_id(),
		'url'        => boi_schema_current_url(),
		'name'       => boi_plain( $name ),
		'inLanguage' => get_bloginfo( 'language' ),
		'isPartOf'   => array( '@id' => boi_entity_id( 'website' ) ),
		'publisher'  => array( '@id' => boi_entity_id( 'organization' ) ),
	);

	$desc = function_exists( 'boi_meta_description' ) ? boi_plain( boi_meta_description() ) : '';

	if ( '' !== $desc ) {
		$page['description'] = $desc;
	}

	if ( boi_schema_has_breadcrumb() ) {
		$page['breadcrumb'] = array( '@id' => boi_schema_breadcrumb_id() );
	}

	return $page;
}

/**
 * An ItemList of the posts a listing view shows on this page.
 *
 * @param WP_Post[] $posts Posts in display order.
 * @param string    $name  List name.
 * @return array|null ItemList, or null when empty.
 */
function boi_schema_post_list( $posts, $name ) {
	$items  = array();
	$paged  = max( 1, (int) get_query_var( 'paged' ) );
	$offset = ( $paged - 1 ) * max( 1, (int) get_option( 'posts_per_page', 10 ) );

	foreach ( array_values( $posts ) as $i => $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $offset + $i + 1,
			'url'      => get_permalink( $post ),
			'name'     => boi_plain( get_the_title( $post ) ),
		);
	}

	if ( ! $items ) {
		return null;
	}

	return array(
		'@type'           => 'ItemList',
		'@id'             => boi_schema_current_url() . '#list',
		'name'            => boi_plain( $name ),
		'numberOfItems'   => count( $items ),
		'itemListElement' => $items,
	);
}

/**
 * The questions and answers of a page written as headings each followed by
 * paragraphs, such as the FAQ page.
 *
 * @param WP_Post $post Page.
 * @return array[] Question nodes.
 */
function boi_schema_page_questions( $post ) {
	$questions = array();
	$current   = null;

	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( 'core/heading' === $block['blockName'] ) {
			if ( $current && '' !== $current['answer'] ) {
				$questions[] = $current;
			}

			$current = array(
				'name'   => boi_plain( render_block( $block ) ),
				'answer' => '',
			);
		} elseif ( $current && 'core/paragraph' === $block['blockName'] ) {
			$current['answer'] = trim( $current['answer'] . ' ' . boi_plain( render_block( $block ) ) );
		}
	}

	if ( $current && '' !== $current['answer'] ) {
		$questions[] = $current;
	}

	$nodes = array();

	foreach ( $questions as $q ) {
		if ( '' === $q['name'] ) {
			continue;
		}

		$nodes[] = array(
			'@type'          => 'Question',
			'name'           => $q['name'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $q['answer'],
			),
		);
	}

	return $nodes;
}

/**
 * The page node for a page: its type from what the page is for.
 *
 * @param WP_Post $post Page.
 * @return array Graph nodes.
 */
function boi_schema_page_entities( $post ) {
	$types = array(
		'about'                      => 'AboutPage',
		'contact'                    => 'ContactPage',
		'frequently-asked-questions' => 'FAQPage',
	);

	$type = isset( $types[ $post->post_name ] ) ? $types[ $post->post_name ] : 'WebPage';
	$page = boi_schema_page_node( $type, get_the_title( $post ) );

	$page['datePublished'] = get_post_time( 'c', true, $post );
	$page['dateModified']  = get_post_modified_time( 'c', true, $post );

	if ( 'AboutPage' === $type ) {
		$page['about'] = array( '@id' => boi_entity_id( 'organization' ) );
	}

	if ( 'ContactPage' === $type ) {
		$page['mainEntity'] = array( '@id' => boi_entity_id( 'organization' ) );
	}

	if ( 'FAQPage' === $type ) {
		$questions = boi_schema_page_questions( $post );

		if ( $questions ) {
			$page['mainEntity'] = $questions;
		} else {
			$page['@type'] = 'WebPage';
		}
	}

	if ( 'sources-and-standards' === $post->post_name ) {
		$page['about'] = array( '@id' => boi_entity_id( 'organization' ) );
	}

	if ( 'sitemap' === $post->post_name ) {
		$page['significantLink'] = array_values(
			array_filter(
				array_map(
					function ( $link ) {
						return $link[1];
					},
					boi_schema_menu_links( 'footer' )
				)
			)
		);
	}

	return array( $page );
}

/**
 * The graph for every view other than an article, the author profile and the
 * Glossary, which their own functions describe.
 *
 * @return array Graph nodes.
 */
function boi_schema_view_entities() {
	global $wp_query;

	if ( is_front_page() ) {
		$page          = boi_schema_page_node( 'WebPage', get_bloginfo( 'name' ) );
		$page['about'] = array( '@id' => boi_entity_id( 'organization' ) );

		$hero = function_exists( 'boi_hero_post' ) ? boi_hero_post() : null;
		$img  = $hero && function_exists( 'boi_article_image_object' ) ? boi_article_image_object( $hero ) : null;

		if ( $img ) {
			$page['primaryImageOfPage'] = $img;
		}

		return array( $page );
	}

	if ( is_page() && ! ( function_exists( 'boi_is_glossary_page' ) && boi_is_glossary_page() ) ) {
		$post = get_queried_object();
		return $post instanceof WP_Post ? boi_schema_page_entities( $post ) : array();
	}

	$posts = isset( $wp_query->posts ) ? $wp_query->posts : array();

	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		$name = $term ? $term->name : wp_strip_all_tags( get_the_archive_title() );
		$page = boi_schema_page_node( 'CollectionPage', $name );

		if ( $term && ! empty( $term->description ) ) {
			$page['about'] = array(
				'@type'       => 'Thing',
				'name'        => boi_plain( $term->name ),
				'description' => boi_plain( $term->description ),
			);
		}

		$list = boi_schema_post_list( $posts, $name );

		if ( $list ) {
			$page['mainEntity'] = array( '@id' => $list['@id'] );
			return array( $page, $list );
		}

		return array( $page );
	}

	if ( is_home() ) {
		$blog = (int) get_option( 'page_for_posts' );
		$name = $blog ? get_the_title( $blog ) : __( 'Reflections', 'bestofislam' );
		$page = boi_schema_page_node( 'CollectionPage', $name );
		$list = boi_schema_post_list( $posts, $name );

		if ( $list ) {
			$page['mainEntity'] = array( '@id' => $list['@id'] );
			return array( $page, $list );
		}

		return array( $page );
	}

	if ( is_search() ) {
		$query = get_search_query();
		/* translators: %s: search terms. */
		$name = sprintf( __( 'Search results for %s', 'bestofislam' ), $query );
		$page = boi_schema_page_node( 'SearchResultsPage', $name );
		$list = boi_schema_post_list( $posts, $name );

		if ( $list ) {
			$page['mainEntity'] = array( '@id' => $list['@id'] );
			return array( $page, $list );
		}

		return array( $page );
	}

	if ( is_404() ) {
		return array( boi_schema_page_node( 'WebPage', __( 'Page not found', 'bestofislam' ) ) );
	}

	if ( is_archive() ) {
		$name = wp_strip_all_tags( get_the_archive_title() );
		$page = boi_schema_page_node( 'CollectionPage', $name );
		$list = boi_schema_post_list( $posts, $name );

		if ( $list ) {
			$page['mainEntity'] = array( '@id' => $list['@id'] );
			return array( $page, $list );
		}

		return array( $page );
	}

	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {
			$page                  = boi_schema_page_node( 'WebPage', get_the_title( $post ) );
			$page['datePublished'] = get_post_time( 'c', true, $post );
			$page['dateModified']  = get_post_modified_time( 'c', true, $post );
			return array( $page );
		}
	}

	return array();
}
