<?php
/**
 * Content auto-population.
 *
 * Runs once on activation and creates the topic taxonomy terms, four listicles
 * built from the block set, a front page, and the primary navigation menu. The
 * listicle bodies are structural skeletons: titles and objections are in place
 * so the layout is populated, while the responses are left for the author.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;


/**
 * Saves a new post. WordPress removes one level of backslashes from what it
 * is given, so the data is slashed first; without this, escaped quotation
 * marks inside block settings were stripped and the settings became
 * unreadable.
 *
 * @param array $postarr Post data.
 * @param bool  $wp_error Whether to return a WP_Error on failure.
 * @return int|WP_Error
 */
function boi_insert_post( $postarr, $wp_error = false ) {
	return wp_insert_post( wp_slash( $postarr ), $wp_error );
}

/**
 * Updates a post, slashing the data for the same reason.
 *
 * @param array $postarr Post data, including ID.
 * @return int|WP_Error
 */
function boi_update_post( $postarr ) {
	return wp_update_post( wp_slash( $postarr ) );
}

/**
 * A fingerprint of block content in the form WordPress stores it.
 * WordPress rewrites the formatting of block settings on saving, so the
 * content is passed through its own parser and serialiser before hashing,
 * and a file and its saved copy produce the same fingerprint.
 *
 * @param string $content Block content.
 * @return string
 */
function boi_content_fingerprint( $content ) {
	return md5( serialize_blocks( parse_blocks( (string) $content ) ) );
}

/**
 * Reads one JSON data file from inc/data/, once per request.
 *
 * Seed data is JSON, read and decoded, never executed. A file that is
 * missing (briefly, while an update unpacks) or not valid JSON reads as an
 * empty array, so the seeder creates nothing from it instead of failing.
 *
 * @param string $name File name without extension.
 * @return array
 */
function boi_read_data( $name ) {
	static $cache = array();
	if ( ! isset( $cache[ $name ] ) ) {
		$file = BOI_DIR . '/inc/data/' . $name . '.json';
		$data = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$cache[ $name ] = is_array( $data ) ? $data : array();
	}
	return $cache[ $name ];
}

/**
 * One part of the seed list in inc/data/seed.json: topics, listicles or pages.
 *
 * @param string $part Key in the file.
 * @return array
 */
function boi_seed_data( $part ) {
	$data = boi_read_data( 'seed' );
	return isset( $data[ $part ] ) && is_array( $data[ $part ] ) ? $data[ $part ] : array();
}

/**
 * Whether the seed list could be read: topics, listicles and pages all
 * present. While it cannot (an update still unpacking, a damaged file),
 * population does nothing and the version is not recorded, so the next
 * request tries again.
 *
 * @return bool
 */
function boi_seed_ready() {
	return boi_seed_data( 'topics' ) && boi_seed_data( 'listicles' ) && boi_seed_data( 'pages' );
}

/**
 * Returns the topic terms created on activation, from `topics` in
 * inc/data/seed.json.
 *
 * @return array
 */
function boi_seed_topics() {
	return boi_seed_data( 'topics' );
}

/**
 * Returns the listicles created on activation.
 *
 * Each entry (slug, title, excerpt, topic) is in `listicles` in
 * inc/data/seed.json, and names a file in /content/, which holds the
 * finished article as block markup inside a JSON file. Keeping seed data
 * out of PHP means an editor can revise it without touching code.
 *
 * @return array
 */
function boi_seed_listicles() {
	return boi_seed_data( 'listicles' );
}

/**
 * Loads an article's block markup from the content directory.
 *
 * Articles are stored as JSON, each holding its block markup under a
 * "content" key, rather than as raw .html files.
 *
 * @param string $slug Article slug.
 * @return string Block markup, or an empty string when the file is missing
 *                or malformed.
 */
function boi_load_article( $slug ) {
	$path = BOI_DIR . '/content/' . sanitize_file_name( $slug ) . '.json';

	if ( ! file_exists( $path ) ) {
		return '';
	}

	$raw  = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$data = json_decode( $raw, true );

	if ( ! is_array( $data ) || ! isset( $data['content'] ) || ! is_string( $data['content'] ) ) {
		return '';
	}

	return $data['content'];
}

/**
 * Creates the topic terms.
 *
 * @return array Map of slug to term identifier.
 */
function boi_create_topics() {
	$map = array();

	if ( isset( $pages['blog'] ) ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => __( 'Reflections', 'bestofislam' ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $pages['blog'],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	foreach ( boi_seed_topics() as $slug => $topic ) {
		$existing = term_exists( $slug, 'listicle-topic' );

		if ( $existing ) {
			$map[ $slug ] = (int) $existing['term_id'];

			// A section that was once nested under another is promoted when the seed says so.
			$current = get_term( $map[ $slug ], 'listicle-topic' );
			$parent  = ( '' !== $topic['parent'] && isset( $map[ $topic['parent'] ] ) ) ? $map[ $topic['parent'] ] : 0;

			if ( $current && ! is_wp_error( $current ) && (int) $current->parent !== $parent && '' === $topic['parent'] ) {
				wp_update_term( $map[ $slug ], 'listicle-topic', array( 'parent' => 0 ) );
			}
			continue;
		}

		$args = array(
			'slug'        => $slug,
			'description' => $topic['description'],
		);

		// Parents are declared before their children in the seed array, so the
		// parent identifier is always present by the time a child is reached.
		if ( '' !== $topic['parent'] && isset( $map[ $topic['parent'] ] ) ) {
			$args['parent'] = $map[ $topic['parent'] ];
		}

		$term = wp_insert_term( $topic['name'], 'listicle-topic', $args );

		if ( ! is_wp_error( $term ) ) {
			$map[ $slug ] = (int) $term['term_id'];
		}
	}

	return $map;
}

/**
 * Seeded article slugs that have changed, old => current. Addresses carry
 * the subject, never a verse number or a collision suffix.
 *
 * @return array
 */
function boi_seed_renames() {
	return (array) apply_filters(
		'boi_seed_renames',
		array(
			'quran-4-34' => 'does-the-quran-allow-wife-beating',
		)
	);
}

/**
 * Renames a published article's slug in place when the seed list has moved
 * on, so the post keeps its identity, comments and attachments. The theme
 * does not redirect the old address, so the note WordPress keeps of the old
 * slug for its own redirect is removed; the fingerprint that tells an
 * untouched article from an edited one moves with the post.
 *
 * @return void
 */
function boi_rename_seeded_posts() {
	foreach ( boi_seed_renames() as $old => $new ) {
		$post    = get_page_by_path( $old, OBJECT, 'post' );
		$renamed = get_page_by_path( $new, OBJECT, 'post' );

		// A post renamed by an earlier release still carries the old-slug note
		// WordPress keeps for its own redirect; the theme does not redirect.
		if ( $renamed ) {
			delete_post_meta( $renamed->ID, '_wp_old_slug', $old );
		}

		if ( ! $post || $renamed ) {
			continue;
		}

		boi_update_post(
			array(
				'ID'        => $post->ID,
				'post_name' => $new,
			)
		);
		delete_post_meta( $post->ID, '_wp_old_slug' );

		$hashes = (array) get_option( 'boi_article_hashes', array() );

		if ( isset( $hashes[ $old ] ) ) {
			$hashes[ $new ] = $hashes[ $old ];
			unset( $hashes[ $old ] );
			update_option( 'boi_article_hashes', $hashes, false );
		}
	}
}

/**
 * Creates the seed listicles.
 *
 * @param array $topics Map of topic slug to term identifier.
 * @return array Created post identifiers.
 */
function boi_create_listicles( $topics ) {
	$created = array();

	boi_rename_seeded_posts();

	foreach ( boi_seed_listicles() as $listicle ) {
		if ( get_page_by_path( $listicle['slug'], OBJECT, 'post' ) ) {
			continue;
		}

		$content = boi_load_article( $listicle['slug'] );

		if ( '' === $content ) {
			continue;
		}

		$post_id = boi_insert_post(
			array(
				'post_title'   => $listicle['title'],
				'post_name'    => $listicle['slug'],
				'post_excerpt' => $listicle['excerpt'],
				'post_content' => $content,
				'post_status'  => isset( $listicle['status'] ) ? $listicle['status'] : 'publish',
				'post_author'  => boi_seed_author(),
				'post_type'    => 'post',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		if ( isset( $topics[ $listicle['topic'] ] ) ) {
			wp_set_object_terms( $post_id, array( $topics[ $listicle['topic'] ] ), 'listicle-topic' );
		}

		$created[] = $post_id;
	}

	return $created;
}

/**
 * Standing page definitions: title and content, keyed by role.
 *
 * Built from `pages` in inc/data/seed.json. Each page lists its blocks in
 * order: ["p", text] and ["h", text] are escaped here into a paragraph or a
 * heading; ["html", markup] is block markup used as it stands.
 *
 * @return array
 */
function boi_seed_pages() {
	$p = function ( $text ) {
		return "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$h = function ( $text ) {
		return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $text ) . "</h2>\n<!-- /wp:heading -->\n\n";
	};

	$pages = array();
	foreach ( boi_seed_data( 'pages' ) as $key => $page ) {
		$content = '';
		foreach ( (array) ( $page['blocks'] ?? array() ) as $block ) {
			list( $type, $value ) = array_pad( (array) $block, 2, '' );
			if ( 'p' === $type ) {
				$content .= $p( (string) $value );
			} elseif ( 'h' === $type ) {
				$content .= $h( (string) $value );
			} else {
				$content .= (string) $value;
			}
		}
		unset( $page['blocks'] );
		$page['content'] = $content;
		$pages[ $key ]   = $page;
	}

	return $pages;
}

/**
 * Content the theme would seed for one standing page.
 *
 * @param string $key Page key.
 * @return string
 */
function boi_seed_page_content( $key ) {
	$pages = boi_seed_pages();

	return isset( $pages[ $key ] ) ? (string) $pages[ $key ]['content'] : '';
}

/**
 * Creates the front page and the about page.
 *
 * @return array Map of key to post identifier.
 */
function boi_create_pages() {
	$pages = boi_seed_pages();

	$map = array();

	foreach ( $pages as $key => $page ) {
		$title_slug = sanitize_title( $page['title'] );
		$slug       = isset( $page['slug'] ) ? $page['slug'] : $title_slug;
		$existing   = get_page_by_path( $slug, OBJECT, 'page' );

		// A page the theme made under an earlier name (Site map at /site-map/) is
		// deleted outright, without a redirect, and the page is made again at the
		// address the seed now names. A page already at the new address that still
		// carries the earlier title is renamed.
		if ( ! empty( $page['legacy']['slug'] ) && $page['legacy']['slug'] !== $slug ) {
			$old = get_page_by_path( $page['legacy']['slug'], OBJECT, 'page' );

			if ( $old ) {
				wp_delete_post( $old->ID, true );
			}
		}

		if ( $existing && ! empty( $page['legacy']['title'] ) && $existing->post_title === $page['legacy']['title'] ) {
			boi_update_post(
				array(
					'ID'         => $existing->ID,
					'post_title' => $page['title'],
				)
			);
		}

		if ( $existing ) {
			$map[ $key ] = (int) $existing->ID;
			continue;
		}

		$post_id = boi_insert_post(
			array(
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => (string) $page['content'],
				'post_status'  => 'publish',
				'post_author'  => boi_seed_author(),
				'post_type'    => 'page',
			)
		);

		if ( ! is_wp_error( $post_id ) && $post_id ) {
			$map[ $key ] = (int) $post_id;
		}
	}

	return $map;
}

/**
 * Creates the navigation as a wp_navigation post.
 *
 * Block themes read navigation from this post type, not from classic menus,
 * so this is what the header's navigation block resolves to when an editor
 * picks it in the Site Editor. The header part ships static links as well,
 * so the menu is present on activation with no selection required.
 *
 * @param array $topics Map of topic slug to term identifier.
 * @param array $pages  Map of page key to post identifier.
 * @return int Navigation post identifier, or 0.
 */
function boi_create_navigation( $topics, $pages ) {
	$existing = get_posts(
		array(
			'post_type'      => 'wp_navigation',
			'name'           => 'primary',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$links = '';

	if ( isset( $pages['blog'] ) ) {
		$links .= boi_nav_link_block( __( 'Reflections', 'bestofislam' ), get_permalink( $pages['blog'] ), $pages['blog'], 'page' );
	}

	foreach ( boi_seed_topics() as $slug => $topic ) {
		if ( ! isset( $topics[ $slug ] ) || '' !== $topic['parent'] ) {
			continue;
		}

		$links .= boi_nav_link_block( $topic['name'], get_term_link( (int) $topics[ $slug ], 'listicle-topic' ), $topics[ $slug ], 'listicle-topic' );
	}

	foreach ( array( 'about' => __( 'About', 'bestofislam' ), 'faq' => __( 'FAQ', 'bestofislam' ), 'contact' => __( 'Contact', 'bestofislam' ) ) as $key => $label ) {
		if ( isset( $pages[ $key ] ) ) {
			$links .= boi_nav_link_block( $label, get_permalink( $pages[ $key ] ), $pages[ $key ], 'page' );
		}
	}

	$id = boi_insert_post(
		array(
			'post_type'    => 'wp_navigation',
			'post_title'   => __( 'Primary', 'bestofislam' ),
			'post_name'    => 'primary',
			'post_status'  => 'publish',
			'post_author'  => boi_seed_author(),
			'post_content' => $links,
		)
	);

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Builds one navigation-link block.
 *
 * @param string     $label Link text.
 * @param string     $url   Destination.
 * @param int        $id    Object identifier.
 * @param string     $kind  page or taxonomy slug.
 * @return string
 */
function boi_nav_link_block( $label, $url, $id, $kind ) {
	$attrs = array(
		'label' => $label,
		'url'   => $url,
		'id'    => (int) $id,
		'kind'  => 'page' === $kind ? 'post-type' : 'taxonomy',
		'type'  => $kind,
	);

	return '<!-- wp:navigation-link ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' /-->' . "\n";
}

/**
 * Removes the WordPress sample content.
 *
 * @return void
 */
function boi_remove_sample_content() {
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );

	if ( $hello ) {
		wp_trash_post( $hello->ID );
	}

	$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );

	if ( $sample ) {
		wp_trash_post( $sample->ID );
	}
}

/**
 * Populates the site. Idempotent: nothing is duplicated on a second run.
 *
 * @return array Summary counts.
 */
function boi_populate_content() {
	if ( ! boi_seed_ready() ) {
		return array(
			'topics'    => 0,
			'listicles' => 0,
			'pages'     => 0,
		);
	}

	$topics    = boi_create_topics();
	$pages     = boi_create_pages();
	$listicles = boi_create_listicles( $topics );

	boi_create_navigation( $topics, $pages );
	boi_remove_sample_content();

	// Set the front page and posts page only when the site has not chosen
	// its own: population runs again on updates, and must not undo a choice
	// the owner made after activation.
	// "Chosen" means a published page: a draft or trashed one is as good as none.
	$usable = function ( $id ) {
		$page = $id ? get_post( $id ) : null;
		return $page && 'page' === $page->post_type && 'publish' === $page->post_status;
	};

	$front = (int) get_option( 'page_on_front' );

	if ( isset( $pages['home'] ) && ( 'page' !== get_option( 'show_on_front' ) || ! $usable( $front ) ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
	}

	$posts_page = (int) get_option( 'page_for_posts' );

	if ( isset( $pages['blog'] ) && ! $usable( $posts_page ) ) {
		update_option( 'page_for_posts', $pages['blog'] );
	}

	boi_backfill_listicle_flags();
	boi_migrate_seed( $topics );

	// The same atomic lock the front-end image import uses, so the two cannot
	// sideload an image at once.
	if ( function_exists( 'boi_attach_featured_images' ) && boi_images_lock_take() ) {
		boi_attach_featured_images();
		boi_images_lock_release();
	}

	boi_migrate_pages( $pages );
	boi_retire_questions_page();
	boi_repair_seed_authors();

	if ( function_exists( 'boi_stagger_seed_dates' ) ) {
		boi_stagger_seed_dates();
	}

	update_option( 'boi_content_populated', gmdate( 'c' ) );

	return array(
		'topics'    => count( $topics ),
		'listicles' => count( $listicles ),
		'pages'     => count( $pages ),
	);
}

/**
 * Runs population once, on first activation.
 *
 * @return void
 */
function boi_populate_on_activation() {
	if ( get_option( 'boi_content_populated' ) ) {
		return;
	}

	boi_register_taxonomy();
	boi_populate_content();
}
add_action( 'after_switch_theme', 'boi_populate_on_activation', 20 );

/**
 * Handles a manual re-run from the settings screen.
 *
 * @return void
 */
function boi_handle_populate_request() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not permitted to do that.', 'bestofislam' ) );
	}

	check_admin_referer( 'boi_populate' );

	$summary = boi_populate_content();

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'          => 'boi-settings',
				'tab'           => 'content',
				'boi_populated' => (int) $summary['listicles'],
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_post_boi_populate', 'boi_handle_populate_request' );

/**
 * Corrects records left by earlier versions of the seed.
 *
 * Population never overwrites, so a term created at the top level by an old
 * seed stays there, and a post created with an old title keeps it. This pass
 * moves terms to the parent the current seed declares, and updates the title
 * and excerpt of any seeded post that still carries an earlier version.
 *
 * @param array $topics Map of topic slug to term identifier.
 * @return void
 */
function boi_migrate_seed( $topics ) {
	foreach ( boi_seed_topics() as $slug => $topic ) {
		if ( ! isset( $topics[ $slug ] ) ) {
			continue;
		}

		$term = get_term( $topics[ $slug ], 'listicle-topic' );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$parent = '' !== $topic['parent'] && isset( $topics[ $topic['parent'] ] ) ? (int) $topics[ $topic['parent'] ] : 0;
		$args   = array();

		if ( (int) $term->parent !== $parent ) {
			$args['parent'] = $parent;
		}

		if ( $term->description !== $topic['description'] ) {
			$args['description'] = $topic['description'];
		}

		if ( $args ) {
			wp_update_term( $term->term_id, 'listicle-topic', $args );
		}
	}

	foreach ( boi_seed_listicles() as $listicle ) {
		$post = get_page_by_path( $listicle['slug'], OBJECT, 'post' );

		if ( ! $post ) {
			continue;
		}

		$args = array();

		// Refresh the body only while the owner has not edited it. Four cases:
		// already current; damaged by the old unslashed save and otherwise
		// untouched, which is repaired; recorded as written by the theme,
		// which is refreshed; anything else is left for the owner to decide
		// from the Content tab.
		$fresh   = boi_load_article( $listicle['slug'] );
		$hashes  = (array) get_option( 'boi_article_hashes', array() );
		$slug    = $listicle['slug'];
		$current = boi_content_fingerprint( $post->post_content );

		// The theme's own text may be refreshed only while the owner has not
		// touched the article. An edited body means the title and excerpt may
		// have been edited too, so they are left alone as well.
		$untouched = false;

		if ( '' !== $fresh ) {
			$target  = boi_content_fingerprint( $fresh );
			$damaged = boi_content_fingerprint( wp_unslash( $fresh ) );

			if ( $current === $target ) {
				$hashes[ $slug ] = $current;
				update_option( 'boi_article_hashes', $hashes, false );
				$untouched = true;
			} elseif ( ( isset( $hashes[ $slug ] ) && $hashes[ $slug ] === $current )
				|| $current === $damaged
				|| ( ! isset( $hashes[ $slug ] ) && $post->post_modified_gmt === $post->post_date_gmt ) ) {
				$args['post_content'] = $fresh;
				$untouched            = true;
			}
		}

		// Title and excerpt are judged on their own record: a body left alone
		// says nothing about whether the owner reworded the title.
		$text_hashes = (array) get_option( 'boi_article_text_hashes', array() );
		$text_now    = md5( $post->post_title . "\n" . $post->post_excerpt );
		$text_seed   = md5( $listicle['title'] . "\n" . $listicle['excerpt'] );
		$text_free   = $text_now === $text_seed
			|| ( isset( $text_hashes[ $slug ] ) && $text_hashes[ $slug ] === $text_now )
			|| ( ! isset( $text_hashes[ $slug ] ) && $post->post_modified_gmt === $post->post_date_gmt );

		if ( $text_free ) {
			if ( $post->post_title !== $listicle['title'] ) {
				$args['post_title'] = $listicle['title'];
			}

			if ( $post->post_excerpt !== $listicle['excerpt'] ) {
				$args['post_excerpt'] = $listicle['excerpt'];
			}

			$text_hashes[ $slug ] = $text_seed;
			update_option( 'boi_article_text_hashes', $text_hashes, false );
		}

		if ( isset( $topics[ $listicle['topic'] ] ) ) {
			wp_set_object_terms( $post->ID, array( $topics[ $listicle['topic'] ] ), 'listicle-topic' );
		}

		if ( $args ) {
			$args['ID'] = $post->ID;
			boi_update_post( $args );

			if ( isset( $args['post_content'] ) ) {
				$saved           = get_post( $post->ID );
				$hashes          = (array) get_option( 'boi_article_hashes', array() );
				$hashes[ $slug ] = boi_content_fingerprint( $saved->post_content );
				update_option( 'boi_article_hashes', $hashes, false );
			}
		}
	}
}

/**
 * Brings the content of seeded standing pages up to date, but only while the
 * owner has not edited them, so a customised page is never overwritten.
 *
 * @param array $pages Map of page key to post identifier.
 * @return void
 */
function boi_migrate_pages( $pages ) {
	$hashes = (array) get_option( 'boi_page_hashes', array() );

	foreach ( array( 'about', 'faq', 'sources', 'sitemap' ) as $key ) {
		if ( empty( $pages[ $key ] ) ) {
			continue;
		}

		$post = get_post( $pages[ $key ] );

		if ( ! $post ) {
			continue;
		}

		$current = boi_content_fingerprint( $post->post_content );
		$fresh   = boi_seed_page_content( $key );

		// Unedited means the stored content still matches what the theme last
		// wrote. With no record, the page counts as unedited only if it already
		// holds the theme's text (a page the theme has just created); otherwise
		// an edit cannot be ruled out and the page is left as it is.
		if ( isset( $hashes[ $key ] ) ) {
			if ( $hashes[ $key ] !== $current ) {
				continue;
			}
		} elseif ( '' === $fresh || boi_content_fingerprint( $fresh ) !== $current ) {
			continue;
		}

		if ( '' !== $fresh && boi_content_fingerprint( $fresh ) !== $current ) {
			boi_update_post( array( 'ID' => $post->ID, 'post_content' => $fresh ) );
			$current = boi_content_fingerprint( get_post( $post->ID )->post_content );
		}

		$hashes[ $key ] = $current;
	}

	update_option( 'boi_page_hashes', $hashes, false );
}

/**
 * Removes the Questions answered page seeded in 1.34.0, now that the answers
 * live on the front page. The page is moved to the bin, so it can be
 * restored, and only while its content is still what the theme wrote.
 *
 * @return void
 */
function boi_retire_questions_page() {
	$page = get_page_by_path( 'questions-answered', OBJECT, 'page' );

	if ( ! $page || 'trash' === $page->post_status ) {
		return;
	}

	if ( false === strpos( $page->post_content, '<!-- wp:bestofislam/questions /-->' ) ) {
		return;
	}

	$hashes = (array) get_option( 'boi_page_hashes', array() );

	// Trash it only when the record proves the owner has not edited it.
	if ( ! isset( $hashes['questions'] ) || ( md5( $page->post_content ) !== $hashes['questions'] && boi_content_fingerprint( $page->post_content ) !== $hashes['questions'] ) ) {
		return;
	}

	wp_trash_post( $page->ID );
	unset( $hashes['questions'] );
	update_option( 'boi_page_hashes', $hashes, false );
}

/**
 * The user seeded content belongs to: the site's first administrator.
 *
 * Population can run on a front-end request, where nobody is logged in, and
 * WordPress assigns new posts to the current user. Without an explicit author
 * the seeded articles belonged to no one: no author card, an empty author
 * archive, and no author in the Article structured data.
 *
 * @return int User identifier, or 0 when the site has no administrator.
 */
function boi_seed_author() {
	static $id = null;

	if ( null === $id ) {
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'orderby' => 'ID',
				'order'   => 'ASC',
				'number'  => 1,
				'fields'  => 'ID',
			)
		);

		$id = $admins ? (int) $admins[0] : 0;
	}

	return $id;
}

/**
 * Gives an author to seeded articles and pages that were created without
 * one. Posts that already have an author, including any the owner has
 * reassigned, are left alone.
 *
 * @return int Number of posts repaired.
 */
function boi_repair_seed_authors() {
	$author = boi_seed_author();

	if ( ! $author ) {
		return 0;
	}

	$slugs = array();

	foreach ( boi_seed_listicles() as $listicle ) {
		$slugs[] = $listicle['slug'];
	}

	$fixed = 0;

	foreach ( get_posts(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'author'         => 0,
		)
	) as $post ) {
		if ( 0 !== (int) $post->post_author ) {
			continue;
		}

		if ( 'post' === $post->post_type && ! in_array( $post->post_name, $slugs, true ) ) {
			continue;
		}

		boi_update_post(
			array(
				'ID'          => $post->ID,
				'post_author' => $author,
			)
		);
		++$fixed;
	}

	return $fixed;
}

/**
 * Seeded articles whose text differs from this release's, and which the
 * update therefore left alone because it cannot tell whether the owner
 * edited them.
 *
 * @return WP_Post[] Keyed by slug.
 */
function boi_differing_articles() {
	$out = array();

	foreach ( boi_seed_listicles() as $listicle ) {
		$post  = get_page_by_path( $listicle['slug'], OBJECT, 'post' );
		$fresh = boi_load_article( $listicle['slug'] );

		if ( ! $post || '' === $fresh ) {
			continue;
		}

		if ( boi_content_fingerprint( $post->post_content ) !== boi_content_fingerprint( $fresh ) ) {
			$out[ $listicle['slug'] ] = $post;
		}
	}

	return $out;
}

/**
 * Brings the articles the owner selects on the Content tab up to this
 * release's text, replacing whatever they currently hold.
 *
 * @return void
 */
function boi_handle_refresh_articles() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'bestofislam' ) );
	}

	check_admin_referer( 'boi_refresh_articles' );

	$chosen  = isset( $_POST['boi_articles'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_POST['boi_articles'] ) ) : array();
	$allowed = boi_differing_articles();
	$hashes  = (array) get_option( 'boi_article_hashes', array() );
	$done    = 0;

	foreach ( $chosen as $slug ) {
		if ( ! isset( $allowed[ $slug ] ) ) {
			continue;
		}

		boi_update_post(
			array(
				'ID'           => $allowed[ $slug ]->ID,
				'post_content' => boi_load_article( $slug ),
			)
		);

		$hashes[ $slug ] = boi_content_fingerprint( get_post( $allowed[ $slug ]->ID )->post_content );
		++$done;
	}

	update_option( 'boi_article_hashes', $hashes, false );

	wp_safe_redirect( add_query_arg( array( 'page' => 'boi-settings', 'tab' => 'content', 'boi_refreshed' => $done ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_boi_refresh_articles', 'boi_handle_refresh_articles' );
