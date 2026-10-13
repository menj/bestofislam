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

const BOI_GLOSSARY_VERSION = '3';

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
		$out .= '<p class="boi-term__script" lang="ar" dir="rtl">' . $t['ar'] . '</p>';
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
		$out .= '<p class="boi-term__label">Often misunderstood as</p>';
		$out .= '<p>' . $t['misread'] . '</p>';
		$out .= "</div>\n<!-- /wp:group -->\n\n";
	}

	if ( ! empty( $t['sources'] ) ) {
		$out .= $h( 'What the sources say' );
		foreach ( $t['sources'] as $para ) {
			$out .= $p( $para );
		}
	}

	$attrs = wp_json_encode(
		array(
			'terms'    => array_values( isset( $t['terms'] ) ? $t['terms'] : array() ),
			'articles' => array_values( isset( $t['articles'] ) ? $t['articles'] : array() ),
		)
	);

	return $out . '<!-- wp:bestofislam/term-related ' . $attrs . " /-->\n";
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
			$batch  = array_slice( $terms, $cursor, BOI_GLOSSARY_BATCH );

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
	$plain = strtoupper( substr( remove_accents( wp_strip_all_tags( $title ) ), 0, 1 ) );

	return preg_match( '/^[A-Z]$/', $plain ) ? $plain : '#';
}

/**
 * The index rows grouped by letter. An entry known by other names also appears
 * under each of them, as a short line pointing to the entry.
 *
 * @return array[] Letter => rows; each row has name, entry and alias.
 */
function boi_glossary_groups() {
	static $groups = null;

	if ( null !== $groups ) {
		return $groups;
	}

	$rows = array();

	foreach ( boi_glossary_entries() as $entry ) {
		$rows[] = array(
			'name'  => get_the_title( $entry ),
			'entry' => $entry,
			'alias' => false,
		);

		foreach ( (array) get_post_meta( $entry->ID, '_boi_term_aliases', true ) as $alias ) {
			if ( '' !== trim( (string) $alias ) ) {
				$rows[] = array(
					'name'  => (string) $alias,
					'entry' => $entry,
					'alias' => true,
				);
			}
		}
	}

	usort(
		$rows,
		function ( $a, $b ) {
			return strnatcasecmp( remove_accents( wp_strip_all_tags( $a['name'] ) ), remove_accents( wp_strip_all_tags( $b['name'] ) ) );
		}
	);

	$groups = array();

	foreach ( $rows as $row ) {
		$groups[ boi_glossary_letter( $row['name'] ) ][] = $row;
	}

	ksort( $groups );

	return $groups;
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
 * Renders the index: filters by kind, then the entries as cards, a group for
 * each letter. The filters are enabled by assets/js/glossary.js; without it
 * the page is the plain list and the letter links in the bar are anchors.
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

	foreach ( $groups as $letter => $items ) {
		$id   = boi_glossary_letter_id( $letter );
		$list = '';
		$n    = 0;

		foreach ( $items as $row ) {
			$entry = $row['entry'];
			$type  = (string) get_post_meta( $entry->ID, '_boi_term_type', true );
			$type  = isset( $types[ $type ] ) ? $type : 'term';

			if ( $row['alias'] ) {
				$list .= sprintf(
					'<li class="boi-glossary__item is-alias" data-type="%5$s"><span class="boi-glossary__name">%1$s</span><span class="boi-glossary__see">%2$s <a href="%3$s">%4$s</a></span></li>',
					esc_html( $row['name'] ),
					esc_html__( 'see', 'bestofislam' ),
					esc_url( get_permalink( $entry ) ),
					esc_html( get_the_title( $entry ) ),
					esc_attr( $type )
				);
				continue;
			}

			++$n;
			++$total;
			$counts[ $type ] = isset( $counts[ $type ] ) ? $counts[ $type ] + 1 : 1;

			$ar  = (string) get_post_meta( $entry->ID, '_boi_term_ar', true );
			$tag = 'term' !== $type ? '<span class="boi-glossary__type">' . esc_html( $types[ $type ] ) . '</span>' : '';

			$list .= sprintf(
				'<li class="boi-glossary__item" data-type="%6$s"><a class="boi-glossary__term" href="%1$s">%2$s</a>%3$s%4$s<p class="boi-glossary__def">%5$s</p></li>',
				esc_url( get_permalink( $entry ) ),
				esc_html( get_the_title( $entry ) ),
				$tag,
				'' !== $ar ? '<span class="boi-glossary__ar" lang="ar" dir="rtl">' . esc_html( $ar ) . '</span>' : '',
				wp_kses( $entry->post_excerpt, array( 'em' => array() ) ),
				esc_attr( $type )
			);
		}

		$body .= sprintf(
			'<section class="boi-glossary__group" id="%1$s" aria-labelledby="%1$s-h"><h2 class="boi-glossary__letter" id="%1$s-h">%2$s<span class="boi-glossary__n">%3$s</span><a class="boi-glossary__top" href="#top">%4$s</a></h2><ul class="boi-glossary__list">%5$s</ul></section>',
			esc_attr( $id ),
			esc_html( $letter ),
			esc_html( number_format_i18n( $n ) ),
			esc_html__( 'Back to top', 'bestofislam' ),
			$list
		);
	}

	$chips = sprintf( '<button type="button" class="boi-chip is-active" data-type="" aria-pressed="true">%1$s <span>%2$s</span></button>', esc_html__( 'All', 'bestofislam' ), esc_html( number_format_i18n( $total ) ) );

	foreach ( $types as $slug => $label ) {
		if ( ! empty( $counts[ $slug ] ) ) {
			$chips .= sprintf( '<button type="button" class="boi-chip" data-type="%1$s" aria-pressed="false">%2$s <span>%3$s</span></button>', esc_attr( $slug ), esc_html( $label ), esc_html( number_format_i18n( $counts[ $slug ] ) ) );
		}
	}

	return sprintf(
		'<div class="boi-glossary" data-total="%1$d"><div class="boi-glossary__tools" hidden><div class="boi-glossary__types" role="group" aria-label="%2$s">%3$s</div><p class="boi-glossary__count" role="status" aria-live="polite"></p><button type="button" class="boi-glossary__reset" hidden>%4$s</button></div>%5$s<p class="boi-glossary__empty" hidden>%6$s</p></div>',
		(int) $total,
		esc_attr__( 'Filter by kind', 'bestofislam' ),
		$chips,
		esc_html__( 'Show every letter', 'bestofislam' ),
		$body,
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

		foreach ( $items as $row ) {
			$entry = $row['entry'];
			$type  = (string) get_post_meta( $entry->ID, '_boi_term_type', true );
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

	$html = '<aside class="boi-term__related"><h2 class="boi-term__label">' . esc_html__( 'Related', 'bestofislam' ) . '</h2>';

	if ( '' !== $terms ) {
		$html .= '<div><h3>' . esc_html__( 'Terms', 'bestofislam' ) . '</h3><ul>' . $terms . '</ul></div>';
	}

	if ( '' !== $articles ) {
		$html .= '<div><h3>' . esc_html__( 'Read more', 'bestofislam' ) . '</h3><ul>' . $articles . '</ul></div>';
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
