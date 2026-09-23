<?php
/**
 * Listicle topic taxonomy.
 *
 * Registered here rather than in a plugin at the site owner's direction. The
 * file is self-contained so that it may be lifted into a plugin later without
 * modification.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the listicle-topic taxonomy.
 *
 * @return void
 */
function boi_register_taxonomy() {
	$labels = array(
		'name'              => _x( 'Listicle Topics', 'taxonomy general name', 'bestofislam' ),
		'singular_name'     => _x( 'Listicle Topic', 'taxonomy singular name', 'bestofislam' ),
		'search_items'      => __( 'Search Topics', 'bestofislam' ),
		'all_items'         => __( 'All Topics', 'bestofislam' ),
		'parent_item'       => __( 'Parent Topic', 'bestofislam' ),
		'parent_item_colon' => __( 'Parent Topic:', 'bestofislam' ),
		'edit_item'         => __( 'Edit Topic', 'bestofislam' ),
		'update_item'       => __( 'Update Topic', 'bestofislam' ),
		'add_new_item'      => __( 'Add New Topic', 'bestofislam' ),
		'new_item_name'     => __( 'New Topic Name', 'bestofislam' ),
		'menu_name'         => __( 'Listicle Topics', 'bestofislam' ),
	);

	register_taxonomy(
		'listicle-topic',
		array( 'post' ),
		array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'         => 'topic',
				'with_front'   => false,
				'hierarchical' => true,
			),
		)
	);
}
add_action( 'init', 'boi_register_taxonomy' );

/**
 * Flushes rewrite rules once after the taxonomy is first registered.
 *
 * @return void
 */
function boi_flush_rewrites() {
	boi_register_taxonomy();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'boi_flush_rewrites' );
