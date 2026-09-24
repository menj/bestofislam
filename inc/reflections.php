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

/**
 * Whether any published reflection exists: a post without the listicle flag.
 * Cached until the next save.
 *
 * @return bool
 */
function boi_has_reflections() {
	$cached = get_transient( 'boi_has_reflections' );

	if ( false !== $cached ) {
		return 'yes' === $cached;
	}

	$found = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => BOI_LISTICLE_META,
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	set_transient( 'boi_has_reflections', empty( $found ) ? 'no' : 'yes', 12 * HOUR_IN_SECONDS );

	return ! empty( $found );
}

/**
 * Clears the reflections cache whenever a post changes.
 *
 * @return void
 */
function boi_flush_reflections_flag() {
	delete_transient( 'boi_has_reflections' );
}
add_action( 'save_post_post', 'boi_flush_reflections_flag' );
add_action( 'deleted_post', 'boi_flush_reflections_flag' );

/**
 * Hides navigation links to the Reflections index while it would be empty,
 * so the first item a reader meets never leads to a blank page. The link
 * returns by itself with the first published reflection.
 *
 * @param string $content Rendered block.
 * @param array  $block   Parsed block.
 * @return string
 */
function boi_hide_empty_reflections_link( $content, $block ) {
	if ( 'core/navigation-link' !== $block['blockName'] || boi_has_reflections() ) {
		return $content;
	}

	$page_id = (int) get_option( 'page_for_posts' );
	$url     = isset( $block['attrs']['url'] ) ? $block['attrs']['url'] : '';
	$id      = isset( $block['attrs']['id'] ) ? (int) $block['attrs']['id'] : 0;

	$points_at_index = ( $page_id && $id === $page_id )
		|| ( $page_id && untrailingslashit( $url ) === untrailingslashit( get_permalink( $page_id ) ) )
		|| '/reflections' === untrailingslashit( wp_parse_url( $url, PHP_URL_PATH ) ?? '' );

	return $points_at_index ? '' : $content;
}
add_filter( 'render_block', 'boi_hide_empty_reflections_link', 10, 2 );
