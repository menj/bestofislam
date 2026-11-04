<?php
/**
 * Structured data for the features Google Search supports.
 *
 * Google's list of supported structured data (June 2026) names the features
 * that apply to this site: Article, Breadcrumb, Image metadata and
 * Organization. Breadcrumbs come from seo.php and the reading lists from
 * schema.php. This file builds the rest:
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
 * each other rather than repeating themselves. inc/schema-graph.php collects
 * the nodes from here and from the other modules and prints that graph once.
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
		'@type'               => 'ImageObject',
		'@id'                 => get_permalink( $post ) . '#primaryimage',
		'url'                 => $url,
		'contentUrl'          => $url,
		'representativeOfPage' => true,
	);

	if ( $width && $height ) {
		$image['width']  = (int) $width;
		$image['height'] = (int) $height;
	}

	return array_merge( $image, boi_image_credit_fields( $credit, $url ) );
}

/**
 * The licence and credit properties of an ImageObject, from an image credit.
 *
 * The creator is the copyright holder only under a licence that keeps the
 * copyright (CC BY and CC BY-SA); a public-domain image has no holder.
 *
 * @param array  $credit Credit: artist, licence, source.
 * @param string $url    The image address, for its encodingFormat.
 * @return array creator, creditText, copyrightHolder, copyrightNotice,
 *               license, acquireLicensePage and encodingFormat, as known.
 */
function boi_image_credit_fields( $credit, $url = '' ) {
	$fields = array();
	$format = boi_image_encoding_format( '' !== $url ? $url : ( isset( $credit['file'] ) ? $credit['file'] : '' ) );

	if ( '' !== $format ) {
		$fields['encodingFormat'] = $format;
	}

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

		if ( ! empty( $credit['artist'] ) && false !== strpos( $licence, '/licenses/by' ) ) {
			$fields['copyrightHolder'] = $fields['creator'];
			$fields['copyrightNotice'] = $credit['artist'] . ', ' . $credit['licence'];
		}
	}

	if ( '' !== boi_image_source_url( isset( $credit['source'] ) ? $credit['source'] : '' ) ) {
		$fields['acquireLicensePage'] = $credit['source'];
	}

	return $fields;
}

/**
 * The media type of an image, from its file extension.
 *
 * @param string $file Address or file name.
 * @return string Empty when the extension is not one the theme uses.
 */
function boi_image_encoding_format( $file ) {
	$ext   = strtolower( pathinfo( (string) wp_parse_url( (string) $file, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
	$types = array(
		'avif' => 'image/avif',
		'webp' => 'image/webp',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'gif'  => 'image/gif',
		'svg'  => 'image/svg+xml',
	);

	return isset( $types[ $ext ] ) ? $types[ $ext ] : '';
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

		$objects[] = array_merge( $node, boi_image_credit_fields( $credit, $url ) );
	}

	return $objects;
}

/**
 * Organization and WebSite, on every page: the publisher and the site that
 * every other node refers to.
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
		$organization['sameAs'] = boi_schema_with_menj( $same_as );
	}

	$website = array(
		'@type'      => 'WebSite',
		'@id'        => boi_entity_id( 'website' ),
		'name'       => boi_plain( get_bloginfo( 'name' ) ),
		'url'        => home_url( '/' ),
		'inLanguage' => get_bloginfo( 'language' ),
		'publisher'  => array( '@id' => boi_entity_id( 'organization' ) ),
		// The search in the header, which every page carries.
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
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

	// One author on every article: the owner, named in the structured data
	// only (owner rule, 4 November 2026). boi_schema_one_author() adds the
	// Person node this points to.
	$article['author'] = array( '@id' => boi_schema_author_id() );

	if ( '' !== $section ) {
		$article['articleSection'] = $section;
	}

	$images = array();
	$image  = boi_article_image_object( $post );

	if ( $image ) {
		$images[] = $image;
	}

	$inline = boi_inline_image_objects( $post );

	foreach ( $inline as $node ) {
		$images[] = array( '@id' => $node['@id'] );
	}

	if ( $images ) {
		$article['image'] = $images;
	}

	return array_merge( array( $article ), $inline );
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

	$url = get_author_posts_url( $author->ID );

	// The owner's archive is about the one author Person.
	if ( function_exists( 'boi_seed_author' ) && (int) $author->ID === (int) boi_seed_author() ) {
		return array(
			array(
				'@type'      => 'ProfilePage',
				'@id'        => $url . '#profilepage',
				'url'        => $url,
				'name'       => $author->display_name,
				'inLanguage' => get_bloginfo( 'language' ),
				'isPartOf'   => array( '@id' => boi_entity_id( 'website' ) ),
				'mainEntity' => array( '@id' => boi_schema_author_id() ),
			),
		);
	}

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

	return array( $profile, $person );
}

/**
 * The @id of the author Person.
 *
 * @return string
 */
function boi_schema_author_id() {
	return home_url( '/#author' );
}

/**
 * A sameAs list with https://menj.bio appended when it is missing. Existing
 * entries keep their order.
 *
 * @param string[] $same_as Existing entries.
 * @return string[]
 */
function boi_schema_with_menj( $same_as ) {
	$same_as = array_values( array_filter( (array) $same_as, 'is_string' ) );
	if ( ! in_array( 'https://menj.bio', $same_as, true ) ) {
		$same_as[] = 'https://menj.bio';
	}
	return $same_as;
}

/**
 * The author Person: Mohd Elfie Nieshaem Juferi, at https://menj.bio. The
 * owner ruled on 4 November 2026 that the structured data names him as the
 * author of every article; nothing visible changes. The owner's other
 * addresses (boi_author_same_as() for the seeded author) stay in sameAs,
 * ahead of https://menj.bio.
 *
 * @return array
 */
function boi_schema_author_node() {
	$same = array();
	$id   = function_exists( 'boi_seed_author' ) ? (int) boi_seed_author() : 0;
	$user = $id ? get_userdata( $id ) : false;
	if ( $user instanceof WP_User ) {
		$same = boi_author_same_as( $user );
	}
	return array(
		'@type'  => 'Person',
		'@id'    => boi_schema_author_id(),
		'name'   => 'Mohd Elfie Nieshaem Juferi',
		'url'    => 'https://menj.bio',
		'sameAs' => boi_schema_with_menj( $same ),
	);
}

/**
 * Types that carry the author: articles and the pages of the site.
 *
 * @return string[]
 */
function boi_schema_authored_types() {
	return array( 'Article', 'BlogPosting', 'NewsArticle', 'ScholarlyArticle', 'WebPage', 'AboutPage', 'ProfilePage', 'FAQPage', 'CollectionPage', 'ContactPage', 'ItemPage', 'SearchResultsPage', 'QAPage' );
}

/**
 * One author across a graph, the theme's own or Rank Math's after the merge.
 *
 * Every author value, at any depth, points to boi_schema_author_id(); every
 * top-level article or page node without an author gets that reference; a
 * Person that Rank Math made for the WordPress user, which nothing points to
 * once the authors are repointed, is dropped and its sameAs entries join the
 * author's; and the author Person is added where the graph lacks it. The
 * publisher is left as it is.
 *
 * @param array $nodes Top-level nodes (a list, or Rank Math's keyed array).
 * @return array
 */
function boi_schema_one_author( $nodes ) {
	if ( ! is_array( $nodes ) || ! $nodes ) {
		return $nodes;
	}
	$id   = boi_schema_author_id();
	$list = array_values( $nodes ) === $nodes;
	$old  = array();
	$walk = function ( $node ) use ( &$walk, &$old, $id ) {
		if ( ! is_array( $node ) ) {
			return $node;
		}
		foreach ( $node as $k => $v ) {
			if ( 'author' === $k && is_array( $v ) ) {
				if ( ! empty( $v['@id'] ) && $id !== $v['@id'] ) {
					$old[ (string) $v['@id'] ] = true;
				}
				$node[ $k ] = array( '@id' => $id );
			} elseif ( is_array( $v ) ) {
				$node[ $k ] = $walk( $v );
			}
		}
		return $node;
	};
	$nodes = $walk( $nodes );

	// Any other reference to a dropped Person points to the author.
	$relink = function ( $n ) use ( &$relink, $old, $id ) {
		if ( ! is_array( $n ) ) {
			return $n;
		}
		if ( 1 === count( $n ) && isset( $n['@id'] ) && isset( $old[ (string) $n['@id'] ] ) ) {
			return array( '@id' => $id );
		}
		foreach ( $n as $k => $v ) {
			if ( is_array( $v ) ) {
				$n[ $k ] = $relink( $v );
			}
		}
		return $n;
	};
	$nodes = $relink( $nodes );
	$extra = array();
	$out   = array();
	$have  = null;
	foreach ( $nodes as $key => $node ) {
		if ( ! is_array( $node ) ) {
			$out[ $key ] = $node;
			continue;
		}
		$types = (array) ( isset( $node['@type'] ) ? $node['@type'] : array() );
		if ( ! empty( $node['@id'] ) && isset( $old[ (string) $node['@id'] ] ) && in_array( 'Person', $types, true ) ) {
			$extra = array_merge( $extra, array_filter( (array) ( isset( $node['sameAs'] ) ? $node['sameAs'] : array() ), 'is_string' ) );
			continue;
		}
		if ( array_intersect( $types, boi_schema_authored_types() ) && empty( $node['author'] ) ) {
			$node['author'] = array( '@id' => $id );
		}
		if ( isset( $node['@id'] ) && $id === $node['@id'] ) {
			$have = $key;
		}
		$out[ $key ] = $node;
	}
	$person = boi_schema_author_node();
	if ( null === $have ) {
		if ( $list ) {
			$out[] = $person;
			$have  = count( $out ) - 1;
		} else {
			$have         = 'boi_author';
			$out[ $have ] = $person;
		}
	} else {
		$out[ $have ] = array_merge( $out[ $have ], $person );
	}
	$same = array_merge( $person['sameAs'], $extra );
	$same = array_values( array_unique( array_diff( $same, array( 'https://menj.bio' ) ) ) );
	$out[ $have ]['sameAs'] = boi_schema_with_menj( $same );
	return $list ? array_values( $out ) : $out;
}
