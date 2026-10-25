<?php
/**
 * Glossary: an A to Z of the terms that come up when Islam is discussed.
 *
 * Built from ordinary pages, a Glossary page with one child page per term, so
 * an editor can add or change an entry like any other page. The theme adds
 * what pages lack: a generated A to Z index, a related-links block, structured
 * data for the terms, and a first set of entries, created once and updated
 * only while the owner has not edited them.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

require_once BOI_DIR . '/inc/glossary-data.php';

const BOI_GLOSSARY_VERSION = '8';

/** Entries created or refreshed per request, so a large glossary seeds in steps. */
const BOI_GLOSSARY_BATCH = 30;

/**
 * Pages have no excerpt by default; each term keeps its one-line definition
 * there, and the index and meta description draw on it.
 *
 * @return void
 */
function boi_glossary_support() {
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'boi_glossary_support' );

/**
 * The Glossary page, or null.
 *
 * @return WP_Post|null
 */
function boi_glossary_parent() {
	$page = get_page_by_path( 'glossary', OBJECT, 'page' );

	return ( $page && 'trash' !== $page->post_status ) ? $page : null;
}

/**
 * Whether the current view is the Glossary page or one of its terms.
 *
 * @return bool
 */
function boi_is_glossary_page() {
	if ( ! is_page() ) {
		return false;
	}

	$post = get_queried_object();

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( 'glossary' === $post->post_name && ! $post->post_parent ) {
		return true;
	}

	$parent = $post->post_parent ? get_post( $post->post_parent ) : null;

	return $parent && 'glossary' === $parent->post_name && ! $parent->post_parent;
}

/**
 * Builds a term's page content as block markup.
 *
 * @param array $t Term.
 * @return string
 */
function boi_glossary_content( $t ) {
	$p = function ( $text, $class = '' ) {
		$attr = '' !== $class ? ' {"className":"' . $class . '"}' : '';
		$cls  = '' !== $class ? ' class="' . $class . '"' : '';
		return '<!-- wp:paragraph' . $attr . " -->\n<p" . $cls . '>' . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$h = function ( $text ) {
		return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . $text . "</h2>\n<!-- /wp:heading -->\n\n";
	};

	$types = boi_glossary_types();
	$kind  = isset( $t['type'], $types[ $t['type'] ] ) ? $types[ $t['type'] ] : '';

	$out  = "<!-- wp:group {\"className\":\"boi-term\"} -->\n<div class=\"wp-block-group boi-term\">";
	if ( ! empty( $t['ar'] ) ) {
		// A handful of entries quote actual Quranic text (verified against
		// the Uthmani text, not just claimed) rather than a name, term or
		// non-Quranic formula; those alone get the Uthmanic Quran face.
		$script_class = ! empty( $t['quran_ref'] ) ? 'boi-term__script boi-script-quran' : 'boi-term__script';
		$out         .= '<p class="' . $script_class . '" lang="ar" dir="rtl">' . $t['ar'] . boi_glossary_epithet_html( $t ) . '</p>';
	} elseif ( '' !== boi_glossary_epithet_html( $t, false ) ) {
		$out .= '<p class="boi-term__script" lang="ar" dir="rtl">' . boi_glossary_epithet_html( $t, false ) . '</p>';
	}
	if ( ! empty( $t['tr'] ) ) {
		$out .= '<p class="boi-term__translit">' . $t['tr'] . '</p>';
	}
	if ( 'term' !== $t['type'] && '' !== $kind ) {
		$out .= '<p class="boi-term__kind">' . $kind . '</p>';
	}
	$out .= "</div>\n<!-- /wp:group -->\n\n";

	$out .= $p( $t['def'], 'boi-term__def' );

	$out .= $h( 'What it means' );
	foreach ( $t['what'] as $para ) {
		$out .= $p( $para );
	}

	if ( ! empty( $t['misread'] ) ) {
		$out .= "<!-- wp:group {\"className\":\"boi-term__misread\"} -->\n<div class=\"wp-block-group boi-term__misread\">";
		$out .= boi_add_heading_icon( '<p class="boi-term__label">Often misunderstood as</p>' );
		$out .= '<p>' . $t['misread'] . '</p>';
		$out .= "</div>\n<!-- /wp:group -->\n\n";
	}

	if ( ! empty( $t['sources'] ) ) {
		$out .= $h( 'What the sources say' );
		foreach ( $t['sources'] as $para ) {
			$out .= $p( $para );
		}
	}

	$out .= boi_glossary_faruqi_note( $t );

	$attrs = wp_json_encode(
		array(
			'terms'    => array_values( isset( $t['terms'] ) ? $t['terms'] : array() ),
			'articles' => array_values( isset( $t['articles'] ) ? $t['articles'] : array() ),
		)
	);

	return $out . '<!-- wp:bestofislam/term-related ' . $attrs . " /-->\n";
}

/**
 * The note naming where an entry's term and Arabic come from: the lists in
 * Ismail al Faruqi's Toward Islamic English. An entry written first from other
 * sources and later found in the book carries a list of its book entries.
 *
 * @param array $t Entry.
 * @return string Block markup.
 */
function boi_glossary_faruqi_note( $t ) {
	if ( empty( $t['faruqi'] ) ) {
		return '';
	}

	$items = isset( $t['faruqi'][0] ) ? $t['faruqi'] : array( $t['faruqi'] );
	$bits  = array();

	foreach ( $items as $item ) {
		if ( empty( $item['page'] ) ) {
			continue;
		}

		$bits[] = sprintf( 'p. %d', (int) $item['page'] ) . ( ! empty( $item['term'] ) ? ', listed as ' . esc_html( $item['term'] ) : '' );
	}

	if ( ! $bits ) {
		return '';
	}

	return "<!-- wp:paragraph {\"className\":\"boi-term__source\"} -->\n<p class=\"boi-term__source\">" . 'Term and Arabic as listed in ' . boi_glossary_faruqi_name() . ', <em>Toward Islamic English</em> (International Institute of Islamic Thought, 1995), ' . implode( '; ', $bits ) . ". The definition is restated here.</p>\n<!-- /wp:paragraph -->\n\n";
}

/**
 * The address of a Glossary entry, by slug, or an empty string.
 *
 * @param string $slug Entry slug.
 * @return string
 */
function boi_glossary_entry_url( $slug ) {
	static $cache = array();

	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	$url    = '';
	$parent = boi_glossary_parent();

	if ( $parent ) {
		// Pages are found by their path, parent included.
		$found = get_page_by_path( get_page_uri( $parent ) . '/' . $slug );
		$url   = ( $found && 'publish' === $found->post_status ) ? get_permalink( $found ) : '';
	}

	$cache[ $slug ] = $url;

	return $url;
}

/**
 * Ismail al Faruqi's name, linked to his Glossary entry when it exists.
 *
 * @return string HTML.
 */
function boi_glossary_faruqi_name() {
	// Built from the Glossary's own address, so the link is right even when
	// it is written into an entry before his entry exists in the sync.
	$parent = boi_glossary_parent();
	$url    = $parent ? trailingslashit( get_permalink( $parent ) ) . user_trailingslashit( 'ismail-raji-al-faruqi' ) : '';

	return $url ? '<a href="' . esc_url( $url ) . '">Ismail al Faruqi</a>' : 'Ismail al Faruqi';
}

/**
 * The Glossary page's own content.
 *
 * @return string
 */
function boi_glossary_parent_content() {
	return "<!-- wp:paragraph -->\n<p>Plain explanations of the words that come up most often when Islam is discussed: what each one means, how Muslims understand it, and what the sources say. Every entry sets a common misunderstanding beside the answer to it.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:bestofislam/glossary-index /-->\n";
}

/**
 * Creates the Glossary and its entries, and refreshes entries the owner has
 * not edited. A large glossary is seeded in steps of BOI_GLOSSARY_BATCH
 * entries, each step scheduling the next, so no single request has to create
 * hundreds of pages.
 *
 * @return void
 */
function boi_glossary_sync() {
	$progress = (array) get_option( 'boi_glossary_progress', array() );

	if ( isset( $progress['version'], $progress['done'] ) && BOI_GLOSSARY_VERSION === $progress['version'] && ! empty( $progress['complete'] ) ) {
		return;
	}

	$next = boi_with_lock(
		'glossary',
		function () use ( $progress ) {
			$cursor = ( isset( $progress['version'] ) && BOI_GLOSSARY_VERSION === $progress['version'] ) ? (int) $progress['done'] : 0;
			$terms  = boi_glossary_terms();
			$author = function_exists( 'boi_seed_author' ) ? boi_seed_author() : 0;
			$parent = boi_glossary_parent();

			if ( ! $parent ) {
				$parent_id = boi_insert_post(
					array(
						'post_title'   => 'Glossary',
						'post_name'    => 'glossary',
						'post_content' => boi_glossary_parent_content(),
						'post_excerpt' => 'The words, people and places that come up most often when Islam is discussed: what they mean, how Muslims understand them, and what the sources say.',
						'post_status'  => 'publish',
						'post_author'  => $author,
						'post_type'    => 'page',
					)
				);
				$parent    = is_wp_error( $parent_id ) || ! $parent_id ? null : get_post( $parent_id );
			}

			if ( ! $parent ) {
				return false;
			}

			// A Glossary page that already existed gets the index block it lacks, once.
			if ( 'trash' !== $parent->post_status && false === strpos( $parent->post_content, 'bestofislam/glossary-index' ) ) {
				boi_update_post( array( 'ID' => $parent->ID, 'post_content' => rtrim( $parent->post_content ) . "\n\n<!-- wp:bestofislam/glossary-index /-->\n" ) );
			}

			$hashes = (array) get_option( 'boi_glossary_hashes', array() );

			if ( 0 === $cursor ) {
				boi_glossary_migrate_slugs( $hashes );
			}

			$batch = array_slice( $terms, $cursor, BOI_GLOSSARY_BATCH );

			$failed = isset( $progress['failed'] ) && BOI_GLOSSARY_VERSION === $progress['version'] ? (int) $progress['failed'] : 0;

			foreach ( $batch as $t ) {
				if ( ! boi_glossary_write_entry( $t, $parent, $author, $hashes ) ) {
					++$failed;
				}
			}

			$cursor += count( $batch );
			$passes  = isset( $progress['passes'] ) && BOI_GLOSSARY_VERSION === $progress['version'] ? (int) $progress['passes'] : 0;

			// Every entry was tried; if some failed, go round again (at most three times).
			if ( $cursor >= count( $terms ) && $failed && $passes < 3 ) {
				$cursor = 0;
				$failed = 0;
				++$passes;
			}

			update_option( 'boi_glossary_hashes', $hashes, false );
			update_option(
				'boi_glossary_progress',
				array(
					'version'  => BOI_GLOSSARY_VERSION,
					'done'     => $cursor,
					'failed'   => $failed,
					'passes'   => $passes,
					'complete' => $cursor >= count( $terms ) ? 1 : 0,
				),
				false
			);

			return $cursor < count( $terms );
		},
		true
	);

	if ( $next ) {
		if ( ! wp_next_scheduled( 'boi_glossary_continue' ) ) {
			wp_schedule_single_event( time() + 10, 'boi_glossary_continue' );
		}
	} else {
		delete_transient( 'boi_site_counts' );
	}
}
add_action( 'admin_init', 'boi_glossary_sync' );
add_action( 'after_switch_theme', 'boi_glossary_sync' );
add_action( 'boi_glossary_continue', 'boi_glossary_sync' );

/**
 * Fingerprint of what the theme writes for an entry: its content and excerpt.
 *
 * @param WP_Post $post Page.
 * @return string
 */
function boi_glossary_fingerprint( $post ) {
	return boi_content_fingerprint( $post->post_content . "\n" . $post->post_excerpt );
}

/**
 * Entry slugs that have changed, old => current. A slug ending in -2 is the
 * suffix WordPress adds on a collision and never belongs in an address;
 * entries that carried one were merged into the entry they duplicated or
 * given their own name. Verse numbers are kept out of addresses too.
 *
 * @return array
 */
function boi_glossary_retired_slugs() {
	return (array) apply_filters(
		'boi_glossary_retired_slugs',
		array(
			'abd-2'                            => 'abd',
			'ahmad-2'                          => 'ahmad',
			'bismi-allah-al-rahman-al-rahim-2' => 'bismi-allah-al-rahman-al-rahim',
			'hafiz-al-2'                       => 'hafeez-al',
			'mubarak-2'                        => 'mabruk',
			'domestic-discipline-quran-4-34'   => 'domestic-discipline',
		)
	);
}

/**
 * Brings pages written under a retired slug up to date: a renamed entry's
 * page keeps its identity and takes the new slug and title; a merged
 * duplicate is trashed once the entry it duplicated has its own page. The
 * old addresses are redirected by inc/legacy-permalinks.php.
 *
 * @param array $hashes Fingerprints by slug, updated in place.
 * @return void
 */
function boi_glossary_migrate_slugs( &$hashes ) {
	foreach ( boi_glossary_retired_slugs() as $old => $new ) {
		$page = get_page_by_path( 'glossary/' . $old, OBJECT, 'page' );

		if ( ! $page || 'trash' === $page->post_status ) {
			continue;
		}

		$current = get_page_by_path( 'glossary/' . $new, OBJECT, 'page' );

		if ( $current && 'trash' !== $current->post_status ) {
			wp_trash_post( $page->ID );
			unset( $hashes[ $old ] );
			continue;
		}

		$term = boi_glossary_term_by_slug( $new );

		boi_update_post(
			array(
				'ID'         => $page->ID,
				'post_name'  => $new,
				'post_title' => $term ? $term['title'] : $page->post_title,
			)
		);

		if ( isset( $hashes[ $old ] ) ) {
			$hashes[ $new ] = boi_glossary_fingerprint( get_post( $page->ID ) );
			unset( $hashes[ $old ] );
		}
	}
}

/**
 * Creates one entry, or refreshes it while it is exactly as the theme wrote it.
 *
 * @param array   $t      Entry.
 * @param WP_Post $parent Glossary page.
 * @param int     $author Author for new pages.
 * @param array   $hashes Recorded content fingerprints, updated in place.
 * @return bool False when the page could not be created.
 */
function boi_glossary_write_entry( $t, $parent, $author, &$hashes ) {
	$content = boi_glossary_content( $t );
	$page    = get_page_by_path( 'glossary/' . $t['slug'], OBJECT, 'page' );

	if ( $page && 'trash' === $page->post_status ) {
		return true; // The owner removed it; do not bring it back.
	}

	if ( ! $page ) {
		$id = boi_insert_post(
			array(
				'post_title'   => $t['title'],
				'post_name'    => $t['slug'],
				'post_parent'  => $parent->ID,
				'post_content' => $content,
				'post_excerpt' => $t['def'],
				'post_status'  => 'publish',
				'post_author'  => $author,
				'post_type'    => 'page',
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$hashes[ $t['slug'] ] = boi_glossary_fingerprint( get_post( $id ) );
			boi_glossary_meta( $id, $t );
			return true;
		}

		return false;
	}

	// Refresh only an entry still exactly as the theme last wrote it.
	$current = boi_glossary_fingerprint( $page );

	if ( isset( $hashes[ $t['slug'] ] ) && $hashes[ $t['slug'] ] === $current && boi_content_fingerprint( $content . "\n" . $t['def'] ) !== $current ) {
		boi_update_post(
			array(
				'ID'           => $page->ID,
				'post_content' => $content,
				'post_excerpt' => $t['def'],
			)
		);
		$hashes[ $t['slug'] ] = boi_glossary_fingerprint( get_post( $page->ID ) );
	}

	boi_glossary_meta( $page->ID, $t );

	return true;
}

/**
 * Keeps the index's data about an entry: its script, kind and other names.
 *
 * @param int   $id Page identifier.
 * @param array $t  Entry.
 * @return void
 */
function boi_glossary_meta( $id, $t ) {
	update_post_meta( $id, '_boi_term_ar', isset( $t['ar'] ) ? $t['ar'] : '' );
	update_post_meta( $id, '_boi_term_type', isset( $t['type'] ) ? $t['type'] : 'term' );
	update_post_meta( $id, '_boi_term_aliases', isset( $t['aliases'] ) ? array_values( (array) $t['aliases'] ) : array() );
}

/**
 * Registers the two dynamic blocks.
 *
 * @return void
 */
function boi_glossary_register_blocks() {
	register_block_type( 'bestofislam/glossary-index', array( 'render_callback' => 'boi_render_glossary_index' ) );
	register_block_type( 'bestofislam/glossary-bar', array( 'render_callback' => 'boi_render_glossary_bar' ) );
	register_block_type( 'bestofislam/term-hero', array( 'render_callback' => 'boi_render_term_hero' ) );
	register_block_type(
		'bestofislam/term-related',
		array(
			'render_callback' => 'boi_render_term_related',
			'attributes'      => array(
				'terms'    => array(
					'type'    => 'array',
					'default' => array(),
				),
				'articles' => array(
					'type'    => 'array',
					'default' => array(),
				),
			),
		)
	);
}
add_action( 'init', 'boi_glossary_register_blocks' );

/**
 * The glossary's entries, in title order.
 *
 * @return WP_Post[]
 */
function boi_glossary_entries() {
	$parent = boi_glossary_parent();

	if ( ! $parent ) {
		return array();
	}

	$exclude = function_exists( 'boi_unlist_enabled' ) && boi_unlist_enabled() ? boi_unlisted_ids() : array();

	return get_pages(
		array(
			'parent'       => $parent->ID,
			'hierarchical' => 0,
			'post_status'  => 'publish',
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
			'exclude'     => $exclude,
		)
	);
}

/**
 * The index letter for a title.
 *
 * @param string $title Title.
 * @return string A to Z, or #.
 */
function boi_glossary_letter( $title ) {
	$plain = strtoupper( substr( remove_accents( boi_glossary_sort_key( $title ) ), 0, 1 ) );

	return preg_match( '/^[A-Z]$/', $plain ) ? $plain : '#';
}

/**
 * A title with its leading apostrophe, ayn and hamza marks removed, so that
 * 'Abd and Abd are filed and sorted together.
 *
 * @param string $title Title.
 * @return string
 */
function boi_glossary_sort_key( $title ) {
	return preg_replace( '/^[\x{02BF}\x{02BE}\x{2018}\x{2019}\x{0027}`\s]+/u', '', wp_strip_all_tags( (string) $title ) );
}

/**
 * The index rows grouped by letter. An entry known by other names can also be
 * listed under each of them, which the search uses; the index itself shows
 * only the entries, with their other names beneath.
 *
 * @param bool $with_aliases Whether to include a row for each other name.
 * @return array[] Letter => rows; each row has name, entry and alias.
 */
function boi_glossary_groups( $with_aliases = false ) {
	static $cache = array();

	$key = $with_aliases ? 'all' : 'entries';

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$rows = array();

	foreach ( boi_glossary_entries() as $entry ) {
		$rows[] = array(
			'name'  => get_the_title( $entry ),
			'entry' => $entry,
			'alias' => false,
		);

		if ( ! $with_aliases ) {
			continue;
		}

		foreach ( boi_glossary_aliases( $entry ) as $alias ) {
			$rows[] = array(
				'name'  => $alias,
				'entry' => $entry,
				'alias' => true,
			);
		}
	}

	usort(
		$rows,
		function ( $a, $b ) {
			return strnatcasecmp( remove_accents( boi_glossary_sort_key( $a['name'] ) ), remove_accents( boi_glossary_sort_key( $b['name'] ) ) );
		}
	);

	$groups = array();

	foreach ( $rows as $row ) {
		$groups[ boi_glossary_letter( $row['name'] ) ][] = $row;
	}

	ksort( $groups );
	$cache[ $key ] = $groups;

	return $groups;
}

/**
 * The other names of an entry.
 *
 * @param WP_Post $entry Entry.
 * @return string[]
 */
function boi_glossary_aliases( $entry ) {
	$out = array();

	foreach ( (array) get_post_meta( $entry->ID, '_boi_term_aliases', true ) as $alias ) {
		$alias = trim( (string) $alias );

		if ( '' !== $alias ) {
			$out[] = $alias;
		}
	}

	return $out;
}

/**
 * The photographs used on the Glossary: one for the index and one for each
 * kind of entry. Credits live in inc/glossary-images.json.
 *
 * @return array Key (hero, person, prophet, place, event, group, book, topic, term) => image.
 */
function boi_glossary_images() {
	static $data = null;

	if ( null === $data ) {
		$raw  = file_exists( BOI_DIR . '/inc/glossary-images.json' ) ? file_get_contents( BOI_DIR . '/inc/glossary-images.json' ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$data = json_decode( (string) $raw, true );
		$data = is_array( $data ) ? $data : array();
	}

	return $data;
}

/**
 * One Glossary photograph with its address.
 *
 * @param string $key `hero`, or a kind of entry (its first photograph).
 * @return array|null
 */
function boi_glossary_image( $key ) {
	$all = boi_glossary_images();

	if ( 'hero' === $key ) {
		$image = isset( $all['hero'] ) ? $all['hero'] : null;
	} else {
		$image = ! empty( $all['kinds'][ $key ][0] ) ? $all['kinds'][ $key ][0] : null;
	}

	return $image ? boi_glossary_image_url( $image ) : null;
}

/**
 * The photograph for an entry: its own portrait when the entry carries one,
 * otherwise one of several stock photos for its kind, chosen from its address
 * so that an entry always shows the same one and neighbours differ.
 *
 * @param string $kind Kind of entry.
 * @param string $slug Entry slug.
 * @return array|null
 */
function boi_glossary_entry_image( $kind, $slug ) {
	$portrait = boi_glossary_entry_portrait( $slug );

	if ( $portrait ) {
		return boi_glossary_image_url( $portrait );
	}

	$all  = boi_glossary_images();
	$pool = ! empty( $all['kinds'][ $kind ] ) ? $all['kinds'][ $kind ] : ( isset( $all['kinds']['term'] ) ? $all['kinds']['term'] : array() );

	if ( empty( $pool ) ) {
		return null;
	}

	return boi_glossary_image_url( $pool[ (int) sprintf( '%u', crc32( $slug ) ) % count( $pool ) ] );
}

/**
 * An entry's own portrait, when its record in inc/glossary-data/ carries a
 * "portrait" field, rather than a photo drawn from the shared stock pool for
 * its kind. Used for named individuals whose own likeness is on file, with a
 * verified source and licence, rather than a generic photograph.
 *
 * @param string $slug Entry slug.
 * @return array|null Record shaped like a boi_glossary_images() pool entry
 *                     (file, alt, artist, licence, source, width, height), or
 *                     null when the entry has none.
 */
function boi_glossary_entry_portrait( $slug ) {
	$term = boi_glossary_term_by_slug( $slug );

	return ( $term && ! empty( $term['portrait'] ) && ! empty( $term['portrait']['file'] ) ) ? $term['portrait'] : null;
}

/**
 * An entry's raw data record, by slug.
 *
 * @param string $slug Entry slug.
 * @return array|null
 */
function boi_glossary_term_by_slug( $slug ) {
	static $by_slug = null;

	if ( null === $by_slug ) {
		$by_slug = array();
		foreach ( boi_glossary_terms() as $term ) {
			if ( ! empty( $term['slug'] ) ) {
				$by_slug[ $term['slug'] ] = $term;
			}
		}
	}

	return isset( $by_slug[ $slug ] ) ? $by_slug[ $slug ] : null;
}

/**
 * The Arabic epithet that follows a name: the blessing on the Prophet, the
 * peace on the other prophets, the "may God be pleased with" on the
 * Companions. An entry carries it in its "epithet" field; "epithet_rubric"
 * sets it in red ink, after the manuscript convention.
 *
 * @param array|null $t    Entry.
 * @param bool       $lead Whether to lead with a non-breaking space, so the
 *                         epithet stays on the same line as the name.
 * @return string Markup, or '' when the entry has no epithet.
 */
function boi_glossary_epithet_html( $t, $lead = true ) {
	if ( ! is_array( $t ) || empty( $t['epithet'] ) || ! is_string( $t['epithet'] ) ) {
		return '';
	}

	$class = 'boi-epithet' . ( ! empty( $t['epithet_rubric'] ) ? ' boi-epithet--rubric' : '' );

	return ( $lead ? '&nbsp;' : '' ) . '<span class="' . esc_attr( $class ) . '" lang="ar" dir="rtl">' . esc_html( $t['epithet'] ) . '</span>';
}

/**
 * Adds the address to a photograph's record.
 *
 * @param array $image Record from inc/glossary-images.json.
 * @return array|null
 */
function boi_glossary_image_url( $image ) {
	if ( empty( $image['file'] ) ) {
		return null;
	}

	$image['url'] = BOI_URI . '/assets/images/glossary/' . rawurlencode( $image['file'] );

	return $image;
}

/**
 * A photograph's credit line.
 *
 * @param array $image Image from boi_glossary_image().
 * @return string
 */
function boi_glossary_credit( $image ) {
	return sprintf(
		/* translators: 1: photographer, 2: licence name. */
		esc_html__( 'Photo: %1$s, %2$s', 'bestofislam' ),
		'<a href="' . esc_url( $image['source'] ) . '" rel="noopener">' . esc_html( $image['artist'] ) . '</a>',
		esc_html( $image['licence'] )
	);
}

/**
 * The anchor identifier for a letter group.
 *
 * @param string $letter A to Z, or #.
 * @return string
 */
function boi_glossary_letter_id( $letter ) {
	return 'letter-' . ( '#' === $letter ? 'other' : strtolower( $letter ) );
}

/**
 * Renders the index: a photograph, tiles for each kind of entry that filter
 * the list, then the entries as cards, a group for each letter. The tiles are
 * enabled by assets/js/glossary.js; without it the page is the plain list and
 * the letter links in the bar are anchors.
 *
 * @return string
 */
function boi_render_glossary_index() {
	$groups = boi_glossary_groups();

	if ( empty( $groups ) ) {
		return '';
	}

	$types  = boi_glossary_types();
	$counts = array();
	$total  = 0;
	$body   = '';

	// Two parts, each with its own A to Z: terms first, then people.
	$people_types = array( 'person', 'prophet' );
	$parts        = array( 'terms' => array(), 'people' => array() );

	foreach ( $groups as $letter => $items ) {
		foreach ( $items as $row ) {
			$t = (string) get_post_meta( $row['entry']->ID, '_boi_term_type', true );
			$parts[ in_array( $t, $people_types, true ) ? 'people' : 'terms' ][ $letter ][] = $row;
		}
	}

	$part_titles = array(
		'terms'  => __( 'Terms', 'bestofislam' ),
		'people' => __( 'People', 'bestofislam' ),
	);

	foreach ( $parts as $part => $part_groups ) {
		if ( ! $part_groups ) {
			continue;
		}

		$body .= boi_add_heading_icon( sprintf( '<h2 class="boi-glossary__part" id="glossary-%1$s">%2$s</h2>', esc_attr( $part ), esc_html( $part_titles[ $part ] ) ) );

	foreach ( $part_groups as $letter => $items ) {
		$letter_id = boi_glossary_letter_id( $letter );
		$id        = 'terms' === $part ? $letter_id : 'people-' . $letter_id;
		$list      = '';
		$n         = 0;

		foreach ( $items as $row ) {
			$entry = $row['entry'];
			$type  = (string) get_post_meta( $entry->ID, '_boi_term_type', true );
			$type  = isset( $types[ $type ] ) ? $type : 'term';

			++$n;
			++$total;
			$counts[ $type ] = isset( $counts[ $type ] ) ? $counts[ $type ] + 1 : 1;

			$ar      = (string) get_post_meta( $entry->ID, '_boi_term_ar', true );
			$kind    = 'term' !== $type ? '<span class="boi-glossary__type">' . esc_html( $types[ $type ] ) . '</span>' : '';
			$aliases = array_slice( boi_glossary_aliases( $entry ), 0, 3 );
			$aka     = $aliases ? '<span class="boi-glossary__aka">' . esc_html( sprintf( /* translators: %s: other names. */ __( 'Also: %s', 'bestofislam' ), implode( ', ', $aliases ) ) ) . '</span>' : '';

			$thumb = boi_glossary_entry_image( $type, $entry->post_name );
			$img   = $thumb ? '<img class="boi-glossary__thumb" src="' . esc_url( $thumb['url'] ) . '" alt="" width="' . (int) $thumb['width'] . '" height="' . (int) $thumb['height'] . '" loading="lazy" decoding="async">' : '';

			$long  = mb_strlen( $ar ) > 14 ? ' has-long-ar' : '';
			$list .= sprintf(
				'<li class="boi-glossary__item' . $long . '" data-type="%1$s">' . $img . '%2$s<a class="boi-glossary__term" href="%3$s">%4$s</a>%5$s%6$s<p class="boi-glossary__def">%7$s</p></li>',
				esc_attr( $type ),
				$kind,
				esc_url( get_permalink( $entry ) ),
				esc_html( get_the_title( $entry ) ),
				'' !== $ar ? '<span class="boi-glossary__ar" lang="ar" dir="rtl">' . esc_html( $ar ) . '</span>' : '',
				$aka,
				wp_kses( $entry->post_excerpt, array( 'em' => array() ) )
			);
		}

		$body .= sprintf(
			'<section class="boi-glossary__group" id="%1$s" data-letter="%6$s" aria-labelledby="%1$s-h"><h3 class="boi-glossary__letter" id="%1$s-h">%2$s<span class="boi-glossary__n">%3$s</span><a class="boi-glossary__top" href="#top">%4$s</a></h3><ul class="boi-glossary__list">%5$s</ul></section>',
			esc_attr( $id ),
			esc_html( $letter ),
			esc_html( number_format_i18n( $n ) ),
			esc_html__( 'Back to top', 'bestofislam' ),
			$list,
			esc_attr( $letter_id )
		);
	}
	}

	$tile = function ( $slug, $label, $count, $image, $active ) {
		return sprintf(
			'<button type="button" class="boi-chip boi-kind%1$s" data-type="%2$s" aria-pressed="%3$s">%4$s<span class="boi-kind__label">%5$s</span><span class="boi-kind__n">%6$s</span></button>',
			$active ? ' is-active' : '',
			esc_attr( $slug ),
			$active ? 'true' : 'false',
			$image ? '<img class="boi-kind__img" src="' . esc_url( $image['url'] ) . '" alt="" width="' . (int) $image['width'] . '" height="' . (int) $image['height'] . '" loading="lazy" decoding="async">' : '',
			esc_html( $label ),
			esc_html( number_format_i18n( $count ) )
		);
	};

	$tiles = $tile( '', __( 'All', 'bestofislam' ), $total, boi_glossary_image( 'hero' ), true );

	foreach ( $types as $slug => $label ) {
		if ( ! empty( $counts[ $slug ] ) ) {
			$tiles .= $tile( $slug, $label, $counts[ $slug ], boi_glossary_image( $slug ), false );
		}
	}

	$hero  = boi_glossary_image( 'hero' );
	$intro = '';

	if ( $hero ) {
		$intro .= '<figure class="boi-glossary__hero"><img src="' . esc_url( $hero['url'] ) . '" alt="' . esc_attr( $hero['alt'] ) . '" width="' . (int) $hero['width'] . '" height="' . (int) $hero['height'] . '" decoding="async"><figcaption>' . boi_glossary_credit( $hero ) . '</figcaption></figure>';
	}

	$intro .= '<p class="boi-glossary__basis">' . sprintf(
		/* translators: %s: the scholar's name, linked to his entry. */
		esc_html__( 'The terms and their Arabic forms follow the lists in %s\'s Toward Islamic English. Each definition is restated here, with the source of the term given on its page.', 'bestofislam' ),
		boi_glossary_faruqi_name() // phpcs:ignore WordPress.Security.EscapeOutput
	) . '</p>';

	return sprintf(
		'%1$s<div class="boi-glossary" data-total="%2$d"><div class="boi-glossary__tools" hidden><div class="boi-glossary__types" role="group" aria-label="%3$s">%4$s</div><p class="boi-glossary__count" role="status" aria-live="polite"></p><button type="button" class="boi-glossary__reset" hidden>%5$s</button></div>%6$s<p class="boi-glossary__empty" hidden>%7$s</p></div>',
		$intro, // phpcs:ignore WordPress.Security.EscapeOutput
		(int) $total,
		esc_attr__( 'Filter by kind', 'bestofislam' ),
		$tiles, // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html__( 'Show every letter', 'bestofislam' ),
		$body, // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html__( 'Nothing matches. Try another spelling, or clear the filters.', 'bestofislam' )
	);
}

/**
 * Renders the Glossary bar shown on the Glossary and every entry: a way back
 * to the index, a search box that finds a term as you type, and the letters.
 * The terms for the search are printed once as JSON.
 *
 * @return string
 */
function boi_render_glossary_bar() {
	$groups = boi_glossary_groups();

	if ( empty( $groups ) ) {
		return '';
	}

	$types = boi_glossary_types();
	$base  = home_url( '/glossary/' );
	$az    = '';
	$data  = array();

	foreach ( $groups as $letter => $items ) {
		$id  = boi_glossary_letter_id( $letter );
		$n   = count( $items );
		$az .= sprintf( '<li><a href="%1$s" data-letter="%2$s" title="%3$s">%4$s</a></li>', esc_url( $base . '#' . $id ), esc_attr( $id ), esc_attr( sprintf( /* translators: %d: number of entries. */ _n( '%d entry', '%d entries', $n, 'bestofislam' ), $n ) ), esc_html( $letter ) );
	}

	foreach ( boi_glossary_groups( true ) as $items ) {
		foreach ( $items as $row ) {
			$entry  = $row['entry'];
			$type   = (string) get_post_meta( $entry->ID, '_boi_term_type', true );
			$data[] = array(
				'n' => wp_strip_all_tags( $row['name'] ),
				'u' => get_permalink( $entry ),
				't' => ( isset( $types[ $type ] ) && 'term' !== $type ) ? $types[ $type ] : '',
				's' => $row['alias'] ? wp_strip_all_tags( get_the_title( $entry ) ) : '',
			);
		}
	}

	return sprintf(
		'<div class="boi-gbar" data-index="%1$s"><div class="boi-gbar__inner"><a class="boi-gbar__brand" href="%1$s">%2$s</a><div class="boi-gbar__search" role="search"><label class="screen-reader-text" for="boi-gq">%3$s</label><input id="boi-gq" type="search" placeholder="%3$s" autocomplete="off" spellcheck="false" aria-controls="boi-gresults" aria-expanded="false"><ul id="boi-gresults" class="boi-gbar__results" role="listbox" hidden></ul></div><nav class="boi-gbar__az" aria-label="%4$s"><ul>%5$s</ul></nav></div><script type="application/json" id="boi-gterms">%6$s</script></div>',
		esc_url( $base ),
		esc_html__( 'Glossary', 'bestofislam' ),
		esc_attr__( 'Find a term', 'bestofislam' ),
		esc_attr__( 'Jump to a letter', 'bestofislam' ),
		$az,
		wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
	);
}

/**
 * Renders the photograph at the head of an entry, chosen by its kind.
 *
 * @return string
 */
function boi_render_term_hero() {
	$post = get_queried_object();

	if ( ! $post instanceof WP_Post || ! $post->post_parent || ! boi_is_glossary_page() ) {
		return '';
	}

	$type  = (string) get_post_meta( $post->ID, '_boi_term_type', true );
	$image = boi_glossary_entry_image( isset( boi_glossary_types()[ $type ] ) ? $type : 'term', $post->post_name );

	if ( ! $image ) {
		return '';
	}

	return '<figure class="boi-term-hero alignwide"><img src="' . esc_url( $image['url'] ) . '" alt="" width="' . (int) $image['width'] . '" height="' . (int) $image['height'] . '" decoding="async"><figcaption>' . boi_glossary_credit( $image ) . '</figcaption></figure>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * The entries either side of this one in the index, for the pager.
 *
 * @param int $id Page identifier.
 * @return string
 */
function boi_glossary_pager( $id ) {
	$entries = boi_glossary_entries();
	$at      = null;

	foreach ( $entries as $i => $entry ) {
		if ( (int) $entry->ID === (int) $id ) {
			$at = $i;
			break;
		}
	}

	if ( null === $at ) {
		return '';
	}

	$link = function ( $entry, $dir, $label ) {
		return sprintf(
			'<a class="boi-term__%1$s" href="%2$s" rel="%1$s"><span>%3$s</span>%4$s</a>',
			esc_attr( $dir ),
			esc_url( get_permalink( $entry ) ),
			esc_html( $label ),
			esc_html( get_the_title( $entry ) )
		);
	};

	$prev = isset( $entries[ $at - 1 ] ) ? $link( $entries[ $at - 1 ], 'prev', __( 'Previous term', 'bestofislam' ) ) : '<span></span>';
	$next = isset( $entries[ $at + 1 ] ) ? $link( $entries[ $at + 1 ], 'next', __( 'Next term', 'bestofislam' ) ) : '<span></span>';

	return '<nav class="boi-term__pager" aria-label="' . esc_attr__( 'Neighbouring terms', 'bestofislam' ) . '">' . $prev . $next . '</nav>';
}

/**
 * Renders the related terms and articles at the foot of an entry.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function boi_render_term_related( $attributes ) {
	$terms = '';

	foreach ( (array) $attributes['terms'] as $slug ) {
		$page = get_page_by_path( 'glossary/' . sanitize_title( $slug ), OBJECT, 'page' );

		if ( $page && 'publish' === $page->post_status ) {
			$terms .= sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink( $page ) ), esc_html( get_the_title( $page ) ) );
		}
	}

	$articles = '';

	foreach ( (array) $attributes['articles'] as $slug ) {
		$post = get_page_by_path( sanitize_title( $slug ), OBJECT, 'post' );

		if ( $post && 'publish' === $post->post_status && ( ! function_exists( 'boi_is_unlisted' ) || ! boi_is_unlisted( $post ) ) ) {
			$articles .= sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink( $post ) ), esc_html( get_the_title( $post ) ) );
		}
	}

	$pager = boi_glossary_pager( get_queried_object_id() );

	if ( '' === $terms && '' === $articles ) {
		return $pager;
	}

	$html = '<aside class="boi-term__related">' . boi_add_heading_icon( '<h2 class="boi-term__label">' . esc_html__( 'Related', 'bestofislam' ) . '</h2>' );

	if ( '' !== $terms ) {
		$html .= '<div class="boi-term__group boi-term__group--terms">' . boi_add_heading_icon( '<h3>' . esc_html__( 'Terms', 'bestofislam' ) . '</h3>' ) . '<ul>' . $terms . '</ul></div>';
	}

	if ( '' !== $articles ) {
		$html .= '<div class="boi-term__group boi-term__group--reads">' . boi_add_heading_icon( '<h3>' . esc_html__( 'Read more', 'bestofislam' ) . '</h3>' ) . '<ul>' . $articles . '</ul></div>';
	}

	return $html . '<p class="boi-term__back"><a href="' . esc_url( home_url( '/glossary/' ) ) . '">' . esc_html__( 'All terms', 'bestofislam' ) . '</a><a href="#top">' . esc_html__( 'Back to top', 'bestofislam' ) . '</a></p></aside>' . $pager;
}

/**
 * Structured data for the Glossary. The Glossary is a DefinedTermSet and each
 * entry a DefinedTerm with its other names, its Arabic script and the terms
 * and articles it points to. Under the theme's own graph the pages also get a
 * CollectionPage (the index) or a WebPage (an entry); with Rank Math's schema
 * active that page node is Rank Math's, and these nodes refer to it by id. The
 * breadcrumb trail comes from the breadcrumbs block.
 *
 * @return void
 */
function boi_glossary_schema() {
	if ( ! boi_is_glossary_page() ) {
		return;
	}

	$post   = get_queried_object();
	$parent = boi_glossary_parent();

	if ( ! $parent ) {
		return;
	}

	$set_url = get_permalink( $parent );
	$set_id  = $set_url . '#glossary';
	$set_ref = array(
		'@type' => 'DefinedTermSet',
		'@id'   => $set_id,
		'name'  => wp_strip_all_tags( get_the_title( $parent ) ),
		'url'   => $set_url,
	);
	$page_id = get_permalink( $post ) . '#webpage';
	$graph   = array();

	if ( $post->ID === $parent->ID ) {
		$terms = array();

		foreach ( boi_glossary_entries() as $entry ) {
			$terms[] = array(
				'@type'       => 'DefinedTerm',
				'@id'         => get_permalink( $entry ) . '#term',
				'name'        => wp_strip_all_tags( get_the_title( $entry ) ),
				'description' => wp_strip_all_tags( $entry->post_excerpt ),
				'url'         => get_permalink( $entry ),
			);
		}

		$graph[] = $set_ref + array(
			'description'    => wp_strip_all_tags( $parent->post_excerpt ),
			'inLanguage'     => get_bloginfo( 'language' ),
			'hasDefinedTerm' => $terms,
			'mainEntityOfPage' => array( '@id' => $page_id ),
		);

		if ( ! boi_glossary_rank_math() ) {
			$graph[] = array(
				'@type'      => 'CollectionPage',
				'@id'        => $page_id,
				'url'        => $set_url,
				'name'       => wp_strip_all_tags( get_the_title( $parent ) ),
				'isPartOf'   => array( '@id' => boi_entity_id( 'website' ) ),
				'inLanguage' => get_bloginfo( 'language' ),
				'mainEntity' => array( '@id' => $set_id ),
			);
		}
	} else {
		$url     = get_permalink( $post );
		$aliases = array_values( array_filter( array_map( 'strval', (array) get_post_meta( $post->ID, '_boi_term_aliases', true ) ) ) );
		$ar      = (string) get_post_meta( $post->ID, '_boi_term_ar', true );

		if ( '' !== $ar ) {
			$aliases[] = $ar;
		}

		$related = array();

		if ( preg_match( '/<!-- wp:bestofislam\/term-related (\{.*?\}) \/-->/s', $post->post_content, $m ) ) {
			$attrs = json_decode( $m[1], true );

			foreach ( (array) ( isset( $attrs['terms'] ) ? $attrs['terms'] : array() ) as $slug ) {
				$rel = get_page_by_path( 'glossary/' . sanitize_title( $slug ), OBJECT, 'page' );

				if ( $rel && 'publish' === $rel->post_status ) {
					$related[] = get_permalink( $rel );
				}
			}

			foreach ( (array) ( isset( $attrs['articles'] ) ? $attrs['articles'] : array() ) as $slug ) {
				$rel = get_page_by_path( sanitize_title( $slug ), OBJECT, 'post' );

				if ( $rel && 'publish' === $rel->post_status && ( ! function_exists( 'boi_is_unlisted' ) || ! boi_is_unlisted( $rel ) ) ) {
					$related[] = get_permalink( $rel );
				}
			}
		}

		$term = array(
			'@type'            => 'DefinedTerm',
			'@id'              => $url . '#term',
			'name'             => wp_strip_all_tags( get_the_title( $post ) ),
			'description'      => wp_strip_all_tags( $post->post_excerpt ),
			'url'              => $url,
			'termCode'         => $post->post_name,
			'inDefinedTermSet' => array( '@id' => $set_id ),
			'mainEntityOfPage' => array( '@id' => $page_id ),
		);

		if ( $aliases ) {
			$term['alternateName'] = $aliases;
		}

		$graph[] = $term;
		$graph[] = $set_ref;

		if ( ! boi_glossary_rank_math() ) {
			$page = array(
				'@type'      => 'WebPage',
				'@id'        => $page_id,
				'url'        => $url,
				'name'       => wp_strip_all_tags( get_the_title( $post ) ),
				'isPartOf'   => array( '@id' => $set_id ),
				'inLanguage' => get_bloginfo( 'language' ),
				'mainEntity' => array( '@id' => $url . '#term' ),
			);

			if ( $related ) {
				$page['relatedLink'] = $related;
			}

			$graph[] = $page;
		}
	}

	echo "\n<script type=\"application/ld+json\">" . boi_json_ld(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		)
	) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'wp_head', 'boi_glossary_schema', 22 );

/**
 * Whether Rank Math's schema is active, so it supplies the page node.
 *
 * @return bool
 */
function boi_glossary_rank_math() {
	return function_exists( 'boi_rank_math_schema_active' ) && boi_rank_math_schema_active();
}

/**
 * Marks the body on the Glossary page (the wide index) and on its entries.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function boi_glossary_body_class( $classes ) {
	if ( boi_is_glossary_page() ) {
		$classes[] = get_queried_object()->post_parent ? 'boi-glossary-entry' : 'boi-glossary-index';
	}

	return $classes;
}
add_filter( 'body_class', 'boi_glossary_body_class' );

/**
 * Entries use the Glossary template, with its bar, like the Glossary itself.
 *
 * @param string[] $templates Template hierarchy.
 * @return string[]
 */
function boi_glossary_template_hierarchy( $templates ) {
	if ( boi_is_glossary_page() ) {
		array_unshift( $templates, 'page-glossary' );
	}

	return $templates;
}
add_filter( 'page_template_hierarchy', 'boi_glossary_template_hierarchy' );
