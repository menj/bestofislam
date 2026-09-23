<?php
/**
 * Search engine practices.
 *
 * Each function below implements one practice from the Google Search Engine
 * Optimization Starter Guide: unique titles, a description per page, a
 * breadcrumb trail, search results kept out of the index, and robots
 * directives. Nothing here manipulates ranking; it makes the site legible.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the theme should emit its own meta description.
 *
 * An installed SEO plugin will emit one already, and two descriptions on a
 * page is worse than none. The theme steps back when it detects one.
 *
 * @return bool
 */
function boi_should_emit_description() {
	$options = boi_get_options();

	if ( empty( $options['seo_description'] ) ) {
		return false;
	}

	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) {
		return false;
	}

	return true;
}

/**
 * Title separator: a spaced pipe reads cleanly in a results page.
 *
 * @return string
 */
function boi_title_separator() {
	return '|';
}
add_filter( 'document_title_separator', 'boi_title_separator' );

/**
 * Removes the site tagline from the front page title so the title is the
 * site name alone, and keeps it unique against every other page.
 *
 * @param array $parts Title parts.
 * @return array
 */
function boi_title_parts( $parts ) {
	if ( is_front_page() ) {
		unset( $parts['tagline'] );
	}

	if ( is_tax( 'listicle-topic' ) ) {
		$parts['title'] = single_term_title( '', false );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'boi_title_parts' );

/**
 * Builds the description for the current view.
 *
 * Singular views use the excerpt, which the seed keeps within 130 characters.
 * Section archives use the term description. Everything else falls back to
 * the site tagline. Search results get none, since they are not indexed.
 *
 * @return string
 */
function boi_meta_description() {
	if ( is_search() ) {
		return '';
	}

	if ( is_singular() ) {
		$post = get_post();

		if ( $post && has_excerpt( $post ) ) {
			return wp_strip_all_tags( get_the_excerpt( $post ) );
		}

		if ( $post ) {
			return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 22, '' );
		}
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$desc = term_description();

		if ( '' !== $desc ) {
			return wp_strip_all_tags( $desc );
		}
	}

	if ( is_home() ) {
		return __( 'Essays and shorter pieces from Best of Islam.', 'bestofislam' );
	}

	return get_bloginfo( 'description', 'display' );
}

/**
 * Prints the description meta tag.
 *
 * @return void
 */
function boi_print_meta_description() {
	if ( ! boi_should_emit_description() ) {
		return;
	}

	$desc = boi_meta_description();

	if ( '' === $desc ) {
		return;
	}

	if ( function_exists( 'mb_substr' ) && mb_strlen( $desc ) > 160 ) {
		$desc = rtrim( mb_substr( $desc, 0, 157 ) ) . '…';
	}

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
}
add_action( 'wp_head', 'boi_print_meta_description', 1 );

/**
 * Keeps search results and paginated archives out of the index.
 *
 * The guide is explicit that search result pages should not be crawled. Paged
 * archives beyond the first page are near-duplicates of the first.
 *
 * @param array $robots Directives.
 * @return array
 */
function boi_robots( $robots ) {
	if ( is_search() || ( is_paged() && ( is_home() || is_archive() ) ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}

	return $robots;
}
add_filter( 'wp_robots', 'boi_robots' );

/**
 * Disallows the search endpoint in robots.txt.
 *
 * @param string $output Existing robots.txt content.
 * @param bool   $public Whether the site is public.
 * @return string
 */
function boi_robots_txt( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}

	$output .= "Disallow: /?s=\n";
	$output .= "Disallow: /search/\n";

	return $output;
}
add_filter( 'robots_txt', 'boi_robots_txt', 10, 2 );

/**
 * Includes the section taxonomy in the core XML sitemap and excludes users.
 *
 * @param bool   $enabled Whether the provider is on.
 * @param string $name    Provider name.
 * @return bool
 */
function boi_sitemap_providers( $enabled, $name ) {
	if ( 'users' === $name ) {
		return false;
	}

	return $enabled;
}
add_filter( 'wp_sitemaps_add_provider', 'boi_sitemap_providers', 10, 2 );

/**
 * Registers the breadcrumb block.
 *
 * @return void
 */
function boi_register_breadcrumb_block() {
	register_block_type(
		'bestofislam/breadcrumbs',
		array( 'render_callback' => 'boi_render_breadcrumbs' )
	);
}
add_action( 'init', 'boi_register_breadcrumb_block' );

/**
 * Builds the breadcrumb trail as a list of label and URL pairs.
 *
 * @return array
 */
function boi_breadcrumb_trail() {
	$trail = array(
		array( 'label' => __( 'Home', 'bestofislam' ), 'url' => home_url( '/' ) ),
	);

	if ( is_home() ) {
		$trail[] = array( 'label' => get_the_title( get_option( 'page_for_posts' ) ), 'url' => '' );
		return $trail;
	}

	if ( is_singular( 'post' ) ) {
		$terms = get_the_terms( get_the_ID(), 'listicle-topic' );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$term = $terms[0];

			if ( $term->parent ) {
				$parent = get_term( $term->parent, 'listicle-topic' );

				if ( $parent && ! is_wp_error( $parent ) ) {
					$trail[] = array( 'label' => $parent->name, 'url' => get_term_link( $parent ) );
				}
			}

			$trail[] = array( 'label' => $term->name, 'url' => get_term_link( $term ) );
		}

		$trail[] = array( 'label' => get_the_title(), 'url' => '' );
		return $trail;
	}

	if ( is_singular( 'page' ) ) {
		$trail[] = array( 'label' => get_the_title(), 'url' => '' );
		return $trail;
	}

	if ( is_tax( 'listicle-topic' ) ) {
		$term = get_queried_object();

		if ( $term && $term->parent ) {
			$parent = get_term( $term->parent, 'listicle-topic' );

			if ( $parent && ! is_wp_error( $parent ) ) {
				$trail[] = array( 'label' => $parent->name, 'url' => get_term_link( $parent ) );
			}
		}

		$trail[] = array( 'label' => $term->name, 'url' => '' );
		return $trail;
	}

	if ( is_tag() ) {
		$trail[] = array( 'label' => __( 'Tags', 'bestofislam' ), 'url' => '' );
		$trail[] = array( 'label' => single_tag_title( '', false ), 'url' => '' );
		return $trail;
	}

	if ( is_archive() ) {
		$trail[] = array( 'label' => wp_strip_all_tags( get_the_archive_title() ), 'url' => '' );
		return $trail;
	}

	if ( is_search() ) {
		$trail[] = array( 'label' => __( 'Search', 'bestofislam' ), 'url' => '' );
		return $trail;
	}

	if ( is_404() ) {
		$trail[] = array( 'label' => __( 'Page not found', 'bestofislam' ), 'url' => '' );
	}

	return $trail;
}

/**
 * Renders the breadcrumb trail with its BreadcrumbList graph.
 *
 * @return string
 */
function boi_render_breadcrumbs() {
	if ( is_front_page() ) {
		return '';
	}

	$trail = boi_breadcrumb_trail();

	if ( count( $trail ) < 2 ) {
		return '';
	}

	$items = '';
	$graph = array();
	$last  = count( $trail ) - 1;

	foreach ( $trail as $i => $crumb ) {
		$is_last = $i === $last;

		$items .= $is_last
			? sprintf( '<li aria-current="page">%s</li>', esc_html( $crumb['label'] ) )
			: sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $crumb['url'] ), esc_html( $crumb['label'] ) );

		$node = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => wp_strip_all_tags( $crumb['label'] ),
		);

		if ( ! $is_last && '' !== $crumb['url'] ) {
			$node['item'] = esc_url_raw( $crumb['url'] );
		}

		$graph[] = $node;
	}

	$json = wp_json_encode(
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);

	return sprintf(
		'<nav class="boi-breadcrumbs" aria-label="%1$s"><ol>%2$s</ol></nav><script type="application/ld+json">%3$s</script>',
		esc_attr__( 'Breadcrumb', 'bestofislam' ),
		$items,
		$json
	);
}
