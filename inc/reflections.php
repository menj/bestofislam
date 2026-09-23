<?php
/**
 * Reflections and listicles.
 *
 * A post is a listicle when it contains the listicle block. That is recorded
 * as meta on save, so the editor makes no separate choice and the flag cannot
 * drift from the content. Reflections are everything else: essays and shorter
 * pieces that do not take the ranked format.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

const BOI_LISTICLE_META = '_boi_is_listicle';

/**
 * Records whether a post carries the listicle block.
 *
 * @param int     $post_id Post identifier.
 * @param WP_Post $post    Post object.
 * @return void
 */
function boi_flag_listicle( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( 'post' !== $post->post_type ) {
		return;
	}

	if ( has_block( 'bestofislam/listicle', $post ) ) {
		update_post_meta( $post_id, BOI_LISTICLE_META, '1' );
		return;
	}

	delete_post_meta( $post_id, BOI_LISTICLE_META );
}
add_action( 'save_post', 'boi_flag_listicle', 10, 2 );

/**
 * Keeps listicles out of the Reflections index.
 *
 * Section archives are unaffected, so a reader browsing Theology or History
 * still sees everything filed there.
 *
 * @param WP_Query $query The query.
 * @return void
 */
function boi_filter_reflections( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_home() ) {
		return;
	}

	$meta = (array) $query->get( 'meta_query' );

	$meta[] = array(
		'key'     => BOI_LISTICLE_META,
		'compare' => 'NOT EXISTS',
	);

	$query->set( 'meta_query', $meta );
}
add_action( 'pre_get_posts', 'boi_filter_reflections' );

/**
 * Applies the flag to posts that predate it.
 *
 * Runs once, after population, so seeded articles are classified without
 * requiring each to be opened and re-saved.
 *
 * @return void
 */
function boi_backfill_listicle_flags() {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
		)
	);

	foreach ( $posts as $post_id ) {
		$post = get_post( $post_id );

		if ( $post ) {
			boi_flag_listicle( $post_id, $post );
		}
	}
}
