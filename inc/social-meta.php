<?php
/**
 * Link previews: Open Graph and Twitter Card tags.
 *
 * When an article is shared on WhatsApp, Facebook, X, LinkedIn or Telegram,
 * these tags give the preview its title, summary and picture. Without them a
 * shared link shows a bare address. The title, description and address are
 * the ones the theme already gives search engines, so a preview never says
 * something the page does not.
 *
 * The site's images are AVIF, which most link-preview services do not show.
 * The preview picture is therefore a JPEG copy of the article's featured
 * image, made once and kept in uploads/boi-share/, and made again only when
 * the featured image changes.
 *
 * An SEO plugin prints its own preview tags, so the theme stands aside when
 * one is active.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Width of the preview picture. Facebook and LinkedIn recommend 1200 pixels.
 */
const BOI_SHARE_WIDTH = 1200;

/**
 * The image a preview uses for a post: its attached featured image, or the
 * image bundled with the theme while the attachment is not yet made.
 *
 * @param WP_Post $post Post.
 * @return array|null Array( path, url, alt ), or null.
 */
function boi_share_source( $post ) {
	$id = (int) get_post_thumbnail_id( $post );

	if ( $id ) {
		$path = get_attached_file( $id );

		if ( $path && file_exists( $path ) ) {
			return array(
				'path' => $path,
				'url'  => (string) wp_get_attachment_image_url( $id, 'full' ),
				'alt'  => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			);
		}
	}

	$bundled = function_exists( 'boi_bundled_image' ) ? boi_bundled_image( $post ) : null;

	if ( $bundled ) {
		return array(
			'path' => BOI_DIR . '/assets/images/featured/' . $bundled['file'],
			'url'  => $bundled['url'],
			'alt'  => isset( $bundled['alt'] ) ? (string) $bundled['alt'] : '',
		);
	}

	return null;
}

/**
 * The preview picture for a post, as a JPEG or PNG that link previews show.
 *
 * A JPEG or PNG source is used as it is. Any other format is copied once to
 * a JPEG no wider than BOI_SHARE_WIDTH; the file name carries a hash of the
 * source's path and modification time, so a changed image gets a new copy.
 *
 * @param WP_Post $post Post.
 * @return array|null Array( url, width, height, type, alt ), or null.
 */
function boi_share_image( $post ) {
	static $cache = array();

	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ] ? $cache[ $post->ID ] : null;
	}

	$cache[ $post->ID ] = false;
	$source             = boi_share_source( $post );

	if ( ! $source || ! file_exists( $source['path'] ) ) {
		return null;
	}

	$type = wp_check_filetype( $source['path'] );

	if ( in_array( $type['type'], array( 'image/jpeg', 'image/png' ), true ) ) {
		$size = wp_getimagesize( $source['path'] );

		$cache[ $post->ID ] = array(
			'url'    => $source['url'],
			'width'  => $size ? (int) $size[0] : 0,
			'height' => $size ? (int) $size[1] : 0,
			'type'   => $type['type'],
			'alt'    => $source['alt'],
		);

		return $cache[ $post->ID ];
	}

	$uploads = wp_upload_dir( null, false );

	if ( ! empty( $uploads['error'] ) ) {
		return null;
	}

	$key  = substr( md5( $source['path'] . '|' . (string) filemtime( $source['path'] ) ), 0, 10 );
	$name = sanitize_file_name( $post->post_name . '-' . $key . '.jpg' );
	$dir  = trailingslashit( $uploads['basedir'] ) . 'boi-share';
	$file = $dir . '/' . $name;

	if ( ! file_exists( $file ) ) {
		$editor = wp_get_image_editor( $source['path'] );

		if ( is_wp_error( $editor ) || ! wp_mkdir_p( $dir ) ) {
			return null;
		}

		$editor->resize( BOI_SHARE_WIDTH, null, false );
		$editor->set_quality( 82 );
		$saved = $editor->save( $file, 'image/jpeg' );

		if ( is_wp_error( $saved ) ) {
			return null;
		}
	}

	$size = wp_getimagesize( $file );

	$cache[ $post->ID ] = array(
		'url'    => trailingslashit( $uploads['baseurl'] ) . 'boi-share/' . $name,
		'width'  => $size ? (int) $size[0] : 0,
		'height' => $size ? (int) $size[1] : 0,
		'type'   => 'image/jpeg',
		'alt'    => $source['alt'],
	);

	return $cache[ $post->ID ];
}

/**
 * The address a preview links to: the canonical address of the view.
 *
 * @return string
 */
function boi_share_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_singular() ) {
		$url = wp_get_canonical_url();
		return $url ? $url : (string) get_permalink();
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? '' : $link;
	}

	if ( is_author() ) {
		$author = get_queried_object();
		return $author ? get_author_posts_url( $author->ID ) : '';
	}

	if ( is_home() ) {
		$page = (int) get_option( 'page_for_posts' );
		return $page ? get_permalink( $page ) : home_url( '/' );
	}

	return '';
}

/**
 * Prints the Open Graph and Twitter Card tags.
 *
 * @return void
 */
function boi_print_social_meta() {
	if ( boi_seo_plugin_active() || is_search() || is_404() || is_feed() ) {
		return;
	}

	$url = boi_share_url();

	if ( '' === $url ) {
		return;
	}

	$is_article = is_singular( 'post' );
	$title      = wp_get_document_title();
	$desc       = function_exists( 'boi_meta_description' ) ? boi_meta_description() : '';

	// The picture: the article's own, or on any other view the article whose
	// image the front page shows behind its headline.
	$image_post = $is_article ? get_queried_object() : ( function_exists( 'boi_hero_post' ) ? boi_hero_post() : null );
	$image      = $image_post instanceof WP_Post ? boi_share_image( $image_post ) : null;

	$tags = array(
		array( 'property', 'og:site_name', wp_strip_all_tags( get_bloginfo( 'name', 'display' ) ) ),
		array( 'property', 'og:locale', get_locale() ),
		array( 'property', 'og:type', $is_article ? 'article' : 'website' ),
		array( 'property', 'og:title', $title ),
		array( 'property', 'og:description', $desc ),
		array( 'property', 'og:url', $url ),
	);

	if ( $image ) {
		$tags[] = array( 'property', 'og:image', $image['url'] );
		$tags[] = array( 'property', 'og:image:type', $image['type'] );

		if ( $image['width'] && $image['height'] ) {
			$tags[] = array( 'property', 'og:image:width', (string) $image['width'] );
			$tags[] = array( 'property', 'og:image:height', (string) $image['height'] );
		}

		$tags[] = array( 'property', 'og:image:alt', $image['alt'] );
	}

	if ( $is_article ) {
		$post  = get_queried_object();
		$terms = get_the_terms( $post, 'listicle-topic' );

		$tags[] = array( 'property', 'article:published_time', get_post_time( 'c', true, $post ) );
		$tags[] = array( 'property', 'article:modified_time', get_post_modified_time( 'c', true, $post ) );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$tags[] = array( 'property', 'article:section', $terms[0]->name );
		}
	}

	$tags[] = array( 'name', 'twitter:card', $image ? 'summary_large_image' : 'summary' );
	$tags[] = array( 'name', 'twitter:title', $title );
	$tags[] = array( 'name', 'twitter:description', $desc );

	if ( $image ) {
		$tags[] = array( 'name', 'twitter:image', $image['url'] );
		$tags[] = array( 'name', 'twitter:image:alt', $image['alt'] );
	}

	foreach ( $tags as $tag ) {
		if ( '' === (string) $tag[2] ) {
			continue;
		}

		printf(
			"<meta %s=\"%s\" content=\"%s\" />\n",
			esc_attr( $tag[0] ),
			esc_attr( $tag[1] ),
			'og:url' === $tag[1] || 'og:image' === $tag[1] || 'twitter:image' === $tag[1] ? esc_url( $tag[2] ) : esc_attr( wp_strip_all_tags( $tag[2] ) )
		);
	}
}
add_action( 'wp_head', 'boi_print_social_meta', 3 );
