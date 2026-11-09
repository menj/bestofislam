<?php
/**
 * Seeded articles that follow the bundle, safely.
 *
 * Population (setup.php) creates each article once and refreshes it only while the owner has not edited it.
 * This file records what the theme wrote, so that "not edited" is proven and never guessed, lists what it may
 * not touch, retires what the bundle dropped, and lets a replacement article supersede an old one. It never
 * touches an article the owner created.
 *
 * Fingerprints. When the theme writes an article it records the fingerprint of the stored text (title, excerpt
 * and body, the body in the form WordPress stores it) in post meta (_boi_sync_fp), the fingerprint of the
 * bundled entry it wrote from (_boi_sync_bfp) and the article's bundled slug (_boi_sync_key). An article counts
 * as edited when its stored text no longer matches that record, and also when no record exists. An article
 * written before this record existed is judged by the older option records (boi_article_hashes and
 * boi_article_text_hashes): both must still match. The older rule that took an article never modified since
 * it was created for untouched is gone, because it was a guess.
 *
 * What a sync does with an article the theme created:
 *
 *   current     the bundle has not changed since the last write, or equals the stored text: nothing to do
 *   update      untouched by the owner, bundle text changed: takes the new text
 *   newer       edited by the owner, bundle text changed: listed with a diff; the owner takes the bundled
 *               text or keeps their own
 *   trash       dropped from the bundle and untouched: moved to the Trash, never deleted
 *   removed     dropped from the bundle and edited: listed
 *
 * Supersedes. inc/data/supersedes.json (and the boi_supersedes filter) is { "post": { old slug: new slug } }.
 * While the new article is published and the old one is untouched, the old one answers with a 301 (at any
 * address), is hidden through the unlist module, carries noindex, and is moved to the Trash. An old article the
 * owner edited is listed instead. The map ships empty.
 *
 * Guards. Removals, the redirect and the hiding stop when a bundled file fails its checksum
 * (inc/data/seed-manifest.json, written by bin/seed-manifest.php), when the bundle lists more than 10 percent
 * fewer articles than at the last good sync, or when BOI_DISABLE_CONTENT_SYNC is true. The constant also
 * switches off updates.
 *
 * Delivery. The sync runs when the theme version, the manifest checksum, the supersedes map or the guard state
 * changes, so a corrected article reaches a site without a version bump.
 *
 * @package bestofislam
 * @since   1.114.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The kill switch.
 *
 * @return bool
 */
function boi_sync_disabled() {
	return defined( 'BOI_DISABLE_CONTENT_SYNC' ) && BOI_DISABLE_CONTENT_SYNC;
}

/**
 * Fingerprint of the texts the theme owns for one article, from their values.
 *
 * @param string $title   Title.
 * @param string $excerpt Excerpt.
 * @param string $content Body.
 * @return string
 */
function boi_sync_fp_values( $title, $excerpt, $content ) {
	return md5( (string) wp_json_encode( array( (string) $title, (string) $excerpt, boi_content_fingerprint( $content ) ) ) );
}

/**
 * What a sync does with one existing article. Pure.
 *
 * @param string $bundle_fp      Fingerprint of the bundled entry.
 * @param string $stored_fp      Fingerprint of the stored text now.
 * @param string $recorded_fp    Fingerprint recorded when the theme last wrote it, '' when none.
 * @param string $last_bundle_fp Fingerprint of the bundled entry at that write, '' when none.
 * @param string $ack            Bundle fingerprint the owner chose to keep their text against.
 * @return string current|update|newer|kept
 */
function boi_sync_decide( $bundle_fp, $stored_fp, $recorded_fp, $last_bundle_fp, $ack ) {
	if ( '' !== $last_bundle_fp && $last_bundle_fp === $bundle_fp ) {
		return 'current';
	}
	if ( $stored_fp === $bundle_fp ) {
		return 'current';
	}
	if ( '' !== $recorded_fp && $recorded_fp === $stored_fp ) {
		return 'update';
	}
	return $ack === $bundle_fp ? 'kept' : 'newer';
}

/**
 * What a sync does with an article the bundle no longer carries. Pure.
 *
 * @param string $stored_fp   Fingerprint of the stored text now.
 * @param string $recorded_fp Fingerprint recorded when the theme last wrote it, '' when none.
 * @param string $ack         The owner's earlier choice ('removed' or 'superseded' keeps it quiet).
 * @return string trash|removed|kept
 */
function boi_sync_decide_removed( $stored_fp, $recorded_fp, $ack ) {
	if ( '' !== $recorded_fp && $recorded_fp === $stored_fp ) {
		return 'trash';
	}
	return in_array( $ack, array( 'removed', 'superseded' ), true ) ? 'kept' : 'removed';
}

/**
 * Whether removals may run. Pure.
 *
 * @param bool $disabled    The kill switch.
 * @param bool $intact      Every bundled file matches its checksum.
 * @param int  $count_now   Bundled articles now.
 * @param int  $count_prior Bundled articles at the last good sync, 0 when none.
 * @return string '' when allowed, otherwise the reason.
 */
function boi_sync_guard( $disabled, $intact, $count_now, $count_prior ) {
	if ( $disabled ) {
		return 'the kill switch BOI_DISABLE_CONTENT_SYNC is on';
	}
	if ( ! $intact ) {
		return 'a bundled file is missing or fails its checksum';
	}
	if ( $count_prior > 0 && $count_now * 10 < $count_prior * 9 ) {
		return 'the bundle lists more than 10 percent fewer articles than at the last good sync';
	}
	return '';
}

/**
 * The manifest of bundled files, read cheaply.
 *
 * @return array{files:array<string,string>,checksum:string}
 */
function boi_sync_manifest() {
	$m = boi_read_data( 'seed-manifest' );
	return array(
		'files'    => isset( $m['files'] ) && is_array( $m['files'] ) ? $m['files'] : array(),
		'checksum' => isset( $m['checksum'] ) ? (string) $m['checksum'] : '',
	);
}

/**
 * Whether every bundled file matches the manifest. The answer is kept for an hour per manifest, so the front
 * end does not hash three megabytes.
 *
 * @return bool
 */
function boi_sync_checksums_ok() {
	$m = boi_sync_manifest();
	if ( ! $m['files'] || '' === $m['checksum'] || md5( (string) wp_json_encode( $m['files'] ) ) !== $m['checksum'] ) {
		return false;
	}
	$key    = 'boi_sync_intact_' . substr( $m['checksum'], 0, 12 );
	$cached = get_transient( $key );
	if ( false !== $cached ) {
		return '1' === $cached;
	}
	$ok = true;
	foreach ( $m['files'] as $rel => $sum ) {
		$file = BOI_DIR . '/' . ltrim( (string) $rel, '/' );
		if ( 0 !== strpos( (string) $rel, 'content/' ) && 0 !== strpos( (string) $rel, 'inc/data/' ) || ! is_readable( $file ) || md5_file( $file ) !== $sum ) {
			$ok = false;
			break;
		}
	}
	set_transient( $key, $ok ? '1' : '0', HOUR_IN_SECONDS );
	return $ok;
}

/**
 * Whether removals may run now.
 *
 * @return string '' when allowed, otherwise the reason.
 */
function boi_sync_removal_block() {
	return boi_sync_guard( boi_sync_disabled(), boi_sync_checksums_ok() && boi_seed_ready(), count( boi_seed_listicles() ), (int) get_option( 'boi_sync_count', 0 ) );
}

/**
 * The supersedes map, cleaned: old slug => new slug.
 *
 * @return array<string,string>
 */
function boi_supersedes() {
	$raw = boi_read_data( 'supersedes' );
	$raw = apply_filters( 'boi_supersedes', $raw );
	$map = array();
	foreach ( isset( $raw['post'] ) && is_array( $raw['post'] ) ? $raw['post'] : array() as $old => $new ) {
		$old = sanitize_title( (string) $old );
		$new = sanitize_title( (string) $new );
		if ( '' !== $old && '' !== $new && $old !== $new ) {
			$map[ $old ] = $new;
		}
	}
	return $map;
}

/**
 * The bundle, keyed by slug: slug => array( slug, title, excerpt, content, fp ).
 *
 * @return array<string,array>
 */
function boi_sync_bundle() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();
	foreach ( boi_seed_listicles() as $listicle ) {
		$content = boi_load_article( $listicle['slug'] );
		if ( '' === $content ) {
			continue;
		}
		$cache[ $listicle['slug'] ] = array(
			'slug'    => $listicle['slug'],
			'title'   => (string) $listicle['title'],
			'excerpt' => (string) $listicle['excerpt'],
			'content' => $content,
			'fp'      => boi_sync_fp_values( $listicle['title'], $listicle['excerpt'], $content ),
		);
	}
	return $cache;
}

/**
 * The post the theme created for a slug, or null. Articles the owner created are never returned.
 *
 * @param string $slug Slug.
 * @return WP_Post|null
 */
function boi_sync_post( $slug ) {
	$post = get_page_by_path( $slug, OBJECT, 'post' );
	return ( $post && get_post_meta( $post->ID, '_boi_seeded', true ) ) ? $post : null;
}

/**
 * The state of a post against its own record.
 *
 * @param WP_Post $post Post.
 * @param string  $slug Bundled slug.
 * @return array{stored:string,recorded:string,bfp:string,ack:string}
 */
function boi_sync_state( $post, $slug ) {
	$stored   = boi_sync_fp_values( $post->post_title, $post->post_excerpt, $post->post_content );
	$recorded = (string) get_post_meta( $post->ID, '_boi_sync_fp', true );
	if ( '' === $recorded ) {
		// Only the older option records exist: the body and the text must both still match them.
		$hashes = (array) get_option( 'boi_article_hashes', array() );
		$texts  = (array) get_option( 'boi_article_text_hashes', array() );
		if ( isset( $hashes[ $slug ], $texts[ $slug ] )
			&& $hashes[ $slug ] === boi_content_fingerprint( $post->post_content )
			&& $texts[ $slug ] === md5( $post->post_title . "\n" . $post->post_excerpt ) ) {
			$recorded = $stored;
		}
	}
	return array(
		'stored'   => $stored,
		'recorded' => $recorded,
		'bfp'      => (string) get_post_meta( $post->ID, '_boi_sync_bfp', true ),
		'ack'      => (string) get_post_meta( $post->ID, '_boi_sync_ack', true ),
	);
}

/**
 * Record the stored text of an article the theme wrote, the bundled entry it wrote from and its slug.
 *
 * @param int         $post_id Post ID.
 * @param array       $item    Bundle item.
 * @return void
 */
function boi_sync_record( $post_id, $item ) {
	$post = get_post( $post_id );
	if ( $post ) {
		update_post_meta( $post_id, '_boi_sync_fp', boi_sync_fp_values( $post->post_title, $post->post_excerpt, $post->post_content ) );
		update_post_meta( $post_id, '_boi_sync_bfp', $item['fp'] );
		update_post_meta( $post_id, '_boi_sync_key', $item['slug'] );
		delete_post_meta( $post_id, '_boi_sync_ack' );
	}
}

/**
 * Write a bundled article into its post and record the fingerprints.
 *
 * @param WP_Post $post Post.
 * @param array   $item Bundle item.
 * @return bool
 */
function boi_sync_write( $post, $item ) {
	$done = boi_update_post(
		array(
			'ID'           => $post->ID,
			'post_title'   => $item['title'],
			'post_excerpt' => $item['excerpt'],
			'post_content' => $item['content'],
		)
	);
	if ( ! $done || is_wp_error( $done ) ) {
		return false;
	}
	boi_sync_record( $post->ID, $item );
	return true;
}

/**
 * Superseded pairs that qualify now: old post ID => new post ID. The new one is published, the old one was made
 * by the theme and has not been edited.
 *
 * @return array<int,int>
 */
function boi_superseded_pairs() {
	static $cache = null;
	static $busy  = false;
	if ( null !== $cache ) {
		return $cache;
	}
	if ( $busy ) {
		return array(); // The lookups below fire pre_get_posts, which asks the unlist module, which asks here.
	}
	$map = boi_supersedes();
	if ( ! $map || boi_sync_disabled() || '' !== boi_sync_removal_block() ) {
		$cache = array();
		return $cache;
	}
	$busy  = true;
	$cache = array();
	foreach ( $map as $old => $new ) {
		$old_post = boi_sync_post( $old );
		$new_post = get_page_by_path( $new, OBJECT, 'post' );
		if ( ! $old_post || ! $new_post || 'publish' !== $old_post->post_status || 'publish' !== $new_post->post_status || $old_post->ID === $new_post->ID ) {
			continue;
		}
		$s = boi_sync_state( $old_post, $old );
		if ( '' !== $s['recorded'] && $s['recorded'] === $s['stored'] ) {
			$cache[ (int) $old_post->ID ] = (int) $new_post->ID;
		}
	}
	$busy = false;
	return $cache;
}

/**
 * IDs of the superseded posts, for the unlist module.
 *
 * @return int[]
 */
function boi_superseded_ids() {
	return array_keys( boi_superseded_pairs() );
}

/**
 * The plan: one row per article that needs attention or can be told about.
 *
 * @return array<int,array{key:string,slug:string,title:string,post_id:int,action:string}>
 */
function boi_sync_plan() {
	$bundle = boi_sync_bundle();
	$rows   = array();
	foreach ( $bundle as $slug => $item ) {
		$post = boi_sync_post( $slug );
		if ( ! $post || 'trash' === $post->post_status ) {
			continue; // Not created, or retired: population decides, never this plan.
		}
		$s      = boi_sync_state( $post, $slug );
		$rows[] = array( 'key' => $slug, 'slug' => $slug, 'title' => $post->post_title, 'post_id' => (int) $post->ID, 'action' => boi_sync_decide( $item['fp'], $s['stored'], $s['recorded'], $s['bfp'], $s['ack'] ) );
	}
	$renamed = array_keys( boi_seed_renames() );
	$keyed   = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_boi_sync_key', 'meta_compare' => 'EXISTS' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key -- an admin screen.
	foreach ( $keyed as $id ) {
		$slug = (string) get_post_meta( $id, '_boi_sync_key', true );
		if ( isset( $bundle[ $slug ] ) || in_array( $slug, $renamed, true ) ) {
			continue;
		}
		$post = get_post( $id );
		if ( ! $post || ! get_post_meta( $id, '_boi_seeded', true ) ) {
			continue;
		}
		$s      = boi_sync_state( $post, $slug );
		$rows[] = array( 'key' => $slug, 'slug' => $slug, 'title' => $post->post_title, 'post_id' => (int) $id, 'action' => boi_sync_decide_removed( $s['stored'], $s['recorded'], $s['ack'] ) );
	}
	$pairs = boi_superseded_pairs();
	foreach ( boi_supersedes() as $old => $new ) {
		$old_post = boi_sync_post( $old );
		$new_post = get_page_by_path( $new, OBJECT, 'post' );
		if ( ! $old_post || ! $new_post || 'publish' !== $old_post->post_status || 'publish' !== $new_post->post_status ) {
			continue;
		}
		$ack    = (string) get_post_meta( $old_post->ID, '_boi_sync_ack', true );
		$rows[] = array( 'key' => $old, 'slug' => $old, 'title' => $old_post->post_title, 'post_id' => (int) $old_post->ID, 'action' => isset( $pairs[ $old_post->ID ] ) ? 'supersede' : ( in_array( $ack, array( 'superseded', 'removed' ), true ) ? 'kept' : 'superseded' ) );
	}
	return $rows;
}

/**
 * Append to the change log (the last 100 lines).
 *
 * @param string $key    Article slug.
 * @param string $action What happened.
 * @return void
 */
function boi_sync_log( $key, $action ) {
	$log   = (array) get_option( 'boi_sync_log', array() );
	$log[] = array( 't' => time(), 'key' => $key, 'action' => $action );
	update_option( 'boi_sync_log', array_slice( $log, -100 ), false );
}

/**
 * Run the review steps: take the bundled text for untouched articles, retire what the bundle dropped or superseded
 * when no guard stops it, and list everything that waits for the owner. Safe to call on its own.
 *
 * @return array{updated:int,trashed:int,waiting:int,blocked:string}
 */
function boi_sync_apply() {
	$out = array( 'updated' => 0, 'trashed' => 0, 'waiting' => 0, 'blocked' => '' );
	if ( boi_sync_disabled() ) {
		$out['blocked'] = boi_sync_removal_block();
		return $out;
	}
	$bundle         = boi_sync_bundle();
	$blocked        = boi_sync_removal_block();
	$out['blocked'] = $blocked;
	foreach ( boi_sync_plan() as $row ) {
		$post = get_post( $row['post_id'] );
		if ( ! $post ) {
			continue;
		}
		switch ( $row['action'] ) {
			case 'current':
				if ( isset( $bundle[ $row['key'] ] ) && '' === (string) get_post_meta( $post->ID, '_boi_sync_fp', true ) && boi_sync_state( $post, $row['key'] )['stored'] === $bundle[ $row['key'] ]['fp'] ) {
					boi_sync_record( $post->ID, $bundle[ $row['key'] ] ); // Ours and equal to the bundle: record it.
				}
				break;
			case 'update':
				if ( isset( $bundle[ $row['key'] ] ) && boi_sync_write( $post, $bundle[ $row['key'] ] ) ) {
					++$out['updated'];
					boi_sync_log( $row['key'], 'updated to the bundled text' );
				}
				break;
			case 'trash':
			case 'supersede':
				if ( '' === $blocked && 'trash' !== $post->post_status && wp_trash_post( $post->ID ) ) {
					++$out['trashed'];
					boi_sync_log( $row['key'], 'supersede' === $row['action'] ? 'moved to the Trash, replaced' : 'moved to the Trash, dropped from the bundle' );
				}
				break;
		}
	}
	$pending = array();
	foreach ( boi_sync_plan() as $row ) {
		if ( in_array( $row['action'], array( 'newer', 'removed', 'superseded', 'trash', 'supersede' ), true ) ) {
			if ( in_array( $row['action'], array( 'trash', 'supersede' ), true ) && '' === $blocked ) {
				continue;
			}
			$pending[ $row['key'] ] = $row;
		}
	}
	update_option( 'boi_sync_pending', $pending, false );
	$out['waiting'] = count( $pending );
	if ( '' === $blocked ) {
		update_option( 'boi_sync_count', count( boi_seed_listicles() ), false );
	}
	return $out;
}

/**
 * Run population and the review when the theme version, the manifest, the supersedes map or the guard state
 * changed, and only for someone who may manage options. Behind the same lock as the version migration.
 *
 * @return void
 */
function boi_sync_maybe_run() {
	if ( boi_sync_disabled() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ! boi_seed_ready() ) {
		return;
	}
	$fp = md5( BOI_VERSION . boi_sync_manifest()['checksum'] . wp_json_encode( boi_supersedes() ) . boi_sync_removal_block() );
	if ( get_option( 'boi_sync_fp' ) === $fp ) {
		return;
	}
	boi_with_lock(
		'sync',
		static function () use ( $fp ) {
			if ( get_option( 'boi_sync_fp' ) === $fp ) {
				return;
			}
			if ( get_option( 'boi_content_populated' ) && function_exists( 'boi_populate_content' ) ) {
				boi_populate_content();
			}
			boi_sync_apply();
			update_option( 'boi_sync_fp', $fp, false );
		},
		true
	);
}
add_action( 'admin_init', 'boi_sync_maybe_run', 30 );

/* -------------------------------------------------------------------------
 * The owner's choices, the notice and the card.
 * ---------------------------------------------------------------------- */

/** Where the review lives: Theme Options, Content. */
function boi_sync_screen_url() {
	return add_query_arg( array( 'page' => 'boi-settings', 'tab' => 'content' ), admin_url( 'themes.php' ) ) . '#boi-content-sync';
}

/** Take the bundled text for one article, retire it, or keep the owner's own. All are logged. */
function boi_sync_handle_item() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'bestofislam' ), 403 );
	}
	check_admin_referer( 'boi_sync_item' );
	if ( boi_sync_disabled() ) {
		wp_safe_redirect( boi_sync_screen_url() ); // The switch comes before any choice: nothing is written or trashed.
		exit;
	}
	$slug   = isset( $_POST['item'] ) ? sanitize_title( wp_unslash( $_POST['item'] ) ) : '';
	$choice = isset( $_POST['choice'] ) && 'take' === $_POST['choice'] ? 'take' : 'keep';
	$bundle = boi_sync_bundle();
	$post   = '' !== $slug ? boi_sync_post( $slug ) : null;
	if ( $post ) {
		$item = $bundle[ $slug ] ?? null;
		if ( 'take' === $choice && $item ) {
			boi_sync_write( $post, $item );
			boi_sync_log( $slug, 'took the bundled text' );
		} elseif ( 'take' === $choice && '' === boi_sync_removal_block() ) {
			wp_trash_post( $post->ID ); // Dropped or superseded: the bundled choice is the Trash, never a deletion.
			boi_sync_log( $slug, 'moved to the Trash by choice' );
		} elseif ( 'take' === $choice ) {
			boi_sync_log( $slug, 'the Trash was refused while a removal guard holds' );
		} else {
			update_post_meta( $post->ID, '_boi_sync_ack', $item ? $item['fp'] : ( isset( boi_supersedes()[ $slug ] ) ? 'superseded' : 'removed' ) );
			boi_sync_log( $slug, 'kept the owner text' );
		}
		boi_sync_apply();
	}
	wp_safe_redirect( boi_sync_screen_url() );
	exit;
}
add_action( 'admin_post_boi_sync_item', 'boi_sync_handle_item' );

/** Run the review now, with the same rules as the automatic one. */
function boi_sync_handle_now() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'bestofislam' ), 403 );
	}
	check_admin_referer( 'boi_sync_now' );
	if ( boi_sync_disabled() ) {
		wp_safe_redirect( boi_sync_screen_url() );
		exit;
	}
	if ( get_option( 'boi_content_populated' ) && function_exists( 'boi_populate_content' ) ) {
		boi_populate_content();
	}
	boi_sync_apply();
	wp_safe_redirect( boi_sync_screen_url() );
	exit;
}
add_action( 'admin_post_boi_sync_now', 'boi_sync_handle_now' );

/** Accept the current bundle size as the new baseline for the 10 percent guard, after an intentional retirement. */
function boi_sync_handle_accept_count() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'bestofislam' ), 403 );
	}
	check_admin_referer( 'boi_sync_accept_count' );
	if ( boi_sync_disabled() ) {
		wp_safe_redirect( boi_sync_screen_url() );
		exit;
	}
	update_option( 'boi_sync_count', count( boi_seed_listicles() ), false );
	boi_sync_log( 'bundle', 'accepted the current article count as the baseline' );
	boi_sync_apply();
	wp_safe_redirect( boi_sync_screen_url() );
	exit;
}
add_action( 'admin_post_boi_sync_accept_count', 'boi_sync_handle_accept_count' );

/** One grouped notice for everything that waits for the owner. */
function boi_sync_notice() {
	if ( ! current_user_can( 'manage_options' ) || boi_sync_disabled() ) {
		return;
	}
	$newer = array();
	$gone  = array();
	foreach ( (array) get_option( 'boi_sync_pending', array() ) as $row ) {
		if ( 'newer' === ( $row['action'] ?? '' ) ) {
			$newer[] = (string) $row['title'];
		} elseif ( in_array( $row['action'] ?? '', array( 'removed', 'superseded' ), true ) ) {
			$gone[] = (string) $row['title'];
		}
	}
	if ( ! $newer && ! $gone ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>' . esc_html__( 'Seeded articles have changes for articles you edited. Nothing was overwritten or trashed.', 'bestofislam' ) . '</p>';
	if ( $newer ) {
		echo '<p>' . esc_html__( 'Newer version available:', 'bestofislam' ) . ' ' . esc_html( implode( '; ', array_slice( $newer, 0, 8 ) ) ) . ( count( $newer ) > 8 ? esc_html( sprintf( /* translators: %d: number of articles. */ __( ' and %d more', 'bestofislam' ), count( $newer ) - 8 ) ) : '' ) . '</p>';
	}
	if ( $gone ) {
		echo '<p>' . esc_html__( 'Removed from the bundle or replaced:', 'bestofislam' ) . ' ' . esc_html( implode( '; ', $gone ) ) . '</p>';
	}
	printf( '<p><a href="%s">%s</a></p></div>', esc_url( boi_sync_screen_url() ), esc_html__( 'Review them under Content', 'bestofislam' ) );
}
add_action( 'admin_notices', 'boi_sync_notice' );

/**
 * The text of a field set as one block, for a diff. The body is cut into lines at block boundaries.
 *
 * @param array $fields Fields.
 * @return string
 */
function boi_sync_blob( $fields ) {
	$out = '';
	foreach ( $fields as $name => $value ) {
		$out .= '[' . $name . "]\n" . str_replace( array( '<!-- /wp:', '<!-- wp:' ), array( "\n<!-- /wp:", "\n<!-- wp:" ), (string) $value ) . "\n\n";
	}
	return $out;
}

/** The Content tab card: a dry run, the owner's choices, a log. It is drawn outside every other form. */
function boi_render_content_sync_card() {
	$labels = array(
		'update'     => __( 'Would update (you have not edited it)', 'bestofislam' ),
		'newer'      => __( 'Newer version available (you edited it, so it stays as it is)', 'bestofislam' ),
		'trash'      => __( 'Would move to the Trash (dropped from the bundle, you have not edited it)', 'bestofislam' ),
		'removed'    => __( 'Dropped from the bundle (you edited it, so it stays)', 'bestofislam' ),
		'supersede'  => __( 'Would be redirected, hidden and moved to the Trash: its replacement is live', 'bestofislam' ),
		'superseded' => __( 'Replaced, but you edited it or the theme has no record of its text, so it stays', 'bestofislam' ),
		'kept'       => __( 'Kept as it is', 'bestofislam' ),
	);
	echo '<div id="boi-content-sync">';
	boi_opt_card_open( __( 'Seeded articles: review', 'bestofislam' ) );
	echo '<p>' . esc_html__( 'What the next sync would do. Nothing here has been changed. An article you edited is never overwritten or trashed.', 'bestofislam' ) . '</p>';
	if ( boi_sync_disabled() ) {
		echo '<p>' . esc_html__( 'The content sync is switched off by BOI_DISABLE_CONTENT_SYNC.', 'bestofislam' ) . '</p>';
	}
	$blocked = boi_sync_removal_block();
	if ( '' !== $blocked && ! boi_sync_disabled() ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html( sprintf( /* translators: %s: reason. */ __( 'Removals are stopped: %s.', 'bestofislam' ), $blocked ) ) . '</p>';
		if ( false !== strpos( $blocked, '10 percent' ) ) {
			printf(
				'<form method="post" action="%s"><input type="hidden" name="action" value="boi_sync_accept_count">%s<p><button type="submit" class="button button-small">%s</button> %s</p></form>',
				esc_url( admin_url( 'admin-post.php' ) ),
				wp_nonce_field( 'boi_sync_accept_count', '_wpnonce', true, false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core's nonce field.
				esc_html__( 'Accept the current article count', 'bestofislam' ),
				esc_html__( 'Use this only when you meant the bundle to shrink.', 'bestofislam' )
			);
		}
		echo '</div>';
	}
	$rows = array_filter( boi_sync_plan(), static function ( $r ) {
		return 'current' !== $r['action'];
	} );
	if ( ! $rows ) {
		echo '<p>' . esc_html__( 'Every article the theme created matches the bundle.', 'bestofislam' ) . '</p>';
	} else {
		$bundle = boi_sync_bundle();
		echo '<table class="widefat striped"><thead><tr><th scope="col">' . esc_html__( 'Article', 'bestofislam' ) . '</th><th scope="col">' . esc_html__( 'The next sync', 'bestofislam' ) . '</th><th scope="col">' . esc_html__( 'Your choice', 'bestofislam' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			echo '<tr><td>' . esc_html( $row['title'] ) . '<br><code>' . esc_html( $row['key'] ) . '</code>';
			if ( 'newer' === $row['action'] && isset( $bundle[ $row['key'] ] ) ) {
				$post = get_post( $row['post_id'] );
				if ( $post && function_exists( 'wp_text_diff' ) ) {
					$a    = $bundle[ $row['key'] ];
					$diff = wp_text_diff( boi_sync_blob( array( 'title' => $post->post_title, 'excerpt' => $post->post_excerpt, 'content' => $post->post_content ) ), boi_sync_blob( array( 'title' => $a['title'], 'excerpt' => $a['excerpt'], 'content' => $a['content'] ) ), array( 'title_left' => __( 'Yours', 'bestofislam' ), 'title_right' => __( 'The bundled text', 'bestofislam' ) ) );
					if ( $diff ) {
						echo '<details><summary>' . esc_html__( 'Show the differences', 'bestofislam' ) . '</summary>' . $diff . '</details>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by core's wp_text_diff(), which escapes the text.
					}
				}
			}
			echo '</td><td>' . esc_html( $labels[ $row['action'] ] ?? $row['action'] ) . '</td><td>';
			if ( ! boi_sync_disabled() && in_array( $row['action'], array( 'newer', 'removed', 'superseded', 'kept' ), true ) ) {
				$take    = isset( $bundle[ $row['key'] ] ) ? __( 'Take the bundled text', 'bestofislam' ) : __( 'Move to the Trash', 'bestofislam' );
				$choices = array( 'take' => $take, 'keep' => __( 'Keep mine', 'bestofislam' ) );
				if ( ! isset( $bundle[ $row['key'] ] ) && '' !== $blocked ) {
					unset( $choices['take'] ); // A removal waits while a guard holds.
				}
				foreach ( $choices as $choice => $text ) {
					printf(
						'<form method="post" action="%1$s" style="display:inline"><input type="hidden" name="action" value="boi_sync_item"><input type="hidden" name="item" value="%2$s"><input type="hidden" name="choice" value="%3$s">%4$s<button type="submit" class="button button-small">%5$s</button></form> ',
						esc_url( admin_url( 'admin-post.php' ) ),
						esc_attr( $row['key'] ),
						esc_attr( $choice ),
						wp_nonce_field( 'boi_sync_item', '_wpnonce', true, false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core's nonce field.
						esc_html( $text )
					);
				}
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}
	if ( ! boi_sync_disabled() ) {
		printf(
			'<form method="post" action="%s" style="margin-top:12px"><input type="hidden" name="action" value="boi_sync_now">%s<button type="submit" class="button">%s</button></form>',
			esc_url( admin_url( 'admin-post.php' ) ),
			wp_nonce_field( 'boi_sync_now', '_wpnonce', true, false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core's nonce field.
			esc_html__( 'Run the review now', 'bestofislam' )
		);
	}
	$log = array_reverse( (array) get_option( 'boi_sync_log', array() ) );
	echo '<h3>' . esc_html__( 'What changed', 'bestofislam' ) . '</h3>';
	if ( ! $log ) {
		echo '<p>' . esc_html__( 'Nothing yet.', 'bestofislam' ) . '</p>';
	} else {
		echo '<table class="widefat striped"><thead><tr><th scope="col">' . esc_html__( 'When', 'bestofislam' ) . '</th><th scope="col">' . esc_html__( 'Article', 'bestofislam' ) . '</th><th scope="col">' . esc_html__( 'What happened', 'bestofislam' ) . '</th></tr></thead><tbody>';
		foreach ( $log as $line ) {
			echo '<tr><td>' . esc_html( wp_date( 'Y-m-d H:i', (int) $line['t'] ) ) . '</td><td>' . esc_html( (string) $line['key'] ) . '</td><td>' . esc_html( (string) $line['action'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
	boi_opt_card_close();
	echo '</div>';
}

/* -------------------------------------------------------------------------
 * Superseded articles: the 301 and the hiding.
 * ---------------------------------------------------------------------- */

/** The 301 from a superseded article to its replacement, at whatever address it answers. */
function boi_superseded_redirect() {
	if ( boi_sync_disabled() || is_admin() || is_preview() || is_feed() || ! boi_supersedes() ) {
		return;
	}
	if ( is_singular() ) {
		$pairs = boi_superseded_pairs();
		$id    = (int) get_queried_object_id();
		if ( isset( $pairs[ $id ] ) ) {
			wp_safe_redirect( get_permalink( $pairs[ $id ] ), 301 );
			exit;
		}
		return;
	}
	if ( ! is_404() || '' !== boi_sync_removal_block() ) {
		return;
	}
	// After the old article is in the Trash its address is a 404. The last path segment is its slug, whatever
	// the permalink base. Only an article the theme created and moved to the Trash qualifies.
	$path = trim( (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH ), '/' );
	$slug = sanitize_title( (string) basename( $path ) );
	$map  = boi_supersedes();
	if ( '' === $slug || ! isset( $map[ $slug ] ) ) {
		return;
	}
	$old = get_posts( array( 'post_type' => 'post', 'post_status' => 'trash', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_boi_sync_key', 'meta_value' => $slug ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key,WordPress.DB.SlowDBQuery.slow_query_meta_value -- one lookup on a 404.
	$new = get_page_by_path( $map[ $slug ], OBJECT, 'post' );
	if ( $old && $new && 'publish' === $new->post_status ) {
		wp_safe_redirect( get_permalink( $new ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'boi_superseded_redirect', 1 );

/**
 * While the unlist module is in charge, superseded articles are part of its list (see boi_unlisted_ids()). When
 * it stands down (the Unlist Posts plugin is active, or its switch is off), this hides them: out of lists,
 * search and feeds, out of the sitemaps, and noindex on their own page. An editor at work sees everything.
 */
function boi_superseded_fallback() {
	if ( boi_unlist_enabled() ) {
		return;
	}
	add_action(
		'pre_get_posts',
		static function ( $q ) {
			if ( boi_sync_disabled() || ( is_admin() && ! wp_doing_ajax() ) || $q->is_singular() || $q->get( 'p' ) || $q->get( 'name' ) || $q->get( 'pagename' ) ) {
				return;
			}
			$ids = boi_superseded_ids();
			if ( $ids && ! $q->get( 'post__in' ) ) {
				$q->set( 'post__not_in', array_values( array_unique( array_merge( (array) $q->get( 'post__not_in' ), $ids ) ) ) );
			}
		}
	);
	add_filter(
		'wp_sitemaps_posts_query_args',
		static function ( $args ) {
			$ids = boi_superseded_ids();
			if ( $ids ) {
				$args['post__not_in'] = array_merge( (array) ( $args['post__not_in'] ?? array() ), $ids );
			}
			return $args;
		}
	);
	add_filter(
		'wp_robots',
		static function ( $robots ) {
			return is_singular() && in_array( (int) get_queried_object_id(), boi_superseded_ids(), true ) ? wp_robots_no_robots( $robots ) : $robots;
		}
	);
	add_filter(
		'rank_math/frontend/robots',
		static function ( $robots ) {
			if ( is_singular() && in_array( (int) get_queried_object_id(), boi_superseded_ids(), true ) ) {
				$robots['index'] = 'noindex';
			}
			return $robots;
		}
	);
	add_filter(
		'rank_math/sitemap/posts_to_exclude',
		static function ( $ids ) {
			$ids = is_array( $ids ) ? $ids : preg_split( '/[\\s,]+/', (string) $ids, -1, PREG_SPLIT_NO_EMPTY );
			return array_values( array_unique( array_merge( array_filter( array_map( 'absint', $ids ) ), boi_superseded_ids() ) ) );
		}
	);
	add_filter(
		'rank_math/sitemap/entry',
		static function ( $url, $type, $object ) {
			return 'post' === $type && $object instanceof WP_Post && in_array( (int) $object->ID, boi_superseded_ids(), true ) ? false : $url;
		},
		10,
		3
	);
}
add_action( 'init', 'boi_superseded_fallback', 20 );
