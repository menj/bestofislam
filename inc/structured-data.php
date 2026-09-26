<?php
/**
 * Structured data for the features Google Search supports.
 *
 * Google's list of supported structured data (June 2026) names the features
 * that apply to this site: Article, Breadcrumb, Image metadata and
 * Organization. Breadcrumbs are emitted by seo.php and the reading lists by
 * schema.php. This file adds the rest:
 *
 *  - Organization and WebSite on the front page, with the logo and the
 *    follow links as sameAs.
 *  - Article on every article: headline, description, dates, author,
 *    publisher, section and image.
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

	// The manifest is the record of every seeded image's credit.
	if ( empty( $credit ) && function_exists( 'boi_image_manifest' ) ) {
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

	if ( ! empty( $credit['artist'] ) ) {
		$image['creator']    = array(
			'@type' => 'Person',
			'name'  => $credit['artist'],
		);
		$image['creditText'] = $credit['artist'] . ', via Wikimedia Commons';
	}

	if ( ! empty( $credit['licence'] ) ) {
		$licence = boi_licence_url( $credit['licence'] );

		if ( '' !== $licence ) {
			$image['license'] = $licence;
		}
	}

	if ( ! empty( $credit['source'] ) ) {
		$image['acquireLicensePage'] = $credit['source'];
	}

	return $image;
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
		'mainEntityOfPage' => $url,
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
	}

	if ( '' !== $section ) {
		$article['articleSection'] = $section;
	}

	$image = boi_article_image_object( $post );

	if ( $image ) {
		$article['image'] = array( $image );
	}

	return array( $article );
}

/**
 * Prints the graph for the current page.
 *
 * @return void
 */
function boi_print_structured_data() {
	$nodes = array();

	if ( is_front_page() ) {
		$nodes = boi_site_entities();
	} elseif ( is_singular( 'post' ) ) {
		$post = get_post();

		if ( $post ) {
			$nodes = boi_article_entities( $post );
		}
	}

	if ( empty( $nodes ) ) {
		return;
	}

	echo "\n<script type=\"application/ld+json\">" . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $nodes,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . "</script>\n";
}
add_action( 'wp_head', 'boi_print_structured_data', 19 );
