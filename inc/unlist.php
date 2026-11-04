<?php
/**
 * Unlisted posts and pages.
 *
 * An unlisted item stays published and opens normally from its own address,
 * but appears in no listing: not on the front page, in any theme module, in
 * archives, search, feeds, the XML sitemap, previous and next links, page
 * lists or the question list. It is also marked noindex.
 *
 * Adapted from the Unlist Posts & Pages plugin by Nikhil Chavan (GPLv2 or
 * later). The plugin filters posts_where, which get_posts() skips; the theme
 * builds most of its modules with get_posts(), so this version filters at
 * pre_get_posts, which every query passes through, and the theme's direct
 * lookups check boi_is_unlisted().
 *
 * Editors still see unlisted items throughout the admin and in the block
 * editor's own requests, so nothing becomes hard to find for the people who
 * manage it.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

const BOI_UNLISTED_OPTION = 'boi_unlisted';

/**
 * Whether the theme's unlisting is active. It stands aside while the
 * original plugin is active, so a post is never filtered twice.
 *
 * @return bool
 */
function boi_unlist_enabled() {
	// unlist_posts_enabled is the plugin's own switch; honouring it keeps any
	// code written against the plugin working.
	return ! class_exists( 'Unlist_Posts' ) && (bool) apply_filters( 'boi_unlist_enabled', (bool) apply_filters( 'unlist_posts_enabled', true ) );
}

/**
 * Post types that can be unlisted: every public type, as in the plugin.
 *
 * @return string[]
 */
function boi_unlist_post_types() {
	return array_values( get_post_types( array( 'public' => true ), 'names' ) );
}

/**
 * Identifiers of unlisted items. On first use, imports the list saved by the
 * Unlist Posts & Pages plugin, so switching from the plugin loses nothing.
 *
 * @return int[]
 */
function boi_unlisted_ids() {
	$ids = get_option( BOI_UNLISTED_OPTION, null );

	if ( null === $ids ) {
		$imported = get_option( 'unlist_posts', array() );
		$ids      = is_array( $imported ) ? $imported : array();
		update_option( BOI_UNLISTED_OPTION, array_values( array_unique( array_map( 'intval', $ids ) ) ), true );
	}

	return array_values( array_filter( array_map( 'intval', (array) $ids ) ) );
}

/**
 * Whether an item is unlisted.
 *
 * @param int|WP_Post $post Post or identifier.
 * @return bool
 */
function boi_is_unlisted( $post ) {
	if ( ! boi_unlist_enabled() ) {
		return false;
	}

	$id = $post instanceof WP_Post ? $post->ID : (int) $post;

	return $id > 0 && in_array( $id, boi_unlisted_ids(), true );
}

/**
 * Whether the current request should show unlisted items: anything in the
 * admin, and the block editor's own REST and AJAX requests from someone who
 * can edit posts.
 *
 * @return bool
 */
function boi_unlist_bypass() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return true;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	// AJAX from an admin screen, judged by a same-host referer under wp-admin.
	if ( wp_doing_ajax() ) {
		$referer = wp_parse_url( (string) wp_get_referer() );
		$admin   = wp_parse_url( admin_url() );

		return ! empty( $referer['host'] ) && ! empty( $admin['host'] )
			&& strtolower( $referer['host'] ) === strtolower( $admin['host'] )
			&& 0 === strpos( trailingslashit( isset( $referer['path'] ) ? $referer['path'] : '' ), trailingslashit( isset( $admin['path'] ) ? $admin['path'] : '' ) );
	}

	return false;
}

/**
 * Keeps unlisted items out of every listing query. The query for the item
 * itself, opened from its own address, is left alone.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function boi_unlist_filter_query( $query ) {
	if ( ! boi_unlist_enabled() || boi_unlist_bypass() || $query->is_singular() ) {
		return;
	}

	$ids = boi_unlisted_ids();

	if ( empty( $ids ) ) {
		return;
	}

	// A query naming its posts explicitly asked for them; respect it.
	if ( $query->get( 'post__in' ) ) {
		$query->set( 'post__in', array_values( array_diff( array_map( 'intval', (array) $query->get( 'post__in' ) ), $ids ) ) ?: array( 0 ) );
		return;
	}

	$query->set( 'post__not_in', array_values( array_unique( array_merge( array_map( 'intval', (array) $query->get( 'post__not_in' ) ), $ids ) ) ) );
}
add_action( 'pre_get_posts', 'boi_unlist_filter_query', 20 );

/**
 * Keeps unlisted items out of previous and next links.
 *
 * @param string $where WHERE clause of the adjacent-post query.
 * @return string
 */
function boi_unlist_adjacent( $where ) {
	$ids = boi_unlist_enabled() ? boi_unlisted_ids() : array();

	if ( empty( $ids ) ) {
		return $where;
	}

	return $where . ' AND p.ID NOT IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')';
}
add_filter( 'get_next_post_where', 'boi_unlist_adjacent' );
add_filter( 'get_previous_post_where', 'boi_unlist_adjacent' );

/**
 * Keeps unlisted pages out of page lists.
 *
 * @param int[] $exclude Page identifiers to exclude.
 * @return int[]
 */
function boi_unlist_page_lists( $exclude ) {
	return boi_unlist_enabled() ? array_merge( (array) $exclude, boi_unlisted_ids() ) : $exclude;
}
add_filter( 'wp_list_pages_excludes', 'boi_unlist_page_lists' );

/**
 * Marks an unlisted item noindex when opened directly.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function boi_unlist_robots( $robots ) {
	if ( is_singular() && boi_is_unlisted( get_queried_object_id() ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'], $robots['max-image-preview'] );

		// Yoast SEO prints its own robots tag; empty it so the page carries one.
		add_filter( 'wpseo_robots_array', '__return_empty_array' );
	}

	return $robots;
}
add_filter( 'wp_robots', 'boi_unlist_robots', 20 );

/**
 * Adds the Unlisted box to the editor sidebar for posts and pages.
 *
 * @return void
 */
function boi_unlist_meta_box() {
	if ( ! boi_unlist_enabled() ) {
		return;
	}

	foreach ( boi_unlist_post_types() as $type ) {
		add_meta_box( 'boi-unlist', __( 'Visibility in listings', 'bestofislam' ), 'boi_render_unlist_meta_box', $type, 'side', 'default' );
	}
}
add_action( 'add_meta_boxes', 'boi_unlist_meta_box' );

/**
 * Renders the Unlisted box.
 *
 * @param WP_Post $post Post being edited.
 * @return void
 */
function boi_render_unlist_meta_box( $post ) {
	wp_nonce_field( 'boi_unlist_' . $post->ID, 'boi_unlist_nonce' );
	printf(
		'<p><label><input type="checkbox" name="boi_unlisted" value="1"%1$s /> %2$s</label></p><p class="description">%3$s</p>',
		checked( boi_is_unlisted( $post ), true, false ),
		esc_html__( 'Unlist', 'bestofislam' ),
		esc_html__( 'Opens from its own link, but appears in no listing, search, feed or sitemap, and asks search engines not to index it.', 'bestofislam' )
	);
}

/**
 * Saves the Unlisted setting.
 *
 * @param int $post_id Post identifier.
 * @return void
 */
function boi_unlist_save( $post_id ) {
	if ( ! boi_unlist_enabled() || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}

	// Bulk Edit: core checks the bulk-posts nonce first; check it again so a
	// stray request key in another save_post context changes nothing.
	if ( isset( $_REQUEST['bulk_edit'], $_REQUEST['boi_unlist_bulk'] ) ) {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-posts' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$choice = sanitize_key( wp_unslash( $_REQUEST['boi_unlist_bulk'] ) );

		if ( 'unlist' === $choice || 'list' === $choice ) {
			boi_set_unlisted( $post_id, 'unlist' === $choice );
		}

		return;
	}

	// Quick Edit.
	if ( isset( $_POST['boi_unlist_qe_nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['boi_unlist_qe_nonce'] ) ), 'boi_unlist_quick_edit' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		boi_set_unlisted( $post_id, ! empty( $_POST['boi_unlisted'] ) );
		return;
	}

	if ( ! isset( $_POST['boi_unlist_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['boi_unlist_nonce'] ) ), 'boi_unlist_' . $post_id ) ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	boi_set_unlisted( $post_id, ! empty( $_POST['boi_unlisted'] ) );
}
add_action( 'save_post', 'boi_unlist_save' );

/**
 * Adds or removes an item from the unlisted set.
 *
 * @param int  $post_id  Post identifier.
 * @param bool $unlisted Whether to unlist it.
 * @return void
 */
function boi_set_unlisted( $post_id, $unlisted ) {
	boi_with_lock(
		'unlisted',
		function () use ( $post_id, $unlisted ) {
			// Read inside the lock, so two saves cannot overwrite each other.
			// The option autoloads, so it also sits in the combined "alloptions"
			// cache; clear both or the read below can return a stale list.
			wp_cache_delete( BOI_UNLISTED_OPTION, 'options' );
			wp_cache_delete( 'alloptions', 'options' );
			$ids = boi_unlisted_ids();
			$ids = $unlisted ? array_unique( array_merge( $ids, array( (int) $post_id ) ) ) : array_diff( $ids, array( (int) $post_id ) );

			update_option( BOI_UNLISTED_OPTION, array_values( array_map( 'intval', $ids ) ), true );
		}
	);

	if ( function_exists( 'boi_flush_site_counts' ) ) {
		boi_flush_site_counts();
	}
}

/**
 * Labels unlisted items in the admin lists of posts and pages.
 *
 * @param string[] $states Post states.
 * @param WP_Post  $post   Post.
 * @return string[]
 */
function boi_unlist_post_state( $states, $post ) {
	if ( boi_is_unlisted( $post ) ) {
		$states['boi-unlisted'] = __( 'Unlisted', 'bestofislam' );
	}

	return $states;
}
add_filter( 'display_post_states', 'boi_unlist_post_state', 10, 2 );

/**
 * Adds the Unlisted column to every public post type's list. The column is
 * also what makes WordPress print the Quick Edit and Bulk Edit fields below.
 *
 * @return void
 */
function boi_unlist_list_table_hooks() {
	if ( ! boi_unlist_enabled() ) {
		return;
	}

	foreach ( boi_unlist_post_types() as $type ) {
		add_filter( "manage_{$type}_posts_columns", 'boi_unlist_column' );
		add_action( "manage_{$type}_posts_custom_column", 'boi_unlist_column_cell', 10, 2 );
		add_filter( "views_edit-{$type}", 'boi_unlist_view' );
	}
}
add_action( 'admin_init', 'boi_unlist_list_table_hooks' );

/**
 * The column header.
 *
 * @param array $columns Columns.
 * @return array
 */
function boi_unlist_column( $columns ) {
	$columns['boi_unlisted'] = __( 'Unlisted', 'bestofislam' );
	return $columns;
}

/**
 * The column cell. Its data attribute lets Quick Edit tick the box.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post identifier.
 * @return void
 */
function boi_unlist_column_cell( $column, $post_id ) {
	if ( 'boi_unlisted' !== $column ) {
		return;
	}

	$unlisted = boi_is_unlisted( $post_id );

	printf(
		'<span class="boi-unlist-status" data-unlisted="%1$s">%2$s</span>',
		$unlisted ? '1' : '0',
		$unlisted ? esc_html__( 'Yes', 'bestofislam' ) : '&mdash;'
	);
}

/**
 * The Quick Edit checkbox.
 *
 * @param string $column    Column key.
 * @param string $post_type Post type.
 * @return void
 */
function boi_unlist_quick_edit( $column, $post_type ) {
	if ( 'boi_unlisted' !== $column || ! in_array( $post_type, boi_unlist_post_types(), true ) ) {
		return;
	}

	wp_nonce_field( 'boi_unlist_quick_edit', 'boi_unlist_qe_nonce', false );
	printf(
		'<fieldset class="inline-edit-col-right"><div class="inline-edit-col"><label class="alignleft"><input type="checkbox" name="boi_unlisted" value="1" /> <span class="checkbox-title">%s</span></label></div></fieldset>',
		esc_html__( 'Unlist', 'bestofislam' )
	);
}
add_action( 'quick_edit_custom_box', 'boi_unlist_quick_edit', 10, 2 );

/**
 * The Bulk Edit choice: no change, unlist or list.
 *
 * @param string $column    Column key.
 * @param string $post_type Post type.
 * @return void
 */
function boi_unlist_bulk_edit( $column, $post_type ) {
	if ( 'boi_unlisted' !== $column || ! in_array( $post_type, boi_unlist_post_types(), true ) ) {
		return;
	}

	printf(
		'<fieldset class="inline-edit-col-right"><div class="inline-edit-col"><label class="inline-edit-group"><span class="title">%1$s</span><select name="boi_unlist_bulk"><option value="">%2$s</option><option value="unlist">%3$s</option><option value="list">%4$s</option></select></label></div></fieldset>',
		esc_html__( 'Unlisted', 'bestofislam' ),
		esc_html__( 'No change', 'bestofislam' ),
		esc_html__( 'Unlist', 'bestofislam' ),
		esc_html__( 'List', 'bestofislam' )
	);
}
add_action( 'bulk_edit_custom_box', 'boi_unlist_bulk_edit', 10, 2 );

/**
 * Loads the script that ticks the Quick Edit box, on post lists only.
 *
 * @param string $hook Admin page.
 * @return void
 */
function boi_unlist_admin_assets( $hook ) {
	if ( 'edit.php' !== $hook || ! boi_unlist_enabled() ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->post_type, boi_unlist_post_types(), true ) ) {
		return;
	}

	wp_enqueue_script( 'boi-unlist-admin', BOI_URI . '/assets/js/unlist-admin.js', array( 'jquery', 'inline-edit-post' ), boi_asset_version( '/assets/js/unlist-admin.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'boi_unlist_admin_assets' );

/**
 * Whether the post list is showing the Unlisted view.
 *
 * @return bool
 */
function boi_unlist_view_active() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- list view choice only.
	return is_admin() && isset( $_GET['post_status'] ) && 'unlisted' === $_GET['post_status'];
}

/**
 * Adds "Unlisted (n)" to the views above a post list.
 *
 * @param array $views Views.
 * @return array
 */
function boi_unlist_view( $views ) {
	$ids    = boi_unlisted_ids();
	$screen = get_current_screen();

	if ( empty( $ids ) || ! $screen ) {
		return $views;
	}

	$count = count(
		get_posts(
			array(
				'post_type'      => $screen->post_type,
				'post__in'       => $ids,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		)
	);

	if ( $count > 0 ) {
		$views['boi-unlisted'] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>',
			esc_url( add_query_arg( 'post_status', 'unlisted', admin_url( 'edit.php?post_type=' . $screen->post_type ) ) ),
			boi_unlist_view_active() ? ' class="current" aria-current="page"' : '',
			esc_html__( 'Unlisted', 'bestofislam' ),
			$count
		);
	}

	return $views;
}

/**
 * Limits the post list to unlisted items in the Unlisted view.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function boi_unlist_view_query( $query ) {
	global $pagenow;

	if ( 'edit.php' !== $pagenow || ! $query->is_main_query() || ! boi_unlist_view_active() || ! boi_unlist_enabled() ) {
		return;
	}

	$ids = boi_unlisted_ids();
	$query->set( 'post__in', $ids ? $ids : array( 0 ) );
	$query->set( 'post_status', 'any' );
}
add_action( 'pre_get_posts', 'boi_unlist_view_query' );
