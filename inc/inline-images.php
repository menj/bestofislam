<?php
/**
 * Images from Pexels and Pixabay.
 *
 * Every article carries one featured image and up to three more in its text. The
 * credits for all of them live in inc/images-data.json, keyed by article slug:
 * a "featured" entry for articles whose featured image comes from here, and an
 * "inline" list of two. Articles place the inline ones with the
 * bestofislam/figure block, so a file can be replaced without editing posts.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The credits data.
 *
 * @return array
 */
function boi_images_data() {
	static $data = null;

	if ( null === $data ) {
		$raw  = file_exists( BOI_DIR . '/inc/images-data.json' ) ? file_get_contents( BOI_DIR . '/inc/images-data.json' ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$data = json_decode( (string) $raw, true );
		$data = is_array( $data ) ? $data : array();
	}

	return $data;
}

/**
 * Featured-image entries from the credits data, in manifest form.
 *
 * @return array
 */
function boi_images_data_featured() {
	$out = array();

	foreach ( boi_images_data() as $slug => $entry ) {
		if ( ! empty( $entry['featured'] ) ) {
			$out[ $slug ] = $entry['featured'];
		}
	}

	return $out;
}

/**
 * Renders one inline image of the current article, with its credit.
 *
 * @param array $attributes Block attributes: n, the 1-based position.
 * @return string
 */
function boi_render_figure( $attributes ) {
	$post = get_post();
	$n    = isset( $attributes['n'] ) ? max( 1, (int) $attributes['n'] ) : 1;
	$data = $post ? boi_images_data() : array();

	if ( ! $post || empty( $data[ $post->post_name ]['inline'][ $n - 1 ] ) ) {
		return '';
	}

	$image = $data[ $post->post_name ]['inline'][ $n - 1 ];
	$file  = BOI_DIR . '/assets/images/inline/' . $image['file'];

	if ( ! file_exists( $file ) ) {
		return '';
	}

	$size = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $file ) : getimagesize( $file );

	// A visible caption describes the picture for readers and search engines;
	// the credit line follows it. Images without a caption keep the credit only.
	$caption = ! empty( $image['caption'] ) ? '<span class="boi-caption">' . esc_html( $image['caption'] ) . '</span> ' : '';

	return sprintf(
		'<figure class="boi-figure"><img src="%1$s" alt="%2$s" width="%3$d" height="%4$d" loading="lazy" decoding="async" /><figcaption class="boi-credit">%6$s%5$s</figcaption></figure>',
		esc_url( BOI_URI . '/assets/images/inline/' . $image['file'] ),
		esc_attr( $image['alt'] ),
		$size ? (int) $size[0] : 1200,
		$size ? (int) $size[1] : 800,
		boi_credit_html( sprintf( /* translators: 1: photographer, 2: licence. */ __( 'Image: %1$s. %2$s,', 'bestofislam' ), $image['artist'], $image['licence'] ), $image['source'] ),
		$caption
	);
}

/**
 * Registers the figure block.
 *
 * @return void
 */
function boi_register_figure_block() {
	register_block_type(
		'bestofislam/figure',
		array(
			'render_callback' => 'boi_render_figure',
			'attributes'      => array(
				'n' => array(
					'type'    => 'number',
					'default' => 1,
				),
			),
		)
	);
}
add_action( 'init', 'boi_register_figure_block' );
