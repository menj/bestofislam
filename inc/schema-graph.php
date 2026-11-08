<?php
/**
 * The page graph: one JSON-LD graph per page.
 *
 * Every module that describes a page in schema.org terms hands its nodes to
 * boi_schema_graph_nodes(): the site entities and the article
 * (structured-data.php), the listicles (schema.php), the Glossary
 * (glossary.php), the questions (questions.php) and the breadcrumb trail
 * (seo.php). This file adds what describes every page (the WebPage and its
 * subtype, the header, the footer and their menus, the site search) and what
 * an article cites and names: the Qur'an verses it quotes, the hadith whose
 * grading its notes give, the sources in its notes, the Glossary terms it
 * links and the people and things it names (inc/data/schema-entities.json).
 *
 * Nodes are merged by @id, so a node two modules describe appears once. The
 * graph is printed in one script element. With Rank Math's schema active the
 * theme prints nothing: boi_rank_math_json_ld() merges these nodes into Rank
 * Math's graph by @id instead.
 *
 * Every node describes something a visitor can see on the page, or a plain
 * fact about it. When the thing is absent, so is the node.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The address of the current view, as the page graph identifies it.
 *
 * @return string
 */
function boi_schema_page_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_singular() ) {
		return (string) get_permalink( get_queried_object() );
	}

	if ( is_home() && get_option( 'page_for_posts' ) ) {
		return (string) get_permalink( (int) get_option( 'page_for_posts' ) );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );

		if ( ! is_wp_error( $link ) ) {
			// Page 2 onwards lists other posts, so it gets its own address.
			$paged = (int) get_query_var( 'paged' );

			return $paged > 1 ? (string) get_pagenum_link( $paged ) : $link;
		}
	}

	if ( is_author() && get_queried_object() instanceof WP_User ) {
		return get_author_posts_url( get_queried_object()->ID );
	}

	if ( is_search() ) {
		return get_search_link();
	}

	global $wp;

	return trailingslashit( home_url( isset( $wp->request ) ? (string) $wp->request : '' ) );
}

/**
 * The @id of the page node. An author archive keeps the id its ProfilePage
 * has always had.
 *
 * @param string $url Page address.
 * @return string
 */
function boi_schema_page_id( $url ) {
	return $url . ( is_author() ? '#profilepage' : '#webpage' );
}

/**
 * The data file of schema entities, Qur'an chapters and hadith collections.
 *
 * @return array
 */
function boi_schema_entities_data() {
	static $data = null;

	if ( null === $data ) {
		$file = BOI_DIR . '/inc/data/schema-entities.json';
		$raw  = is_readable( $file ) ? file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$data = json_decode( (string) $raw, true );
		$data = is_array( $data ) ? $data : array();
	}

	return $data;
}

/**
 * One entity from the data file as a node, by key.
 *
 * @param string $key Entity key.
 * @return array|null
 */
function boi_schema_entity_node( $key ) {
	$data = boi_schema_entities_data();

	if ( empty( $data['entities'][ $key ] ) ) {
		return null;
	}

	$node = array_diff_key( $data['entities'][ $key ], array( 'names' => 1 ) );

	return array( '@id' => boi_entity_id( $key ) ) + $node;
}

/**
 * A chapter (surah) of the Qur'an, part of the Qur'an.
 *
 * @param int $n Surah number, 1 to 114.
 * @return array|null
 */
function boi_schema_surah_node( $n ) {
	$data  = boi_schema_entities_data();
	$names = isset( $data['surahs'] ) ? $data['surahs'] : array();
	$n     = (int) $n;

	if ( $n < 1 || $n > 114 ) {
		return null;
	}

	$node = array(
		'@type'      => 'Chapter',
		'@id'        => boi_entity_id( 'quran-' . $n ),
		'position'   => $n,
		'name'       => isset( $names[ $n - 1 ] ) ? 'Surah ' . $names[ $n - 1 ] : 'Surah ' . $n,
		'inLanguage' => 'ar',
		'isPartOf'   => array( '@id' => boi_entity_id( 'quran' ) ),
	);

	return $node;
}

/**
 * The plain text of a block tree's paragraphs and list items, with the
 * footnote markers removed.
 *
 * @param string $html Post content.
 * @return string
 */
function boi_schema_plain_text( $html ) {
	$html = preg_replace( '#<sup>.*?</sup>#s', '', (string) $html );
	$html = preg_replace( '#<!--.*?-->#s', ' ', $html );

	return boi_plain( preg_replace( '#</(p|li|h[1-6])>#', "$0\n", $html ) );
}

/**
 * The Qur'an verses an article quotes, as Quotation nodes, and the chapters
 * it names. A quotation is text in quotation marks followed at once by its
 * reference, the way every article sets one: "(Quran 2:256)", or "(2:256)" in
 * a paragraph that names the Qur'an and no book of the Bible, since the
 * articles cite Bible verses the same way. Text outside that form is not
 * guessed at.
 *
 * @param WP_Post $post Article.
 * @return array { quotes: Quotation nodes, chapters: surah numbers named }
 */
function boi_schema_quran_quotations( $post ) {
	$url      = get_permalink( $post );
	$quotes   = array();
	$chapters = array();
	$bible    = '/\b(?:Genesis|Exodus|Leviticus|Numbers|Deuteronomy|Joshua|Judges|Ruth|Samuel|Kings|Chronicles|Ezra|Nehemiah|Esther|Job|Psalms?|Proverbs|Ecclesiastes|Isaiah|Jeremiah|Lamentations|Ezekiel|Daniel|Hosea|Joel|Amos|Micah|Matthew|Mark|Luke|John|Acts|Romans|Corinthians|Galatians|Ephesians|Philippians|Colossians|Thessalonians|Timothy|Titus|Hebrews|James|Peter|Jude|Revelation|Bible|Gospels?|Torah|Testament|Septuagint)\b/u';
	$quran    = '/\bQur[\'’]?an\b/u';

	preg_match_all( '#<(p|li)>(.*?)</\1>#s', (string) $post->post_content, $paras );

	foreach ( $paras[2] as $html ) {
		$text = boi_plain( preg_replace( '#<sup>.*?</sup>#s', '', $html ) );
		$bare = preg_match( $quran, $text ) && ! preg_match( $bible, $text );
		$pre  = $bare ? '(?:Qur[\'’]?an )?' : 'Qur[\'’]?an ';

		if ( preg_match_all( '/[“"]([^”"]{8,700})[”"]\s*\(' . $pre . '(\d{1,3}):(\d{1,3})(?:[–-](\d{1,3}))?(?:[;,][^)]*)?\)/u', $text, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $match ) {
				$surah = (int) $match[2];
				$ref   = $surah . ':' . $match[3] . ( empty( $match[4] ) ? '' : '–' . $match[4] );
				$id    = $url . '#quran-' . str_replace( array( ':', '–' ), '-', $ref );

				// Two quotations of one verse would share an @id; keep the first.
				if ( $surah < 1 || $surah > 114 || isset( $quotes[ $id ] ) ) {
					continue;
				}

				$chapters[]    = $surah;
				$quotes[ $id ] = array(
					'@type'             => 'Quotation',
					'@id'               => $id,
					'text'              => trim( $match[1] ),
					'inLanguage'        => get_bloginfo( 'language' ),
					'identifier'        => 'Quran ' . $ref,
					'isPartOf'          => array( '@id' => boi_entity_id( 'quran-' . $surah ) ),
					'translationOfWork' => array( '@id' => boi_entity_id( 'quran-' . $surah ) ),
				);
			}
		}

		if ( preg_match_all( '/\bQur[\'’]?an (\d{1,3}):\d{1,3}/u', $text, $refs ) ) {
			foreach ( $refs[1] as $surah ) {
				if ( (int) $surah >= 1 && (int) $surah <= 114 ) {
					$chapters[] = (int) $surah;
				}
			}
		}
	}

	$chapters = array_values( array_unique( $chapters ) );
	sort( $chapters );

	return array(
		'quotes'   => array_values( $quotes ),
		'chapters' => $chapters,
	);
}

/**
 * The notes at the foot of an article: the items of the list that follows a
 * "Notes" heading. Each note that names a source becomes a citation; a note
 * that is only commentary does not. A hadith whose grading the note gives is
 * a Quotation of its collection, with the grading as a PropertyValue, since
 * schema.org has no property for it. No grading is marked where none is shown.
 *
 * @param WP_Post $post Article.
 * @return array Citation nodes, in note order.
 */
function boi_schema_note_citations( $post ) {
	$notes = boi_schema_find_notes( parse_blocks( $post->post_content ) );
	$url   = get_permalink( $post );
	$data  = boi_schema_entities_data();
	$out   = array();

	foreach ( $notes as $i => $note ) {
		$text = boi_plain( $note );

		if ( '' === $text || ! preg_match( '/<em>|\b(?:pp?|vol|ed|eds|trans|no)\.|\b\d{4}\b|\bQur[\'’]?an\b|sunnah\.com|https?:|\b(?:Sahih|Sunan|Musnad|Jami|Muwatta)\b/u', $note . ' ' . $text ) ) {
			continue;
		}

		$node = array(
			'@id'  => $url . '#note-' . ( $i + 1 ),
			'name' => $text,
		);

		// The collection, and the hadith number where one follows its name.
		$collection = '';
		$number     = '';
		foreach ( isset( $data['collections'] ) ? $data['collections'] : array() as $key => $pattern ) {
			if ( preg_match_all( '/(?:' . $pattern . ')(?:,? (?:no\. )?(\d{1,5})\b)?/iu', $text, $cm, PREG_SET_ORDER ) ) {
				$collection = $key;

				foreach ( $cm as $hit ) {
					if ( ! empty( $hit[1] ) ) {
						$number = $hit[1];
						break;
					}
				}
				break;
			}
		}

		if ( '' !== $collection && preg_match( '/\bgraded ((?:hasan )?sahih|hasan|da[ʿ\']?if|weak|mawdu[ʿ\']?|fabricated)\b/iu', $text, $gm ) ) {
			// additionalProperty does not apply to a CreativeWork, so the
			// grading is a PropertyValue under identifier, which takes one.
			$grading = array(
				'@type'      => 'PropertyValue',
				'propertyID' => 'grading',
				'name'       => 'grading',
				'value'      => strtolower( $gm[1] ),
			);

			if ( preg_match( '/\bgraded [^,;.]*? by ([^,;.()]+)/iu', $text, $bm ) ) {
				$grading['description'] = 'Graded ' . strtolower( $gm[1] ) . ' by ' . trim( $bm[1] );
			}

			$node['@type']      = 'Quotation';
			$node['isPartOf']   = array( '@id' => boi_entity_id( $collection ) );
			$node['identifier'] = array( $grading );

			if ( '' !== $number ) {
				array_unshift(
					$node['identifier'],
					array(
						'@type'      => 'PropertyValue',
						'propertyID' => 'hadith number',
						'value'      => $number,
					)
				);
			}
		} else {
			$node['@type'] = 'CreativeWork';
		}

		$out[] = $node;
	}

	return $out;
}

/**
 * The list items under each "Notes" heading in a block tree.
 *
 * @param array $blocks Parsed blocks.
 * @return string[] The markup of each item.
 */
function boi_schema_find_notes( $blocks ) {
	$items = array();
	$after = false;

	foreach ( $blocks as $block ) {
		$name = (string) $block['blockName'];

		if ( 'core/heading' === $name ) {
			$after = (bool) preg_match( '/^(notes|sources|references)$/i', boi_plain( $block['innerHTML'] ) );
			continue;
		}

		if ( $after && 'core/list' === $name ) {
			$html = (string) $block['innerHTML'];

			foreach ( isset( $block['innerBlocks'] ) ? $block['innerBlocks'] : array() as $inner ) {
				$html .= (string) $inner['innerHTML'];
			}

			if ( preg_match_all( '#<li>(.*?)</li>#s', $html, $m ) ) {
				$items = array_merge( $items, $m[1] );
			}

			$after = false;
			continue;
		}

		if ( '' !== $name && ! empty( $block['innerBlocks'] ) ) {
			$items = array_merge( $items, boi_schema_find_notes( $block['innerBlocks'] ) );
		}

		if ( '' !== $name ) {
			$after = false;
		}
	}

	return $items;
}

/**
 * The Glossary entries an article links: the links boi_gl_filter() adds at
 * first mention. Each is a DefinedTerm under the @id its own page gives it.
 *
 * @param WP_Post $post Article.
 * @return array DefinedTerm nodes.
 */
function boi_schema_glossary_mentions( $post ) {
	if ( ! function_exists( 'boi_gl_map' ) || ! function_exists( 'boi_glossary_parent' ) ) {
		return array();
	}

	$parent = boi_glossary_parent();

	if ( ! $parent ) {
		return array();
	}

	// The cache counts only if it was built from this post's current text,
	// map and budget, the checks boi_gl_filter() makes before it reuses it.
	$items  = boi_gl_map();
	$cached = get_post_meta( $post->ID, '_boi_gl_cache', true );
	$html   = '';

	if ( $items && is_array( $cached ) && isset( $cached['html'], $cached['map'], $cached['budget'], $cached['src'] )
		&& boi_gl_compiled( $items )['hash'] === $cached['map']
		&& boi_gl_max() === (int) $cached['budget']
		&& md5( (string) $post->post_content ) === $cached['src'] ) {
		$html = (string) $cached['html'];
	}

	if ( '' === $html ) {
		$html  = $items ? boi_gl_apply( $post->post_content, boi_gl_compiled( $items ), boi_gl_max() ) : '';
	}

	$set  = get_permalink( $parent );
	$base = preg_quote( untrailingslashit( $set ), '#' );
	$refs = array();

	if ( preg_match_all( '#href="(' . $base . '/([^"\#?/]+)/?)"#', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $link ) {
			$entry = get_page_by_path( 'glossary/' . $link[2], OBJECT, 'page' );
			$url   = trailingslashit( $link[1] );

			if ( ! $entry || isset( $refs[ $url ] ) ) {
				continue;
			}

			// The term in brief; its page holds the full description.
			$refs[ $url ] = array(
				'@type'            => 'DefinedTerm',
				'@id'              => $url . '#term',
				'name'             => wp_strip_all_tags( get_the_title( $entry ) ),
				'url'              => $url,
				'inDefinedTermSet' => array(
					'@type' => 'DefinedTermSet',
					'@id'   => $set . '#glossary',
					'name'  => wp_strip_all_tags( get_the_title( $parent ) ),
					'url'   => $set,
				),
			);
		}
	}

	return array_values( $refs );
}

/**
 * The entities an article is about (by its section, and for every page) and
 * the ones it names in its text.
 *
 * @param WP_Post $post Article.
 * @return array { about: keys, mentions: keys }
 */
function boi_schema_article_entities( $post ) {
	$data  = boi_schema_entities_data();
	$about = isset( $data['about']['*'] ) ? (array) $data['about']['*'] : array();
	$terms = get_the_terms( $post, 'listicle-topic' );

	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( isset( $data['about'][ $term->slug ] ) ) {
				$about = array_merge( $about, (array) $data['about'][ $term->slug ] );
			}
		}
	}

	$text     = boi_plain( get_the_title( $post ) ) . "\n" . boi_schema_plain_text( $post->post_content );
	$mentions = array();

	foreach ( isset( $data['entities'] ) ? $data['entities'] : array() as $key => $entity ) {
		foreach ( isset( $entity['names'] ) ? (array) $entity['names'] : array() as $name ) {
			if ( preg_match( '/(?<![\p{L}\p{N}])' . preg_quote( $name, '/' ) . '(?![\p{L}\p{N}])/u', $text ) ) {
				$mentions[] = $key;
				break;
			}
		}
	}

	$about = array_values( array_unique( $about ) );

	return array(
		'about'    => $about,
		'mentions' => array_values( array_diff( $mentions, $about ) ),
	);
}

/**
 * The extra details of an article and the nodes it cites and names.
 *
 * @param WP_Post $post    Article.
 * @param string  $page_id @id of the page node.
 * @return array { article: properties to add, nodes: further graph nodes }
 */
function boi_schema_article_details( $post, $page_id ) {
	$is_listicle = has_block( 'bestofislam/listicle', $post );
	$words       = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	$minutes     = max( 1, (int) round( $words / 220 ) );
	$nodes       = array();
	$details     = array(
		'mainEntityOfPage'    => array( '@id' => $page_id ),
		'wordCount'           => $words,
		// The reading time the article shows under its title.
		'timeRequired'        => 'PT' . $minutes . 'M',
		'isAccessibleForFree' => true,
		'copyrightHolder'     => array( '@id' => boi_entity_id( 'organization' ) ),
		'accessMode'          => array( 'textual' ),
		'accessModeSufficient' => array(
			'@type'           => 'ItemList',
			'itemListElement' => array( 'textual' ),
		),
		'accessibilityFeature' => array( 'structuralNavigation' ),
	);

	// A Reflection is a journal post; a listicle stays an Article.
	if ( ! $is_listicle ) {
		$details['@type'] = array( 'Article', 'BlogPosting' );

		if ( has_excerpt( $post ) ) {
			$details['abstract'] = boi_plain( get_the_excerpt( $post ) );
		}
	}

	$tags = get_the_terms( $post, 'post_tag' );

	if ( $tags && ! is_wp_error( $tags ) ) {
		$details['keywords'] = implode( ', ', array_map( 'boi_plain', wp_list_pluck( $tags, 'name' ) ) );
	}

	// The contents at the head of the article, from WP TOC's rules in inc/anatomy.php.
	if ( function_exists( 'boi_toc_view_tree' ) && boi_toc_view_tree() ) {
		$details['accessibilityFeature'][] = 'tableOfContents';
	}

	if ( boi_article_image_object( $post ) || boi_inline_image_objects( $post ) ) {
		$details['accessMode'][]            = 'visual';
		$details['accessibilityFeature'][] = 'alternativeText';
	}

	if ( $is_listicle ) {
		$lists = boi_listicle_schema_nodes( $post );

		// An ItemList is not a CreativeWork, so it cannot be a hasPart. The
		// list is what a listicle is made of: its main entity.
		foreach ( $lists as $list ) {
			$details['mainEntity'][] = array( '@id' => $list['@id'] );
		}

		if ( isset( $details['mainEntity'] ) && 1 === count( $details['mainEntity'] ) ) {
			$details['mainEntity'] = $details['mainEntity'][0];
		}

		foreach ( boi_find_listicles( parse_blocks( $post->post_content ) ) as $listicle ) {
			if ( ! empty( $listicle['attrs']['showJumpList'] ) ) {
				$details['accessibilityFeature'][] = 'tableOfContents';
				break;
			}
		}

		$nodes = array_merge( $nodes, $lists );
	}

	// What the article cites: the verses it quotes and the sources in its notes.
	$quran     = boi_schema_quran_quotations( $post );
	$notes     = boi_schema_note_citations( $post );
	$citations = array();
	$books     = array();

	foreach ( array_merge( $quran['quotes'], $notes ) as $node ) {
		$citations[] = array( '@id' => $node['@id'] );
		$nodes[]     = $node;

		if ( isset( $node['isPartOf']['@id'] ) && 'Quotation' === $node['@type'] && false === strpos( $node['@id'], '#quran-' ) ) {
			$books[ $node['isPartOf']['@id'] ] = true;
		}
	}

	if ( $citations ) {
		$details['citation'] = $citations;
	}

	foreach ( $quran['chapters'] as $n ) {
		$nodes[] = boi_schema_surah_node( $n );
	}

	if ( $quran['chapters'] ) {
		$nodes[] = boi_schema_entity_node( 'quran' );
	}

	foreach ( array_keys( $books ) as $id ) {
		$key  = substr( $id, strpos( $id, '#' ) + 1 );
		$book = boi_schema_entity_node( $key );

		if ( $book ) {
			$nodes[] = $book;
		}
	}

	// What it is about and what it names.
	$entities = boi_schema_article_entities( $post );
	$mentions = array();

	foreach ( $entities['about'] as $key ) {
		$node = boi_schema_entity_node( $key );

		if ( $node ) {
			$details['about'][] = array( '@id' => $node['@id'] );
			$nodes[]            = $node;
		}
	}

	foreach ( $entities['mentions'] as $key ) {
		$node = boi_schema_entity_node( $key );

		if ( $node ) {
			$mentions[] = array( '@id' => $node['@id'] );
			$nodes[]    = $node;
		}
	}

	foreach ( $quran['chapters'] as $n ) {
		$mentions[] = array( '@id' => boi_entity_id( 'quran-' . $n ) );
	}

	$terms    = boi_schema_glossary_mentions( $post );
	$set      = null;

	foreach ( $terms as $term ) {
		$set                      = $term['inDefinedTermSet'];
		$term['inDefinedTermSet'] = array( '@id' => $set['@id'] );
		$mentions[]               = array( '@id' => $term['@id'] );
		$nodes[]                  = $term;
	}

	if ( $set ) {
		$nodes[] = $set;
	}

	if ( $mentions ) {
		$details['mentions'] = $mentions;
	}

	return array(
		'article' => $details,
		'nodes'   => array_values( array_filter( $nodes ) ),
	);
}

/**
 * The posts a collection view lists, as an ItemList.
 *
 * @param string $url Page address.
 * @return array|null
 */
function boi_schema_query_list( $url ) {
	global $wp_query;

	$posts = ( $wp_query && ! empty( $wp_query->posts ) ) ? $wp_query->posts : array();

	if ( ! $posts ) {
		return null;
	}

	// Positions run on from earlier pages, so the step is the query's page
	// size; a short last page would give a wrong count.
	$per   = ( $wp_query && isset( $wp_query->query_vars['posts_per_page'] ) ) ? (int) $wp_query->query_vars['posts_per_page'] : 0;
	$per   = $per > 0 ? $per : max( 1, (int) get_option( 'posts_per_page' ) );
	$start = ( max( 1, (int) get_query_var( 'paged' ) ) - 1 ) * $per;
	$items = array();

	foreach ( array_values( $posts ) as $i => $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $start + $i + 1,
			'url'      => get_permalink( $post ),
			'name'     => boi_plain( get_the_title( $post ) ),
		);
	}

	if ( ! $items ) {
		return null;
	}

	return array(
		'@type'           => 'ItemList',
		'@id'             => $url . '#itemlist',
		'numberOfItems'   => count( $items ),
		'itemListElement' => $items,
	);
}

/**
 * The Reflections strip on the front page, as an ItemList of BlogPosting.
 * The same query as boi_render_reflections_strip().
 *
 * @return array|null
 */
function boi_schema_reflections_list() {
	if ( ! defined( 'BOI_LISTICLE_META' ) ) {
		return null;
	}

	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => BOI_LISTICLE_META,
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	if ( empty( $posts ) ) {
		return null;
	}

	$items = array();

	foreach ( array_values( $posts ) as $i => $post ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'item'     => array(
				'@type'         => 'BlogPosting',
				'@id'           => get_permalink( $post ) . '#article',
				'headline'      => boi_plain( get_the_title( $post ) ),
				'url'           => get_permalink( $post ),
				'datePublished' => get_post_time( 'c', true, $post ),
			),
		);
	}

	return array(
		'@type'           => 'ItemList',
		'@id'             => home_url( '/#reflections' ),
		'name'            => __( 'Reflections', 'bestofislam' ),
		'itemListElement' => $items,
	);
}

/**
 * The menus of a template part: each navigation block, with its links.
 *
 * @param string $slug Part slug, header or footer.
 * @return array[] Each: label, links (label, url).
 */
function boi_schema_part_menus( $slug ) {
	$content = '';

	if ( function_exists( 'get_block_template' ) ) {
		$part = get_block_template( get_stylesheet() . '//' . $slug, 'wp_template_part' );

		if ( $part && ! empty( $part->content ) ) {
			$content = (string) $part->content;
		}
	}

	if ( '' === $content && is_readable( BOI_DIR . '/parts/' . $slug . '.html' ) ) {
		$content = (string) file_get_contents( BOI_DIR . '/parts/' . $slug . '.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	$menus = array();
	$walk  = function ( $blocks ) use ( &$walk, &$menus ) {
		foreach ( $blocks as $block ) {
			if ( 'core/navigation' === $block['blockName'] ) {
				$links = array();

				foreach ( $block['innerBlocks'] as $link ) {
					if ( 'core/navigation-link' === $link['blockName'] && ! empty( $link['attrs']['label'] ) && ! empty( $link['attrs']['url'] ) ) {
						$links[] = array(
							'label' => boi_plain( $link['attrs']['label'] ),
							'url'   => $link['attrs']['url'],
						);
					}
				}

				if ( $links ) {
					$menus[] = array(
						'label' => isset( $block['attrs']['ariaLabel'] ) ? (string) $block['attrs']['ariaLabel'] : '',
						'links' => $links,
					);
				}

				continue;
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'] );
			}
		}
	};

	$walk( parse_blocks( $content ) );

	return $menus;
}

/**
 * The header and footer, with a SiteNavigationElement for each menu in them.
 * The same on every page, so their @id belongs to the site.
 *
 * @return array Graph nodes: WPHeader, WPFooter, then the menus.
 */
function boi_schema_frame_nodes() {
	$nodes = array();
	$parts = array(
		'header' => 'WPHeader',
		'footer' => 'WPFooter',
	);

	foreach ( $parts as $slug => $type ) {
		$part = array(
			'@type'    => $type,
			'@id'      => boi_entity_id( $slug ),
			'isPartOf' => array( '@id' => boi_entity_id( 'website' ) ),
		);
		$navs       = array();
		$menu_nodes = array();

		foreach ( boi_schema_part_menus( $slug ) as $menu ) {
			$label = '' !== $menu['label'] ? $menu['label'] : ( 'header' === $slug ? __( 'Main menu', 'bestofislam' ) : __( 'Footer menu', 'bestofislam' ) );
			$id    = boi_entity_id( 'nav-' . $slug . '-' . sanitize_title( $label ) );
			$links = array();

			foreach ( $menu['links'] as $link ) {
				$href    = 0 === strpos( $link['url'], '/' ) ? home_url( $link['url'] ) : $link['url'];
				$links[] = array(
					'@type' => 'SiteNavigationElement',
					'name'  => $link['label'],
					'url'   => esc_url_raw( $href ),
				);
			}

			$menu_nodes[] = array(
				'@type'   => 'SiteNavigationElement',
				'@id'     => $id,
				'name'    => $label,
				'hasPart' => $links,
			);
			$navs[]  = array( '@id' => $id );
		}

		if ( $navs ) {
			$part['hasPart'] = $navs;
		}

		$nodes = array_merge( $nodes, array( $part ), $menu_nodes );
	}

	return $nodes;
}

/**
 * The page node's type for the current view.
 *
 * @param bool $faq Whether the page answers questions (the front page).
 * @return string|string[]
 */
function boi_schema_page_type( $faq = false ) {
	if ( is_front_page() ) {
		return $faq ? array( 'WebPage', 'FAQPage' ) : 'WebPage';
	}

	if ( is_search() ) {
		return 'SearchResultsPage';
	}

	if ( is_author() ) {
		return 'ProfilePage';
	}

	if ( is_singular( 'page' ) ) {
		$post = get_queried_object();
		$slug = $post instanceof WP_Post ? $post->post_name : '';

		if ( function_exists( 'boi_is_glossary_page' ) && boi_is_glossary_page() ) {
			return $post->post_parent ? 'ItemPage' : 'CollectionPage';
		}

		if ( 'about' === $slug ) {
			return 'AboutPage';
		}

		if ( 'contact' === $slug || ( $post instanceof WP_Post && has_block( 'bestofislam/contact-form', $post ) ) ) {
			return 'ContactPage';
		}

		return 'WebPage';
	}

	if ( is_home() || is_archive() ) {
		return 'CollectionPage';
	}

	return 'WebPage';
}

/**
 * Every node of the current page's graph, merged by @id.
 *
 * @param bool $rank_math Whether the nodes are for Rank Math's graph, which
 *                        has its own BreadcrumbList when its breadcrumbs are on.
 * @return array Graph nodes.
 */
function boi_schema_graph_nodes( $rank_math = false ) {
	if ( is_feed() || is_embed() ) {
		return array();
	}

	$url     = boi_schema_page_url();
	$page_id = boi_schema_page_id( $url );
	$nodes   = boi_site_entities();
	$page    = array(
		'@type'      => boi_schema_page_type(),
		'@id'        => $page_id,
		'url'        => $url,
		'name'       => boi_plain( wp_get_document_title() ),
		'inLanguage' => get_bloginfo( 'language' ),
		'isPartOf'   => array( '@id' => boi_entity_id( 'website' ) ),
		'speakable'  => array(
			'@type'       => 'SpeakableSpecification',
			'cssSelector' => array( 'h1' ),
		),
	);

	$frame = boi_schema_frame_nodes();

	foreach ( $frame as $node ) {
		if ( in_array( $node['@type'], array( 'WPHeader', 'WPFooter' ), true ) ) {
			$page['hasPart'][] = array( '@id' => $node['@id'] );
		}
	}

	if ( is_front_page() ) {
		$questions = function_exists( 'boi_questions_schema_nodes' ) ? boi_questions_schema_nodes() : array();

		if ( $questions ) {
			$page['@type']      = boi_schema_page_type( true );
			$page['mainEntity'] = array_map(
				function ( $q ) {
					return array( '@id' => $q['@id'] );
				},
				$questions
			);
			$nodes              = array_merge( $nodes, $questions );
		}

		$reflections = boi_schema_reflections_list();

		// The front page names its latest Reflections; hasPart would need a
		// CreativeWork, and the strip is an ItemList.
		if ( $reflections ) {
			$page['mentions'][] = array( '@id' => $reflections['@id'] );
			$nodes[]            = $reflections;
		}
	} elseif ( is_singular( 'post' ) ) {
		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {
			$article = boi_article_entities( $post );
			$details = boi_schema_article_details( $post, $page_id );

			$article[0] = array_merge( $article[0], $details['article'] );
			// The site's Organization is a node of this graph; refer to it.
			$article[0]['publisher'] = array( '@id' => boi_entity_id( 'organization' ) );

			if ( isset( $article[0]['author']['url'] ) ) {
				$article[0]['author']['@id'] = $article[0]['author']['url'] . '#person';
			}

			$page['mainEntity'] = array( '@id' => $article[0]['@id'] );

			if ( ! has_block( 'bestofislam/listicle', $post ) ) {
				$page['speakable']['cssSelector'][] = '.wp-block-post-excerpt';
			}

			$hero = boi_article_image_object( $post );

			if ( $hero ) {
				$page['primaryImageOfPage'] = array( '@id' => $hero['@id'] );
			}

			$nodes = array_merge( $nodes, $article, $details['nodes'] );
		}
	} elseif ( is_author() ) {
		$nodes = array_merge( $nodes, boi_profile_entities( get_queried_object() ) );
	} elseif ( function_exists( 'boi_is_glossary_page' ) && boi_is_glossary_page() ) {
		$nodes = array_merge( $nodes, boi_glossary_schema_nodes() );
	} elseif ( is_home() || is_archive() || is_search() ) {
		$list = boi_schema_query_list( $url );

		if ( $list ) {
			$page['mainEntity'] = array( '@id' => $list['@id'] );
			$nodes[]            = $list;
		}
	}

	$crumbs = ( $rank_math && function_exists( 'boi_rank_math_breadcrumb_schema' ) && boi_rank_math_breadcrumb_schema() ) ? null : boi_breadcrumb_node( $url );

	if ( $crumbs ) {
		$page['breadcrumb'] = array( '@id' => $crumbs['@id'] );
		$nodes[]            = $crumbs;
	}

	// The page node comes after the modules' own description of it, and the
	// frame last, so a module's more specific value is the one kept.
	$nodes[] = $page;
	$nodes   = array_merge( $nodes, $frame );

	return boi_schema_one_author( boi_schema_merge_nodes( (array) apply_filters( 'boi_schema_nodes', $nodes ) ) );
}

/**
 * Merges nodes that share an @id: the first value of each property is kept,
 * the types are joined, and lists of references are combined.
 *
 * @param array $nodes Graph nodes.
 * @return array Graph nodes, one per @id.
 */
function boi_schema_merge_nodes( $nodes ) {
	$out = array();
	$at  = array();

	foreach ( $nodes as $node ) {
		if ( ! is_array( $node ) || empty( $node ) ) {
			continue;
		}

		if ( empty( $node['@id'] ) ) {
			$out[] = $node;
			continue;
		}

		$id = $node['@id'];

		if ( ! isset( $at[ $id ] ) ) {
			$at[ $id ] = count( $out );
			$out[]     = $node;
			continue;
		}

		$out[ $at[ $id ] ] = boi_schema_merge_node( $out[ $at[ $id ] ], $node );
	}

	return array_values( $out );
}

/**
 * Two descriptions of one node as one: the first keeps its values, gains the
 * properties only the second has, and both types.
 *
 * @param array $a First description.
 * @param array $b Second description.
 * @return array
 */
function boi_schema_merge_node( $a, $b ) {
	foreach ( $b as $key => $value ) {
		if ( '@type' === $key ) {
			$types     = array_values( array_unique( array_merge( (array) ( isset( $a['@type'] ) ? $a['@type'] : array() ), (array) $value ) ) );
			$a['@type'] = 1 === count( $types ) ? $types[0] : $types;
		} elseif ( ! isset( $a[ $key ] ) || '' === $a[ $key ] || array() === $a[ $key ] ) {
			$a[ $key ] = $value;
		} elseif ( in_array( $key, array( 'hasPart', 'mentions', 'about', 'citation' ), true ) && is_array( $a[ $key ] ) && is_array( $value ) && isset( $a[ $key ][0] ) && isset( $value[0] ) ) {
			$a[ $key ] = array_values( array_unique( array_merge( $a[ $key ], $value ), SORT_REGULAR ) );
		}
	}

	return $a;
}

/**
 * Adds the theme's nodes to Rank Math's graph. A node Rank Math already has,
 * by @id, gains only the properties and types it lacks. Rank Math names its
 * page, article, breadcrumb and WebSite nodes in its own way, so the theme's nodes of
 * those kinds are folded into Rank Math's, and references follow them.
 *
 * @param array $data  Rank Math's entities, keyed by name.
 * @param array $nodes The theme's nodes, from boi_schema_graph_nodes().
 * @return array
 */
function boi_schema_merge_into_rank_math( $data, $nodes ) {
	$by_id = array();

	foreach ( $data as $key => $node ) {
		if ( is_array( $node ) && ! empty( $node['@id'] ) ) {
			$by_id[ $node['@id'] ] = $key;
		}
	}

	$families = array(
		'page'    => array( 'WebPage', 'CollectionPage', 'ProfilePage', 'SearchResultsPage', 'AboutPage', 'ContactPage', 'ItemPage', 'FAQPage', 'QAPage' ),
		'article' => array( 'Article', 'BlogPosting', 'NewsArticle' ),
		'crumbs'  => array( 'BreadcrumbList' ),
		// Rank Math's WebSite may carry its own @id; the theme's folds into it
		// so the graph holds one WebSite and every isPartOf points at it.
		'website' => array( 'WebSite' ),
	);
	$theirs   = array();

	foreach ( $data as $key => $node ) {
		if ( ! is_array( $node ) || empty( $node['@type'] ) || empty( $node['@id'] ) ) {
			continue;
		}

		foreach ( $families as $family => $types ) {
			if ( ! isset( $theirs[ $family ] ) && array_intersect( (array) $node['@type'], $types ) ) {
				$theirs[ $family ] = $node['@id'];
			}
		}
	}

	$alias = array();

	// Rank Math spells the hero image #primaryImage; the theme, #primaryimage.
	foreach ( $data as $node ) {
		if ( is_array( $node ) && ! empty( $node['@id'] ) && is_string( $node['@id'] ) && '#primaryimage' === strtolower( substr( $node['@id'], -13 ) ) ) {
			$alias[ '@' . substr( $node['@id'], 0, -13 ) . '#primaryimage' ] = $node['@id'];
		}
	}

	foreach ( $nodes as $node ) {
		if ( empty( $node['@id'] ) || isset( $by_id[ $node['@id'] ] ) || empty( $node['@type'] ) ) {
			continue;
		}

		foreach ( $families as $family => $types ) {
			if ( isset( $theirs[ $family ] ) && array_intersect( (array) $node['@type'], $types ) && ! in_array( $family, array_keys( $alias ), true ) ) {
				// Only the page's own node and article are folded, never an
				// item in a list such as a Reflection on the front page.
				if ( 'article' === $family && false === strpos( $node['@id'], boi_schema_page_url() ) ) {
					continue;
				}

				$alias[ $family ]           = true;
				$alias[ '@' . $node['@id'] ] = $theirs[ $family ];
			}
		}
	}

	$rewrite = function ( $value ) use ( &$rewrite, $alias ) {
		if ( is_array( $value ) ) {
			if ( isset( $value['@id'] ) && is_string( $value['@id'] ) && isset( $alias[ '@' . $value['@id'] ] ) ) {
				$value['@id'] = $alias[ '@' . $value['@id'] ];
			}

			foreach ( $value as $k => $v ) {
				if ( is_array( $v ) ) {
					$value[ $k ] = $rewrite( $v );
				}
			}
		}

		return $value;
	};

	foreach ( $nodes as $node ) {
		$node = $rewrite( $node );

		if ( ! empty( $node['@id'] ) && isset( $by_id[ $node['@id'] ] ) ) {
			$key          = $by_id[ $node['@id'] ];
			$data[ $key ] = boi_schema_merge_node( $data[ $key ], $node );
			continue;
		}

		$key = 'boi_' . ( empty( $node['@id'] ) ? md5( serialize( $node ) ) : sanitize_key( str_replace( array( home_url( '/' ), '#' ), array( '', '_' ), $node['@id'] ) ) );

		while ( isset( $data[ $key ] ) ) {
			$key .= '_';
		}

		$data[ $key ] = $node;

		if ( ! empty( $node['@id'] ) ) {
			$by_id[ $node['@id'] ] = $key;
		}
	}

	// A node the theme defines inside another (the hero image inside the
	// article) may have been left behind when Rank Math's node kept its own
	// value. Whatever is still referenced and not defined is added whole.
	$defined = array();
	$wanted  = array();
	$walk    = function ( $value ) use ( &$walk, &$defined, &$wanted ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		if ( isset( $value['@id'] ) && is_string( $value['@id'] ) ) {
			if ( count( $value ) > 1 ) {
				$defined[ $value['@id'] ] = true;
			} else {
				$wanted[ $value['@id'] ] = true;
			}
		}

		foreach ( $value as $v ) {
			$walk( $v );
		}
	};
	$walk( $data );

	$ours = array();
	$find = function ( $value ) use ( &$find, &$ours ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		if ( isset( $value['@id'], $value['@type'] ) && is_string( $value['@id'] ) && ! isset( $ours[ $value['@id'] ] ) ) {
			$ours[ $value['@id'] ] = $value;
		}

		foreach ( $value as $v ) {
			$find( $v );
		}
	};
	$find( $rewrite( $nodes ) );

	foreach ( array_keys( array_diff_key( $wanted, $defined ) ) as $id ) {
		if ( isset( $ours[ $id ] ) ) {
			$data[ 'boi_' . sanitize_key( str_replace( array( home_url( '/' ), '#' ), array( '', '_' ), $id ) ) ] = $ours[ $id ];
		}
	}

	return $data;
}

/**
 * Prints the page graph, once, when Rank Math is not printing its own.
 *
 * @return void
 */
function boi_print_schema_graph() {
	if ( function_exists( 'boi_rank_math_schema_active' ) && boi_rank_math_schema_active() ) {
		return;
	}

	$nodes = boi_schema_graph_nodes();

	if ( empty( $nodes ) ) {
		return;
	}

	echo "\n<script type=\"application/ld+json\">" . boi_json_ld( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $nodes,
		)
	) . "</script>\n";
}
add_action( 'wp_head', 'boi_print_schema_graph', 19 );
