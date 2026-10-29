<?php
/**
 * Featured images.
 *
 * Every seeded article ships with an image drawn from Wikimedia Commons.
 * Each file was checked against its own Commons metadata and is in the
 * public domain or dedicated under CC0. None depicts a prophet: pieces about
 * Jesus, Abraham, Moses and Muhammad are illustrated with manuscripts, places
 * and objects. Credit is shown beneath each image regardless of licence.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The image manifest, keyed by article slug.
 *
 * @return array
 */
function boi_image_manifest() {
	return array_merge( boi_image_manifest_base(), boi_images_data_featured() );
}

/**
 * The hand-listed manifest entries, from Wikimedia Commons, kept as data in
 * inc/data/image-credits.json.
 *
 * @return array
 */
function boi_image_manifest_base() {
	return boi_read_data( 'image-credits' );
}

/**
 * Sideloads bundled images into the media library and sets each as the
 * featured image of its article. Idempotent: an article that already has a
 * featured image, whether ours or one the owner chose, is left alone.
 *
 * Works in small batches. WordPress generates several resized copies of each
 * image, so loading all of them in one request can outrun a host's time
 * limit. Each call attaches at most $limit images and records its progress
 * after every one, and the theme keeps calling it on later requests until
 * nothing is left to attach.
 *
 * @param int $limit Most images to attach in this call.
 * @return int Number of images attached.
 */
function boi_attach_featured_images( $limit = 3 ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attached = 0;
	$map      = (array) get_option( 'boi_image_attachments', array() );

	$pending = false;

	foreach ( boi_image_manifest() as $slug => $image ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		if ( ! $post || has_post_thumbnail( $post ) ) {
			continue;
		}

		if ( $attached >= $limit ) {
			$pending = true;
			break;
		}

		// Reuse an attachment created on an earlier run.
		if ( ! empty( $map[ $slug ] ) && get_post( (int) $map[ $slug ] ) ) {
			set_post_thumbnail( $post, (int) $map[ $slug ] );
			++$attached;
			continue;
		}

		$source = BOI_DIR . '/assets/images/featured/' . $image['file'];

		if ( ! file_exists( $source ) ) {
			continue;
		}

		$tmp = wp_tempnam( $image['file'] );

		if ( ! $tmp ) {
			continue;
		}

		if ( ! copy( $source, $tmp ) ) {
			// wp_tempnam() already created the file, so remove it.
			wp_delete_file( $tmp );
			continue;
		}

		$id = media_handle_sideload(
			array(
				'name'     => $image['file'],
				'tmp_name' => $tmp,
			),
			$post->ID,
			$image['alt']
		);

		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			continue;
		}

		update_post_meta( $id, '_wp_attachment_image_alt', $image['alt'] );
		update_post_meta( $id, '_boi_credit', array( 'artist' => $image['artist'], 'licence' => $image['licence'], 'source' => $image['source'] ) );
		wp_update_post(
			array(
				'ID'           => $id,
				'post_excerpt' => boi_credit_text( $image ),
			)
		);

		set_post_thumbnail( $post, $id );
		$map[ $slug ] = $id;
		++$attached;
		update_option( 'boi_image_attachments', $map, false );
	}

	update_option( 'boi_images_pending', $pending ? 1 : 0, false );

	return $attached;
}

/**
 * Continues attaching images on later requests while any remain, one small
 * batch per request, then stops checking.
 *
 * @return void
 */
function boi_continue_featured_images() {
	if ( ! get_option( 'boi_images_pending' ) || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	// One request at a time, across both this and the population routine.
	if ( ! boi_images_lock_take() ) {
		return;
	}

	boi_attach_featured_images();
	boi_images_lock_release();
}

/**
 * Takes the image-import lock. add_option() fails if the option exists, which
 * makes taking it a single atomic insert; a transient would let two requests
 * both see "unlocked" and sideload the same image twice. A lock older than a
 * minute is treated as left behind by a request that died.
 *
 * @return bool Whether the lock is now held.
 */
function boi_images_lock_take() {
	if ( add_option( 'boi_images_lock', time(), '', false ) ) {
		return true;
	}

	wp_cache_delete( 'boi_images_lock', 'options' );

	if ( time() - (int) get_option( 'boi_images_lock' ) <= MINUTE_IN_SECONDS ) {
		return false;
	}

	delete_option( 'boi_images_lock' );

	return (bool) add_option( 'boi_images_lock', time(), '', false );
}

/**
 * Releases the image-import lock.
 *
 * @return void
 */
function boi_images_lock_release() {
	delete_option( 'boi_images_lock' );
}
add_action( 'init', 'boi_continue_featured_images', 30 );

/**
 * Plain-text credit line.
 *
 * @param array $image Manifest entry.
 * @return string
 */
function boi_credit_text( $image ) {
	if ( '' === boi_image_source_url( isset( $image['source'] ) ? $image['source'] : '' ) ) {
		/* translators: 1: artist, 2: licence. */
		return sprintf( __( 'Image: %1$s. %2$s.', 'bestofislam' ), $image['artist'], $image['licence'] );
	}

	return sprintf(
		/* translators: 1: artist, 2: licence, 3: source site. */
		__( 'Image: %1$s. %2$s, via %3$s.', 'bestofislam' ),
		$image['artist'],
		$image['licence'],
		boi_image_source_name( isset( $image['source'] ) ? $image['source'] : '' )
	);
}

/**
 * Registers the credit blocks.
 *
 * @return void
 */
function boi_register_image_blocks() {
	register_block_type( 'bestofislam/image-credit', array( 'render_callback' => 'boi_render_image_credit' ) );
	register_block_type( 'bestofislam/image-credits', array( 'render_callback' => 'boi_render_image_credits' ) );
}
add_action( 'init', 'boi_register_image_blocks' );

/**
 * An image's source when it is a web address, else ''. The theme's own
 * artwork records the script that drew it ("bin/make-original-graphics.py"),
 * which is no page to link to and no site to credit.
 *
 * @param string $source Source recorded in the manifest.
 * @return string
 */
function boi_image_source_url( $source ) {
	$source = trim( (string) $source );
	return preg_match( '#^https?://#i', $source ) ? $source : '';
}

/**
 * A credit line: the lead ("Image: Artist. Licence,") and a link to the
 * source site, or the lead closed with a full stop when there is none.
 *
 * @param string $lead   Translated lead, ending in a comma.
 * @param string $source Source recorded in the manifest.
 * @return string Escaped HTML.
 */
function boi_credit_html( $lead, $source ) {
	$url = boi_image_source_url( $source );

	if ( '' === $url ) {
		// A pattern, not rtrim(): rtrim() strips single bytes, and would cut a
		// multibyte letter whose last byte matches one of the Arabic comma's.
		return esc_html( preg_replace( '/[\s,\x{060C}]+$/u', '', $lead ) . '.' );
	}

	return esc_html( $lead ) . ' <a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( boi_image_source_name( $url ) ) . '</a>';
}

/**
 * The name of the site an image comes from, for its credit line.
 *
 * @param string $url Source page of the image.
 * @return string
 */
function boi_image_source_name( $url ) {
	$host = (string) wp_parse_url( (string) $url, PHP_URL_HOST );

	if ( false !== strpos( $host, 'pexels.com' ) ) {
		return 'Pexels';
	}

	if ( false !== strpos( $host, 'pixabay.com' ) ) {
		return 'Pixabay';
	}

	return 'Wikimedia Commons';
}

/**
 * Credit beneath the featured image on a single post.
 *
 * @return string
 */
function boi_render_image_credit() {
	$id = get_post_thumbnail_id();

	if ( ! $id ) {
		$bundled = boi_bundled_image( get_post() );

		if ( ! $bundled ) {
			return '';
		}

		return '<p class="boi-credit">' . boi_credit_html( sprintf( /* translators: 1: artist, 2: licence. */ __( 'Image: %1$s. %2$s,', 'bestofislam' ), $bundled['artist'], $bundled['licence'] ), $bundled['source'] ) . '</p>';
	}

	$credit = get_post_meta( $id, '_boi_credit', true );

	if ( ! is_array( $credit ) || empty( $credit['source'] ) ) {
		$caption = wp_get_attachment_caption( $id );
		return '' !== (string) $caption ? '<p class="boi-credit">' . esc_html( $caption ) . '</p>' : '';
	}

	return '<p class="boi-credit">' . boi_credit_html( sprintf( /* translators: 1: artist, 2: licence. */ __( 'Image: %1$s. %2$s,', 'bestofislam' ), $credit['artist'], $credit['licence'] ), $credit['source'] ) . '</p>';
}

/**
 * " Source" link for the credits list, or nothing for the theme's own artwork.
 *
 * @param array $image Manifest entry.
 * @return string Escaped HTML.
 */
function boi_credit_source_link( $image ) {
	$url = boi_image_source_url( isset( $image['source'] ) ? $image['source'] : '' );
	return '' === $url ? '' : ' <a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html__( 'Source', 'bestofislam' ) . '</a>';
}

/**
 * Full credits list, for the Sources and standards page.
 *
 * @return string
 */
function boi_render_image_credits() {
	$items = '';

	foreach ( boi_image_manifest() as $slug => $image ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		$items .= sprintf(
			'<li><strong>%1$s</strong>: %2$s. %3$s.%4$s</li>',
			esc_html( $post ? get_the_title( $post ) : $slug ),
			esc_html( $image['artist'] ),
			esc_html( $image['licence'] ),
			boi_credit_source_link( $image )
		);
	}

	foreach ( boi_images_data() as $slug => $entry ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		foreach ( (array) $entry['inline'] as $image ) {
			$items .= sprintf(
				'<li><strong>%1$s</strong>: %2$s. %3$s.%4$s</li>',
				esc_html( $post ? get_the_title( $post ) : $slug ),
				esc_html( $image['artist'] ),
				esc_html( $image['licence'] ),
				boi_credit_source_link( $image )
			);
		}
	}

	if ( function_exists( 'boi_glossary_images' ) ) {
		$all   = boi_glossary_images();
		$group = array();

		if ( ! empty( $all['hero'] ) ) {
			$group[ __( 'Glossary', 'bestofislam' ) ] = array( $all['hero'] );
		}

		foreach ( (array) ( isset( $all['kinds'] ) ? $all['kinds'] : array() ) as $kind => $pool ) {
			$group[ sprintf( /* translators: %s: kind of entry. */ __( 'Glossary, %s entries', 'bestofislam' ), $kind ) ] = $pool;
		}

		foreach ( $group as $label => $images ) {
			foreach ( $images as $image ) {
				$items .= sprintf(
					'<li><strong>%1$s</strong>: %2$s. %3$s.%4$s</li>',
					esc_html( $label ),
					esc_html( $image['artist'] ),
					esc_html( $image['licence'] ),
					boi_credit_source_link( $image )
				);
			}
		}
	}

	return '<ul class="boi-credits">' . $items . '</ul>';
}

/**
 * The image bundled with the theme for a seeded article, whether or not it
 * has been copied into the media library yet.
 *
 * Every seeded article ships with its image in assets/images/featured/. The
 * media library copy is made in the background and can lag, or fail on hosts
 * that restrict uploads. Wherever an attached image is missing, this bundled
 * file stands in, so no seeded article is ever shown without its picture.
 *
 * @param int|WP_Post $post Post or identifier.
 * @return array|null Manifest entry plus url, width and height, or null.
 */
function boi_bundled_image( $post ) {
	static $cache = array();

	$post = get_post( $post );

	if ( ! $post ) {
		return null;
	}

	if ( array_key_exists( $post->ID, $cache ) ) {
		return $cache[ $post->ID ];
	}

	$manifest = boi_image_manifest();
	$entry    = isset( $manifest[ $post->post_name ] ) ? $manifest[ $post->post_name ] : null;
	$file     = $entry ? BOI_DIR . '/assets/images/featured/' . $entry['file'] : '';

	if ( ! $entry || ! file_exists( $file ) ) {
		$cache[ $post->ID ] = null;
		return null;
	}

	$size = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $file ) : getimagesize( $file );

	$entry['url']    = BOI_URI . '/assets/images/featured/' . $entry['file'];
	$entry['width']  = $size ? (int) $size[0] : 0;
	$entry['height'] = $size ? (int) $size[1] : 0;

	$cache[ $post->ID ] = $entry;

	return $entry;
}

/**
 * The bundled image as an img element, carrying the class and inline style
 * the requesting block passed, so it sits in the block's frame exactly as an
 * attached image would.
 *
 * @param array $image Entry from boi_bundled_image().
 * @param array $attr  Attributes requested by the caller.
 * @return string
 */
function boi_bundled_image_html( $image, $attr = array() ) {
	$attr  = is_array( $attr ) ? $attr : wp_parse_args( $attr );
	$class = 'wp-post-image boi-bundled-image' . ( ! empty( $attr['class'] ) ? ' ' . $attr['class'] : '' );

	return sprintf(
		'<img src="%1$s" alt="%2$s" class="%3$s"%4$s%5$s loading="lazy" decoding="async"%6$s />',
		esc_url( $image['url'] ),
		esc_attr( $image['alt'] ),
		esc_attr( $class ),
		$image['width'] ? ' width="' . (int) $image['width'] . '"' : '',
		$image['height'] ? ' height="' . (int) $image['height'] . '"' : '',
		! empty( $attr['style'] ) ? ' style="' . esc_attr( $attr['style'] ) . '"' : ''
	);
}

/**
 * Counts seeded articles whose image is attached in the media library, for
 * the Content tab.
 *
 * @return array Array( attached, total ).
 */
function boi_image_status() {
	$total    = 0;
	$attached = 0;

	foreach ( array_keys( boi_image_manifest() ) as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		if ( ! $post ) {
			continue;
		}

		++$total;

		if ( has_post_thumbnail( $post ) ) {
			++$attached;
		}
	}

	return array( $attached, $total );
}
