<?php
/**
 * Structured data for the features Google Search supports.
 *
 * Google's list of supported structured data (June 2026) names the features
 * that apply to this site: Article, Breadcrumb, Image metadata and
 * Organization. Breadcrumbs are emitted by seo.php and the reading lists by
 * schema.php. This file adds the rest:
 *
 *  - Organization and WebSite on every page (completed in schema-pages.php),
 *    with the logo and the follow links as sameAs.
 *  - Article on every article: headline, description, dates, author,
 *    publisher, section, image, word count, the Notes as citations, the
 *    Glossary terms it uses, its numbered lists and its comments, carried
 *    by a WebPage with the breadcrumb and the related articles.
 *  - Image metadata on the article's image: licence, the page where the
 *    licence can be checked, a credit line and the creator, drawn from the
 *    Commons credit the theme records for every seeded image.
 *
 * Everything is one @graph per page, joined by @id, so the entities refer to
 * each other rather than repeating themselves.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plain text for structured data. WordPress renders titles and excerpts with
 * typographic entities such as &#8217;, which JSON would carry literally.
 *
 * @param string $text Rendered text.
 * @return string
 */
function boi_plain( $text ) {
	return trim( html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Stable identifiers for the site-wide entities.
 *
 * @param string $what 'organization' or 'website'.
 * @return string
 */
function boi_entity_id( $what ) {
	return trailingslashit( home_url() ) . '#' . $what;
}

/**
 * The logo: the site icon when one is set, the bundled 512px mark otherwise.
 * Google asks for at least 112px on each side.
 *
 * @return array ImageObject.
 */
function boi_logo_object() {
	$url  = get_site_icon_url( 512 );
	$size = 512;

	if ( ! $url ) {
		$url = BOI_URI . '/assets/images/logo-512.png';
	}

	return array(
		'@type'      => 'ImageObject',
		'@id'        => boi_entity_id( 'logo' ),
		'url'        => $url,
		'contentUrl' => $url,
		'width'      => $size,
		'height'     => $size,
		'caption'    => boi_plain( get_bloginfo( 'name' ) ),
	);
}

/**
 * The licence URL for a Commons licence label, where the label maps to a
 * canonical licence page.
 *
 * @param string $licence Licence label, as recorded in the image credit.
 * @return string Empty when the label is not recognised.
 */
function boi_licence_url( $licence ) {
	$label = strtolower( trim( (string) $licence ) );

	if ( false !== strpos( $label, 'cc0' ) ) {
		return 'https://creativecommons.org/publicdomain/zero/1.0/';
	}

	if ( 0 === strpos( $label, 'public domain' ) || 0 === strpos( $label, 'pd' ) ) {
		return 'https://creativecommons.org/publicdomain/mark/1.0/';
	}

	if ( preg_match( '/^cc[ -]by(-sa)?[ -]([0-9]\.[0-9])/', $label, $m ) ) {
		return 'https://creativecommons.org/licenses/by' . ( empty( $m[1] ) ? '' : '-sa' ) . '/' . $m[2] . '/';
	}

	if ( 0 === strpos( $label, 'pexels' ) ) {
		return 'https://www.pexels.com/license/';
	}

	if ( 0 === strpos( $label, 'pixabay' ) ) {
		return 'https://pixabay.com/service/license-summary/';
	}

	return '';
}

/**
 * The article's image as an ImageObject with licence metadata.
 *
 * Uses the attached featured image when there is one, and the image bundled
 * with the theme when there is not yet. The credit comes from the attachment
 * record or, failing that, the image manifest.
 *
 * @param WP_Post $post Article.
 * @return array|null
 */
function boi_article_image_object( $post ) {
	$id     = (int) get_post_thumbnail_id( $post );
	$url    = '';
	$width  = 0;
	$height = 0;
	$credit = array();

	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );

		if ( $src ) {
			list( $url, $width, $height ) = $src;
		}

		$meta   = get_post_meta( $id, '_boi_credit', true );
		$credit = is_array( $meta ) ? $meta : array();
	}

	if ( '' === $url && function_exists( 'boi_bundled_image' ) ) {
		$bundled = boi_bundled_image( $post );

		if ( $bundled ) {
			$url    = $bundled['url'];
			$width  = $bundled['width'];
			$height = $bundled['height'];
		}
	}

	if ( '' === $url ) {
		return null;
	}

	// The manifest is the record of every seeded image's credit. It describes
	// the seeded image only, so it is not applied to an image the owner chose.
	if ( empty( $credit ) && ! $id && function_exists( 'boi_image_manifest' ) ) {
		$manifest = boi_image_manifest();

		if ( isset( $manifest[ $post->post_name ] ) ) {
			$credit = $manifest[ $post->post_name ];
		}
	}

	$image = array(
		'@type'      => 'ImageObject',
		'url'        => $url,
		'contentUrl' => $url,
	);

	if ( $width && $height ) {
		$image['width']  = (int) $width;
		$image['height'] = (int) $height;
	}

	return array_merge( $image, boi_image_credit_fields( $credit ) );
}

/**
 * The licence and credit properties of an ImageObject, from an image credit.
 *
 * @param array $credit Credit: artist, licence, source.
 * @return array creator, creditText, license and acquireLicensePage, as known.
 */
function boi_image_credit_fields( $credit ) {
	$fields = array();

	if ( ! empty( $credit['artist'] ) ) {
		$fields['creator']    = array(
			'@type' => 'Person',
			'name'  => $credit['artist'],
		);
		$url                  = boi_image_source_url( isset( $credit['source'] ) ? $credit['source'] : '' );
		$fields['creditText'] = '' === $url ? $credit['artist'] : $credit['artist'] . ', via ' . boi_image_source_name( $url );
	}

	if ( ! empty( $credit['licence'] ) ) {
		$licence = boi_licence_url( $credit['licence'] );

		if ( '' !== $licence ) {
			$fields['license'] = $licence;
		}
	}

	if ( '' !== boi_image_source_url( isset( $credit['source'] ) ? $credit['source'] : '' ) ) {
		$fields['acquireLicensePage'] = $credit['source'];
	}

	return $fields;
}

/**
 * The images set in the text of an article, as ImageObjects with licence
 * metadata, so Google Images can show who made each and how it may be used.
 *
 * @param WP_Post $post Article.
 * @return array ImageObjects, in page order.
 */
function boi_inline_image_objects( $post ) {
	$data = function_exists( 'boi_images_data' ) ? boi_images_data() : array();

	if ( empty( $data[ $post->post_name ]['inline'] ) ) {
		return array();
	}

	$objects = array();

	foreach ( $data[ $post->post_name ]['inline'] as $i => $credit ) {
		$file = BOI_DIR . '/assets/images/inline/' . $credit['file'];

		if ( ! file_exists( $file ) ) {
			continue;
		}

		$size = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $file ) : getimagesize( $file );
		$url  = BOI_URI . '/assets/images/inline/' . $credit['file'];
		$node = array(
			'@type'      => 'ImageObject',
			'@id'        => get_permalink( $post ) . '#inline-image-' . ( $i + 1 ),
			'url'        => $url,
			'contentUrl' => $url,
			'caption'    => boi_plain( ! empty( $credit['caption'] ) ? $credit['caption'] : $credit['alt'] ),
		);

		if ( ! empty( $credit['caption'] ) ) {
			$node['description'] = boi_plain( $credit['alt'] );
		}

		if ( $size ) {
			$node['width']  = (int) $size[0];
			$node['height'] = (int) $size[1];
		}

		$objects[] = array_merge( $node, boi_image_credit_fields( $credit ) );
	}

	return $objects;
}

/**
 * Organization and WebSite, for the front page.
 *
 * @return array Graph nodes.
 */
function boi_site_entities() {
	$same_as = array();

	if ( function_exists( 'boi_follow_links' ) ) {
		foreach ( boi_follow_links() as $link ) {
			if ( 'rss' !== $link[0] ) {
				$same_as[] = $link[2];
			}
		}
	}

	$organization = array(
		'@type' => 'Organization',
		'@id'   => boi_entity_id( 'organization' ),
		'name'  => boi_plain( get_bloginfo( 'name' ) ),
		'url'   => home_url( '/' ),
		'logo'  => boi_logo_object(),
	);

	$tagline = boi_plain( get_bloginfo( 'description' ) );

	if ( '' !== $tagline ) {
		$organization['description'] = $tagline;
	}

	if ( $same_as ) {
		$organization['sameAs'] = $same_as;
	}

	$website = array(
		'@type'      => 'WebSite',
		'@id'        => boi_entity_id( 'website' ),
		'name'       => boi_plain( get_bloginfo( 'name' ) ),
		'url'        => home_url( '/' ),
		'inLanguage' => get_bloginfo( 'language' ),
		'publisher'  => array( '@id' => boi_entity_id( 'organization' ) ),
	);

	return array( $organization, $website );
}

/**
 * Article, for a single post.
 *
 * @param WP_Post $post Article.
 * @return array Graph nodes.
 */
function boi_article_entities( $post ) {
	$url     = get_permalink( $post );
	$author  = get_userdata( (int) $post->post_author );
	$terms   = get_the_terms( $post, 'listicle-topic' );
	$section = ( $terms && ! is_wp_error( $terms ) ) ? boi_plain( $terms[0]->name ) : '';
	$title   = boi_plain( get_the_title( $post ) );

	$article = array(
		'@type'            => 'Article',
		'@id'              => $url . '#article',
		'headline'         => mb_strlen( $title ) > 110 ? mb_substr( $title, 0, 109 ) . '…' : $title,
		'url'              => $url,
		'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
		'datePublished'    => get_post_time( 'c', true, $post ),
		'dateModified'     => get_post_modified_time( 'c', true, $post ),
		'inLanguage'       => get_bloginfo( 'language' ),
		'isPartOf'         => array( '@id' => boi_entity_id( 'website' ) ),
		'publisher'        => array(
			'@type' => 'Organization',
			'@id'   => boi_entity_id( 'organization' ),
			'name'  => boi_plain( get_bloginfo( 'name' ) ),
			'url'   => home_url( '/' ),
			'logo'  => boi_logo_object(),
		),
	);

	if ( has_excerpt( $post ) ) {
		$article['description'] = boi_plain( get_the_excerpt( $post ) );
	}

	if ( $author ) {
		$article['author'] = array(
			'@type' => 'Person',
			'name'  => $author->display_name,
			'url'   => get_author_posts_url( $author->ID ),
		);

		$same_as = boi_author_same_as( $author );

		if ( $same_as ) {
			$article['author']['sameAs'] = $same_as;
		}
	}

	if ( '' !== $section ) {
		$article['articleSection'] = $section;
	}

	$article['isAccessibleForFree'] = true;
	$article['copyrightHolder']     = array( '@id' => boi_entity_id( 'organization' ) );
	$article['copyrightYear']       = (int) get_post_time( 'Y', true, $post );

	$plain = boi_plain( preg_replace( '/<!--.*?-->/s', ' ', (string) $post->post_content ) );

	if ( '' !== $plain ) {
		$article['wordCount'] = count( preg_split( '/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY ) );
	}

	$citations = boi_article_citations( $post );

	if ( $citations ) {
		$article['citation'] = $citations;
	}

	$mentions = boi_article_mentions( $plain );

	if ( $mentions ) {
		$article['mentions'] = $mentions;
		$article['keywords'] = implode( ', ', array_slice( wp_list_pluck( $mentions, 'name' ), 0, 12 ) );
	}

	if ( function_exists( 'boi_item_list_ids' ) ) {
		$parts = boi_item_list_ids( $post );

		if ( $parts ) {
			$article['hasPart'] = array_map(
				function ( $id ) {
					return array( '@id' => $id );
				},
				$parts
			);
		}
	}

	$comments = boi_article_comments( $post );

	if ( $comments ) {
		$article['commentCount'] = (int) get_comments_number( $post );
		$article['comment']      = $comments;
	}

	$images = array();
	$image  = boi_article_image_object( $post );

	if ( $image ) {
		$image['@id'] = $url . '#primaryimage';
		$images[]     = $image;
	}

	$inline = boi_inline_image_objects( $post );

	foreach ( $inline as $node ) {
		$images[] = array( '@id' => $node['@id'] );
	}

	if ( $images ) {
		$article['image'] = $images;
	}

	// The page that carries the article: its breadcrumb, the picture that
	// represents it, and the articles it points readers to next.
	$page = function_exists( 'boi_schema_page_node' )
		? boi_schema_page_node( 'WebPage', $title )
		: array(
			'@type' => 'WebPage',
			'name'  => $title,
		);

	$page['@id']           = $url . '#webpage';
	$page['url']           = $url;
	$page['mainEntity']    = array( '@id' => $url . '#article' );
	$page['datePublished'] = $article['datePublished'];
	$page['dateModified']  = $article['dateModified'];

	if ( $image ) {
		$page['primaryImageOfPage'] = array( '@id' => $url . '#primaryimage' );
	}

	$related = boi_article_related_links( $post );

	if ( $related ) {
		$page['relatedLink'] = $related;
	}

	$page['potentialAction'] = array(
		array(
			'@type'  => 'ReadAction',
			'target' => array( $url ),
		),
	);

	if ( comments_open( $post ) ) {
		$page['potentialAction'][] = array(
			'@type'  => 'CommentAction',
			'name'   => __( 'Comment', 'bestofislam' ),
			'target' => array( $url . '#respond' ),
		);
	}

	return array_merge( array( $page, $article ), $inline );
}

/**
 * The sources an article cites: the items of its Notes list.
 *
 * @param WP_Post $post Article.
 * @return string[] Citations as plain text, in order.
 */
function boi_article_citations( $post ) {
	$content = (string) $post->post_content;
	$at      = strripos( $content, '>Notes</h' );

	if ( false === $at || ! preg_match( '#<ol[^>]*>(.*?)</ol>#is', substr( $content, $at ), $m ) ) {
		return array();
	}

	$out = array();

	foreach ( preg_split( '#</li>#i', $m[1] ) as $item ) {
		$text = boi_plain( $item );

		if ( '' !== $text ) {
			$out[] = $text;
		}
	}

	return $out;
}

/**
 * The Glossary terms an article uses, as DefinedTerms joined by @id to the
 * term nodes on their own Glossary pages: the terms the Glossary linker can
 * link in the text.
 *
 * @param string $plain The article as plain text.
 * @return array[] DefinedTerm references, at most forty.
 */
function boi_article_mentions( $plain ) {
	if ( '' === $plain || ! function_exists( 'boi_gl_map' ) ) {
		return array();
	}

	$quotes = array( "\u{2019}", "\u{2018}" );
	$text   = str_replace( $quotes, "'", $plain );
	$found  = array();

	foreach ( boi_gl_map() as $item ) {
		if ( isset( $found[ $item['id'] ] ) || count( $found ) >= 40 ) {
			continue;
		}

		$phrase = str_replace( $quotes, "'", $item['phrase'] );

		if ( preg_match( '/(?<![\p{L}\p{N}])' . preg_quote( $phrase, '/' ) . '(?![\p{L}\p{N}])/iu', $text ) ) {
			$term                 = get_post( $item['id'] );
			$found[ $item['id'] ] = array(
				'@type' => 'DefinedTerm',
				'@id'   => $item['url'] . '#term',
				'name'  => $term ? boi_plain( get_the_title( $term ) ) : $item['phrase'],
				'url'   => $item['url'],
			);
		}
	}

	return array_values( $found );
}

/**
 * The articles the Keep reading list points to after this one.
 *
 * @param WP_Post $post Article.
 * @return string[] Permalinks.
 */
function boi_article_related_links( $post ) {
	if ( ! function_exists( 'boi_related_map' ) ) {
		return array();
	}

	$map   = boi_related_map();
	$links = array();

	foreach ( isset( $map[ $post->post_name ] ) ? $map[ $post->post_name ] : array() as $slug ) {
		$item = get_page_by_path( $slug, OBJECT, 'post' );

		if ( $item && 'publish' === $item->post_status && ( ! function_exists( 'boi_is_unlisted' ) || ! boi_is_unlisted( $item ) ) ) {
			$links[] = get_permalink( $item );
		}
	}

	return $links;
}

/**
 * The approved comments on an article, the most recent twenty.
 *
 * @param WP_Post $post Article.
 * @return array[] Comment nodes.
 */
function boi_article_comments( $post ) {
	if ( ! get_comments_number( $post ) ) {
		return array();
	}

	$comments = get_comments(
		array(
			'post_id' => $post->ID,
			'status'  => 'approve',
			'type'    => 'comment',
			'number'  => 20,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
		)
	);

	$out = array();

	foreach ( $comments as $comment ) {
		$text = boi_plain( $comment->comment_content );

		if ( '' === $text ) {
			continue;
		}

		$link  = get_comment_link( $comment );
		$out[] = array(
			'@type'         => 'Comment',
			'@id'           => $link,
			'url'           => $link,
			'text'          => $text,
			'datePublished' => mysql2date( 'c', $comment->comment_date_gmt, false ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => boi_plain( $comment->comment_author ),
			),
		);
	}

	return $out;
}

/**
 * The site owner's profiles elsewhere, for the author's sameAs: the Author
 * URI from style.css, the owner's own addresses, and the website the user
 * profile records. Other authors get only their profile website.
 *
 * @param WP_User $author Author.
 * @return string[] Absolute URLs, in a stable order.
 */
function boi_author_same_as( $author ) {
	$links = array();

	if ( function_exists( 'boi_seed_author' ) && (int) $author->ID === (int) boi_seed_author() ) {
		$theme = wp_get_theme();
		$links = array(
			(string) $theme->get( 'AuthorURI' ),
			'https://ms.wikipedia.org/wiki/Mohd_Elfie_Nieshaem_Juferi',
			'https://my.linkedin.com/in/mohd-elfie-nieshaem-bin-juferi',
			'https://menj.blog/',
		);
	}

	$links[] = (string) $author->user_url;

	// A profile saved as http://this-site is given as the site really is.
	$home = wp_parse_url( home_url( '/' ) );
	foreach ( $links as $i => $link ) {
		if ( isset( $home['scheme'], $home['host'] ) && 'https' === $home['scheme'] && strtolower( (string) wp_parse_url( $link, PHP_URL_HOST ) ) === strtolower( $home['host'] ) ) {
			$links[ $i ] = set_url_scheme( $link, 'https' );
		}
	}

	$links = array_values( array_unique( array_filter( array_map( 'esc_url_raw', $links ) ) ) );

	return (array) apply_filters( 'boi_author_same_as', $links, $author );
}

/**
 * ProfilePage for an author archive: a page about one person tied to the site.
 *
 * @param WP_User|null $author Author.
 * @return array Graph nodes, empty when the author has nothing to show.
 */
function boi_profile_entities( $author ) {
	if ( ! $author instanceof WP_User || ! count_user_posts( $author->ID, 'post', true ) ) {
		return array();
	}

	$url    = get_author_posts_url( $author->ID );
	$person = array(
		'@type' => 'Person',
		'@id'   => $url . '#person',
		'name'  => $author->display_name,
		'url'   => $url,
	);

	$bio = boi_plain( get_the_author_meta( 'description', $author->ID ) );

	if ( '' !== $bio ) {
		$person['description'] = $bio;
	}

	$same_as = boi_author_same_as( $author );

	if ( $same_as ) {
		$person['sameAs'] = $same_as;
	}

	$profile = array(
		'@type'      => 'ProfilePage',
		'@id'        => $url . '#profilepage',
		'url'        => $url,
		'name'       => $author->display_name,
		'inLanguage' => get_bloginfo( 'language' ),
		'isPartOf'   => array( '@id' => boi_entity_id( 'website' ) ),
		'mainEntity' => array( '@id' => $person['@id'] ),
	);

	if ( function_exists( 'boi_schema_has_breadcrumb' ) && boi_schema_has_breadcrumb() ) {
		$profile['breadcrumb'] = array( '@id' => boi_schema_breadcrumb_id() );
	}

	return array( $profile, $person );
}

/**
 * Prints the graph for the current page.
 *
 * @return void
 */
function boi_print_structured_data() {
	// Rank Math prints its own graph; boi_rank_math_json_ld() adds to it.
	if ( function_exists( 'boi_rank_math_schema_active' ) && boi_rank_math_schema_active() ) {
		return;
	}

	// Every page names the site and the organisation it belongs to, and
	// carries the site's menus (schema-pages.php).
	$nodes = function_exists( 'boi_schema_site_nodes' ) ? boi_schema_site_nodes() : boi_site_entities();

	if ( function_exists( 'boi_schema_navigation_nodes' ) ) {
		$nodes = array_merge( $nodes, boi_schema_navigation_nodes() );
	}

	if ( is_singular( 'post' ) ) {
		$post = get_post();

		if ( $post ) {
			$nodes = array_merge( $nodes, boi_article_entities( $post ) );
		}
	} elseif ( is_author() ) {
		$nodes = array_merge( $nodes, boi_profile_entities( get_queried_object() ) );
	} elseif ( ! ( function_exists( 'boi_is_glossary_page' ) && boi_is_glossary_page() ) && function_exists( 'boi_schema_view_entities' ) ) {
		// The Glossary prints its own page and term nodes (glossary.php).
		$nodes = array_merge( $nodes, boi_schema_view_entities() );
	}

	echo "\n<script type=\"application/ld+json\">" . boi_json_ld(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $nodes,
		)
	) . "</script>\n";
}
add_action( 'wp_head', 'boi_print_structured_data', 19 );
