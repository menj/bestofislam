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

	return ! boi_seo_plugin_active();
}

/**
 * Whether an SEO plugin is active and so owns descriptions and canonicals.
 *
 * @return bool
 */
function boi_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
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
 * The longest title tag the site allows, in characters, separator and site
 * name included. Every title reads "<page> | <site name>" and stays under 60.
 *
 * @return int
 */
function boi_title_max_length() {
	return (int) apply_filters( 'boi_title_max_length', 59 );
}

/**
 * The short title tag the seed gives an article, while the article still
 * carries the seeded headline. An owner who renames an article gets a title
 * tag fitted from the new headline instead.
 *
 * @param WP_Post $post The article.
 * @return string Short title, or '' when there is none.
 */
function boi_seed_seo_title( $post ) {
	static $map = null;

	if ( null === $map ) {
		$map = array();

		if ( function_exists( 'boi_seed_listicles' ) ) {
			foreach ( boi_seed_listicles() as $listicle ) {
				if ( ! empty( $listicle['seo_title'] ) ) {
					$map[ $listicle['slug'] ] = array( $listicle['title'], $listicle['seo_title'] );
				}
			}
		}
	}

	if ( ! isset( $map[ $post->post_name ] ) || $post->post_title !== $map[ $post->post_name ][0] ) {
		return '';
	}

	return $map[ $post->post_name ][1];
}

/**
 * Shortens text to a number of characters at a word boundary, dropping any
 * punctuation the cut leaves dangling.
 *
 * @param string $text Plain text.
 * @param int    $max  Maximum length in characters.
 * @return string
 */
function boi_fit_title( $text, $max ) {
	$text = trim( html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES, 'UTF-8' ) );

	if ( $max < 1 || mb_strlen( $text ) <= $max ) {
		return $text;
	}

	// mb_substr is polyfilled by WordPress; the space is a single byte, so the
	// cut at its byte offset cannot split a character.
	$cut   = mb_substr( $text, 0, $max + 1 );
	$space = strrpos( $cut, ' ' );
	$cut   = false !== $space && $space > 0 ? substr( $cut, 0, $space ) : mb_substr( $text, 0, $max );

	return preg_replace( '/[\s,;:.(\x{2013}\x{2014}-]+$/u', '', $cut );
}

/**
 * Builds every title tag as "<page> | <site name>", under 60 characters.
 *
 * The front page leads with its focus ("Objections to Islam, answered"), an
 * article with the short title its seed gives it, a section archive with
 * the section name, and a paged view folds its page number into the first
 * part. Whatever is still too long is cut at a word boundary.
 *
 * @param array $parts Title parts.
 * @return array
 */
function boi_title_parts( $parts ) {
	$site = wp_strip_all_tags( get_bloginfo( 'name', 'display' ) );

	if ( is_front_page() ) {
		// The guide: a home page title can carry the name and the main focus.
		$parts['title'] = apply_filters( 'boi_front_title_focus', __( 'Objections to Islam, answered', 'bestofislam' ) );
	} elseif ( is_tax( 'listicle-topic' ) ) {
		$parts['title'] = single_term_title( '', false );
	} elseif ( is_singular( 'post' ) && empty( $parts['boi_custom'] ) ) {
		// A title the owner set in Rank Math ("boi_custom") keeps its wording.
		$short = boi_seed_seo_title( get_queried_object() );

		if ( '' !== $short ) {
			$parts['title'] = $short;
		}
	} elseif ( function_exists( 'boi_is_glossary_page' ) && boi_is_glossary_page() ) {
		// A Glossary entry whose name is too long for a title tag carries a
		// short form in its "seo_title" field.
		$term = boi_glossary_term_by_slug( get_queried_object()->post_name );

		if ( $term && ! empty( $term['seo_title'] ) ) {
			$parts['title'] = $term['seo_title'];
		}
	}

	$title = isset( $parts['title'] ) ? (string) $parts['title'] : '';

	if ( ! empty( $parts['page'] ) ) {
		/* translators: 1: page title, 2: "Page 2" or similar. */
		$title = sprintf( __( '%1$s, %2$s', 'bestofislam' ), $title, ( function_exists( 'mb_strtolower' ) ? mb_strtolower( wp_strip_all_tags( $parts['page'] ) ) : strtolower( wp_strip_all_tags( $parts['page'] ) ) ) );
	}

	$budget = boi_title_max_length() - mb_strlen( ' | ' . $site );

	return array(
		'title' => boi_fit_title( '' !== $title ? $title : $site, $budget ),
		'site'  => $site,
	);
}
add_filter( 'document_title_parts', 'boi_title_parts' );

/**
 * Prints the Search Console and Bing Webmaster Tools verification tags, so the
 * site can use the free webmaster tools the guide recommends. An SEO plugin has
 * its own fields for these, and the theme leaves them to it.
 *
 * @return void
 */
function boi_print_verification_tags() {
	if ( boi_seo_plugin_active() ) {
		return;
	}

	$options = boi_get_options();

	foreach ( array(
		'verify_google' => 'google-site-verification',
		'verify_bing'   => 'msvalidate.01',
	) as $key => $name ) {
		if ( ! empty( $options[ $key ] ) ) {
			printf( '<meta name="%1$s" content="%2$s">' . "\n", esc_attr( $name ), esc_attr( $options[ $key ] ) );
		}
	}
}
add_action( 'wp_head', 'boi_print_verification_tags', 1 );

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

	// The front page is a static page with no content of its own, so it gets
	// the site's promise, within 130 characters and ending on a call to action.
	if ( is_front_page() ) {
		return __( 'Every objection to Islam stated in its own terms, numbered, and answered from the Quran, hadith and history. Start reading.', 'bestofislam' );
	}

	if ( is_author() ) {
		$author = get_queried_object();
		$bio    = $author ? get_the_author_meta( 'description', $author->ID ) : '';

		if ( '' !== $bio ) {
			return wp_trim_words( wp_strip_all_tags( $bio ), 22, '' );
		}

		return $author ? sprintf( /* translators: %s: author name. */ __( 'Articles by %s on Best of Islam: objections to Islam answered from the sources.', 'bestofislam' ), $author->display_name ) : '';
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
 * The canonical archive of a seeded top-level category: the section
 * (listicle-topic) it mirrors. A subcategory mirrors no section, so it is
 * its own canonical archive, indexed and in the sitemaps. The section archive carries the menus, the section pages and
 * the breadcrumbs, so it is the one search engines index.
 *
 * @return WP_Term|null
 */
function boi_category_canonical_topic() {
	if ( ! is_category() || ! function_exists( 'boi_topic_for_category' ) ) {
		return null;
	}

	return boi_topic_for_category( get_queried_object() );
}

/**
 * Whether this archive would be a thin or duplicate page in the index: a
 * seeded category, which repeats its section, or a tag only one article
 * carries. Such pages stay crawlable (follow) and out of the index.
 *
 * @return bool
 */
function boi_is_duplicate_archive() {
	if ( boi_category_canonical_topic() ) {
		return true;
	}

	if ( is_tag() ) {
		$term = get_queried_object();
		return $term && (int) $term->count < 2;
	}

	return false;
}

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
	// Rank Math prints the robots tag; boi_rank_math_robots() hands it the same rule.
	if ( function_exists( 'boi_rank_math_active' ) && boi_rank_math_active() ) {
		return $robots;
	}

	if ( is_search() || ( is_paged() && ( is_home() || is_archive() ) ) || boi_is_duplicate_archive() ) {
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

	// A seeded category leads to its section, the canonical archive.
	$topic = boi_category_canonical_topic();

	if ( $topic ) {
		$trail[] = array( 'label' => $topic->name, 'url' => get_term_link( $topic ) );
		return $trail;
	}

	// A subcategory leads to the section its parent mirrors, then to itself.
	if ( is_category() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term && $term->parent ) {
			$parent  = get_term( $term->parent, 'category' );
			$section = ( $parent && ! is_wp_error( $parent ) && function_exists( 'boi_topic_for_category' ) ) ? boi_topic_for_category( $parent ) : null;

			if ( $section ) {
				$trail[] = array( 'label' => $section->name, 'url' => get_term_link( $section ) );
			} elseif ( $parent && ! is_wp_error( $parent ) ) {
				$trail[] = array( 'label' => $parent->name, 'url' => get_term_link( $parent ) );
			}

			$trail[] = array( 'label' => $term->name, 'url' => '' );
			return $trail;
		}
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
 * Renders the breadcrumb trail.
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
	$last  = count( $trail ) - 1;

	foreach ( $trail as $i => $crumb ) {
		$items .= $i === $last
			? sprintf( '<li aria-current="page">%s</li>', esc_html( $crumb['label'] ) )
			: sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $crumb['url'] ), esc_html( $crumb['label'] ) );
	}

	// The BreadcrumbList is a node of the page graph (boi_breadcrumb_node()).
	return sprintf(
		'<nav class="boi-breadcrumbs" aria-label="%1$s"><ol>%2$s</ol></nav>',
		esc_attr__( 'Breadcrumb', 'bestofislam' ),
		$items
	);
}

/**
 * The BreadcrumbList for the trail boi_render_breadcrumbs() shows, as a node
 * of the page graph. None on the front page, which shows no trail.
 *
 * @param string $page_url Address of the current page.
 * @return array|null
 */
function boi_breadcrumb_node( $page_url ) {
	if ( is_front_page() ) {
		return null;
	}

	$trail = boi_breadcrumb_trail();

	if ( count( $trail ) < 2 ) {
		return null;
	}

	$list = array();
	$last = count( $trail ) - 1;

	foreach ( $trail as $i => $crumb ) {
		$node = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => boi_plain( $crumb['label'] ),
		);

		if ( $i !== $last && '' !== $crumb['url'] ) {
			$node['item'] = esc_url_raw( $crumb['url'] );
		}

		$list[] = $node;
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $page_url . '#breadcrumb',
		'itemListElement' => $list,
	);
}

/**
 * Prints a canonical link on the indexable views WordPress core leaves
 * without one: section archives, the Reflections index, author archives and
 * their paginated pages. Core already prints one on single posts and pages.
 * Search results are not indexed and get none.
 *
 * @return void
 */
function boi_canonical_link() {
	if ( is_singular() || is_search() || is_404() || boi_seo_plugin_active() ) {
		return;
	}

	$url = '';

	if ( is_tax() || is_category() || is_tag() ) {
		$topic = boi_category_canonical_topic();
		$term  = $topic ? $topic : get_queried_object();
		$link  = $term ? get_term_link( $term ) : '';
		$url  = is_wp_error( $link ) ? '' : $link;
	} elseif ( is_home() ) {
		$page = (int) get_option( 'page_for_posts' );
		$url  = $page ? get_permalink( $page ) : home_url( '/' );
	} elseif ( is_author() ) {
		$author = get_queried_object();
		$url    = $author ? get_author_posts_url( $author->ID ) : '';
	}

	if ( '' === $url ) {
		return;
	}

	$paged = (int) get_query_var( 'paged' );

	if ( $paged > 1 ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' );
	}

	printf( "<link rel=\"canonical\" href=\"%s\" />\n", esc_url( $url ) );
}
add_action( 'wp_head', 'boi_canonical_link', 2 );

/**
 * Category archives in the XML sitemap follow the robots rule: a top-level
 * seeded category repeats its section (the canonical archive) and is
 * noindexed, so it is left out. Every other category stays in: the
 * subcategories, which no section repeats, and any category the owner adds.
 *
 * @param array  $args     Term query arguments.
 * @param string $taxonomy Taxonomy name.
 * @return array
 */
function boi_sitemap_category_args( $args, $taxonomy ) {
	if ( 'category' !== $taxonomy || ! function_exists( 'boi_seed_categories' ) ) {
		return $args;
	}

	$skip = array();

	foreach ( boi_seed_categories() as $slug => $cat ) {
		if ( ! empty( $cat['parent'] ) || empty( $cat['topic'] ) ) {
			continue;
		}

		$term = get_term_by( 'slug', $slug, 'category' );

		if ( $term && ! is_wp_error( $term ) && boi_topic_for_category( $term ) ) {
			$skip[] = (int) $term->term_id;
		}
	}

	if ( $skip ) {
		$args['exclude'] = array_merge( isset( $args['exclude'] ) ? (array) $args['exclude'] : array(), $skip );
	}

	return $args;
}
add_filter( 'wp_sitemaps_taxonomies_query_args', 'boi_sitemap_category_args', 10, 2 );

/**
 * Tag archives with fewer than two articles are not indexed, so they are
 * left out of the sitemap too.
 *
 * @param array  $args     Term query arguments.
 * @param string $taxonomy Taxonomy name.
 * @return array
 */
function boi_sitemap_tag_args( $args, $taxonomy ) {
	if ( 'post_tag' !== $taxonomy ) {
		return $args;
	}

	$skip = array();

	foreach ( get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false ) ) as $term ) {
		if ( (int) $term->count < 2 ) {
			$skip[] = (int) $term->term_id;
		}
	}

	if ( $skip ) {
		$args['exclude'] = $skip;
	}

	return $args;
}
add_filter( 'wp_sitemaps_taxonomies_query_args', 'boi_sitemap_tag_args', 10, 2 );

/**
 * Address of the theme's image sitemap.
 *
 * @return string
 */
function boi_image_sitemap_url() {
	return home_url( '/sitemap-images.xml' );
}

/**
 * Whether the theme serves its own image sitemap. Rank Math lists the images
 * of each post in its own sitemap, so the theme leaves the job to it.
 *
 * @return bool
 */
function boi_image_sitemap_enabled() {
	return ! defined( 'RANK_MATH_VERSION' ) && (bool) get_option( 'blog_public' ) && (bool) apply_filters( 'boi_image_sitemap', true );
}

/**
 * Serves /sitemap-images.xml: every published, listed article with its
 * featured image and the images set in its text, in the image sitemap format
 * the guide recommends.
 *
 * @param WP $wp Request.
 * @return void
 */
function boi_serve_image_sitemap( $wp ) {
	if ( 'sitemap-images.xml' !== $wp->request || ! boi_image_sitemap_enabled() ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex, follow' );

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

	// Every article, 200 at a time: whole posts with their meta in one query
	// per batch, and their featured images primed in one more.
	for ( $paged = 1; ; $paged++ ) {
		$posts = get_posts(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => 200,
				'paged'                  => $paged,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'has_password'           => false,
				'orderby'                => array(
					'modified' => 'DESC',
					'ID'       => 'DESC',
				),
				'suppress_filters'       => false,
			)
		);

		if ( ! $posts ) {
			break;
		}

		$thumbs = array_filter( array_map( 'get_post_thumbnail_id', $posts ) );

		if ( $thumbs && function_exists( '_prime_post_caches' ) ) {
			_prime_post_caches( array_map( 'intval', $thumbs ), false, true );
		}

		boi_print_image_sitemap_urls( $posts );

		if ( count( $posts ) < 200 ) {
			break;
		}
	}

	echo "</urlset>\n";
	exit;
}
add_action( 'parse_request', 'boi_serve_image_sitemap', 1 );

/**
 * Prints the image sitemap entries for one batch of articles.
 *
 * @param WP_Post[] $posts Articles.
 * @return void
 */
function boi_print_image_sitemap_urls( $posts ) {
	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post || ( function_exists( 'boi_is_unlisted' ) && boi_is_unlisted( $post ) ) ) {
			continue;
		}

		$images = array();
		$main   = function_exists( 'boi_article_image_object' ) ? boi_article_image_object( $post ) : null;

		if ( $main && ! empty( $main['url'] ) ) {
			$images[] = $main['url'];
		}

		if ( function_exists( 'boi_inline_image_objects' ) ) {
			foreach ( boi_inline_image_objects( $post ) as $node ) {
				if ( ! empty( $node['url'] ) ) {
					$images[] = $node['url'];
				}
			}
		}

		$images = array_unique( $images );

		if ( ! $images ) {
			continue;
		}

		echo "\t<url>\n\t\t<loc>" . esc_url( get_permalink( $post ) ) . "</loc>\n";

		foreach ( $images as $image ) {
			echo "\t\t<image:image><image:loc>" . esc_url( $image ) . "</image:loc></image:image>\n";
		}

		echo "\t</url>\n";
	}
}

/**
 * Names the image sitemap in robots.txt, next to the core sitemap.
 *
 * @param string $output Existing robots.txt content.
 * @param bool   $public Whether the site is public.
 * @return string
 */
function boi_robots_txt_image_sitemap( $output, $public ) {
	if ( $public && boi_image_sitemap_enabled() ) {
		$output .= "\nSitemap: " . esc_url_raw( boi_image_sitemap_url() ) . "\n";
	}

	return $output;
}
add_filter( 'robots_txt', 'boi_robots_txt_image_sitemap', 20, 2 );
