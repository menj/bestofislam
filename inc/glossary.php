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

const BOI_GLOSSARY_VERSION = '1';

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

	$out  = "<!-- wp:group {\"className\":\"boi-term\"} -->\n<div class=\"wp-block-group boi-term\">";
	$out .= '<p class="boi-term__script" lang="ar" dir="rtl">' . $t['ar'] . '</p>';
	$out .= '<p class="boi-term__translit">' . $t['tr'] . '</p>';
	$out .= "</div>\n<!-- /wp:group -->\n\n";

	$out .= $p( $t['def'], 'boi-term__def' );

	$out .= $h( 'What it means' );
	foreach ( $t['what'] as $para ) {
		$out .= $p( $para );
	}

	$out .= "<!-- wp:group {\"className\":\"boi-term__misread\"} -->\n<div class=\"wp-block-group boi-term__misread\">";
	$out .= '<p class="boi-term__label">Often misunderstood as</p>';
	$out .= '<p>' . $t['misread'] . '</p>';
	$out .= "</div>\n<!-- /wp:group -->\n\n";

	$out .= $h( 'What the sources say' );
	foreach ( $t['sources'] as $para ) {
		$out .= $p( $para );
	}

	$attrs = wp_json_encode(
		array(
			'terms'    => array_values( $t['terms'] ),
			'articles' => array_values( $t['articles'] ),
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
 * Creates the Glossary and its first entries, and refreshes entries the owner
 * has not edited. Runs once per glossary version.
 *
 * @return void
 */
function boi_glossary_sync() {
	if ( get_option( 'boi_glossary_version' ) === BOI_GLOSSARY_VERSION ) {
		return;
	}

	boi_with_lock(
		'glossary',
		function () {
			$author = function_exists( 'boi_seed_author' ) ? boi_seed_author() : 0;
			$parent = boi_glossary_parent();

			if ( ! $parent ) {
				$parent_id = boi_insert_post(
					array(
						'post_title'   => 'Glossary',
						'post_name'    => 'glossary',
						'post_content' => boi_glossary_parent_content(),
						'post_excerpt' => 'The words that come up most often when Islam is discussed: what they mean, how Muslims understand them, and what the sources say.',
						'post_status'  => 'publish',
						'post_author'  => $author,
						'post_type'    => 'page',
					)
				);
				$parent    = is_wp_error( $parent_id ) || ! $parent_id ? null : get_post( $parent_id );
			}

			if ( ! $parent ) {
				return;
			}

			$hashes = (array) get_option( 'boi_glossary_hashes', array() );

			foreach ( boi_glossary_terms() as $t ) {
				$content = boi_glossary_content( $t );
				$page    = get_page_by_path( 'glossary/' . $t['slug'], OBJECT, 'page' );

				if ( $page && 'trash' === $page->post_status ) {
					continue; // The owner removed it; do not bring it back.
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
						$hashes[ $t['slug'] ] = boi_content_fingerprint( get_post( $id )->post_content );
						update_post_meta( $id, '_boi_term_ar', $t['ar'] );
					}
					continue;
				}

				// Refresh only an entry still exactly as the theme last wrote it.
				$current = boi_content_fingerprint( $page->post_content );

				if ( isset( $hashes[ $t['slug'] ] ) && $hashes[ $t['slug'] ] === $current && boi_content_fingerprint( $content ) !== $current ) {
					boi_update_post(
						array(
							'ID'           => $page->ID,
							'post_content' => $content,
							'post_excerpt' => $t['def'],
						)
					);
					$hashes[ $t['slug'] ] = boi_content_fingerprint( get_post( $page->ID )->post_content );
				}

				if ( '' === (string) get_post_meta( $page->ID, '_boi_term_ar', true ) ) {
					update_post_meta( $page->ID, '_boi_term_ar', $t['ar'] );
				}
			}

			update_option( 'boi_glossary_hashes', $hashes, false );
			update_option( 'boi_glossary_version', BOI_GLOSSARY_VERSION, false );
			delete_transient( 'boi_site_counts' );
		},
		true
	);
}
add_action( 'admin_init', 'boi_glossary_sync' );
add_action( 'after_switch_theme', 'boi_glossary_sync' );

/**
 * Registers the two dynamic blocks.
 *
 * @return void
 */
function boi_glossary_register_blocks() {
	register_block_type( 'bestofislam/glossary-index', array( 'render_callback' => 'boi_render_glossary_index' ) );
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
			'child_of'    => $parent->ID,
			'post_status' => 'publish',
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
 * Renders the A to Z index.
 *
 * @return string
 */
function boi_render_glossary_index() {
	$entries = boi_glossary_entries();

	if ( empty( $entries ) ) {
		return '';
	}

	$groups = array();

	foreach ( $entries as $entry ) {
		$groups[ boi_glossary_letter( $entry->post_title ) ][] = $entry;
	}

	ksort( $groups );

	$nav  = '';
	$body = '';

	foreach ( $groups as $letter => $items ) {
		$id   = 'letter-' . ( '#' === $letter ? 'other' : strtolower( $letter ) );
		$nav .= sprintf( '<li><a href="#%1$s">%2$s</a></li>', esc_attr( $id ), esc_html( $letter ) );

		$list = '';
		foreach ( $items as $entry ) {
			$ar    = (string) get_post_meta( $entry->ID, '_boi_term_ar', true );
			$list .= sprintf(
				'<li class="boi-glossary__item"><a class="boi-glossary__term" href="%1$s">%2$s</a>%3$s<p class="boi-glossary__def">%4$s</p></li>',
				esc_url( get_permalink( $entry ) ),
				esc_html( get_the_title( $entry ) ),
				'' !== $ar ? '<span class="boi-glossary__ar" lang="ar" dir="rtl">' . esc_html( $ar ) . '</span>' : '',
				esc_html( $entry->post_excerpt )
			);
		}

		$body .= sprintf(
			'<section class="boi-glossary__group" id="%1$s" aria-labelledby="%1$s-h"><h2 class="boi-glossary__letter" id="%1$s-h">%2$s</h2><ul class="boi-glossary__list">%3$s</ul></section>',
			esc_attr( $id ),
			esc_html( $letter ),
			$list
		);
	}

	return sprintf(
		'<div class="boi-glossary"><nav class="boi-glossary__nav" aria-label="%1$s"><ul>%2$s</ul></nav>%3$s</div>',
		esc_attr__( 'Jump to a letter', 'bestofislam' ),
		$nav,
		$body
	);
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

	if ( '' === $terms && '' === $articles ) {
		return '';
	}

	$html = '<aside class="boi-term__related"><h2 class="boi-term__label">' . esc_html__( 'Related', 'bestofislam' ) . '</h2>';

	if ( '' !== $terms ) {
		$html .= '<div><h3>' . esc_html__( 'Terms', 'bestofislam' ) . '</h3><ul>' . $terms . '</ul></div>';
	}

	if ( '' !== $articles ) {
		$html .= '<div><h3>' . esc_html__( 'Read more', 'bestofislam' ) . '</h3><ul>' . $articles . '</ul></div>';
	}

	return $html . '<p class="boi-term__back"><a href="' . esc_url( home_url( '/glossary/' ) ) . '">' . esc_html__( 'All terms', 'bestofislam' ) . '</a></p></aside>';
}

/**
 * Structured data: the Glossary is a DefinedTermSet and each entry a DefinedTerm.
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

	if ( $post->ID === $parent->ID ) {
		$terms = array();

		foreach ( boi_glossary_entries() as $entry ) {
			$terms[] = array(
				'@type'       => 'DefinedTerm',
				'name'        => wp_strip_all_tags( get_the_title( $entry ) ),
				'description' => wp_strip_all_tags( $entry->post_excerpt ),
				'url'         => get_permalink( $entry ),
			);
		}

		$data = array(
			'@context'       => 'https://schema.org',
			'@type'          => 'DefinedTermSet',
			'name'           => wp_strip_all_tags( get_the_title( $parent ) ),
			'url'            => $set_url,
			'hasDefinedTerm' => $terms,
		);
	} else {
		$data = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'DefinedTerm',
			'name'             => wp_strip_all_tags( get_the_title( $post ) ),
			'description'      => wp_strip_all_tags( $post->post_excerpt ),
			'url'              => get_permalink( $post ),
			'inDefinedTermSet' => array(
				'@type' => 'DefinedTermSet',
				'name'  => wp_strip_all_tags( get_the_title( $parent ) ),
				'url'   => $set_url,
			),
		);
	}

	echo "\n<script type=\"application/ld+json\">" . boi_json_ld( $data ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'wp_head', 'boi_glossary_schema', 22 );
