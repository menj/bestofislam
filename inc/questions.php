<?php
/**
 * Questions answered.
 *
 * One list of the questions put to Muslims, each with a short answer and the
 * article that argues it in full. The list feeds three places: the FAQ page
 * (/faq/), which holds every question with its anchor; the questions band on
 * the front page, a teaser of the featured questions that links to it; and a
 * panel at the head of each article naming the questions it answers. The list is data, in inc/data/questions.json;
 * editing a question there changes it everywhere.
 *
 * Questions are phrased the way they are asked, attributed to no one.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The questions, grouped by theme.
 *
 * Each item: question (q), answer (a), answering article slug, and whether
 * it is featured on the front page (feat). The list is read from
 * inc/data/questions.json; a group without a label and a list of items, or
 * an item without a question, answer and slug, is left out, so a damaged
 * entry can never reach the renderers.
 *
 * @return array
 */
function boi_questions() {
	static $groups = null;
	if ( null !== $groups ) {
		return $groups;
	}
	$out = array();
	foreach ( boi_read_data( 'questions' ) as $key => $group ) {
		if ( ! is_array( $group ) || ! is_string( $group['label'] ?? null ) || ! is_array( $group['items'] ?? null ) ) {
			continue;
		}
		$items = array();
		foreach ( $group['items'] as $item ) {
			if ( is_array( $item ) && is_string( $item['q'] ?? null ) && is_string( $item['a'] ?? null ) && is_string( $item['slug'] ?? null ) ) {
				$items[] = $item;
			}
		}
		if ( $items ) {
			$group['items'] = $items;
			$out[ $key ]    = $group;
		}
	}
	if ( $out ) {
		$groups = $out; // A failed read is not cached.
	}
	return $out;
}

/**
 * Stable anchor for a question.
 *
 * @param string $question Question text.
 * @return string
 */
function boi_question_anchor( $question ) {
	return 'q-' . sanitize_title( $question );
}

/**
 * Resolves an item's answering article, published only.
 *
 * @param array $item Question item.
 * @return WP_Post|null
 */
function boi_question_post( $item ) {
	static $cache = array();

	if ( ! array_key_exists( $item['slug'], $cache ) ) {
		$post                   = get_page_by_path( $item['slug'], OBJECT, 'post' );
		$cache[ $item['slug'] ] = ( $post && 'publish' === $post->post_status && ! boi_is_unlisted( $post ) ) ? $post : null;
	}

	return $cache[ $item['slug'] ];
}

/**
 * The FAQ page, where every question lives, if it is published.
 *
 * @return WP_Post|null
 */
function boi_questions_page() {
	$page = get_page_by_path( 'faq' );

	return ( $page instanceof WP_Post && 'publish' === $page->post_status ) ? $page : null;
}

/**
 * Whether the current view is the FAQ page.
 *
 * @return bool
 */
function boi_is_questions_page() {
	$page = boi_questions_page();

	return $page && is_page() && (int) get_queried_object_id() === (int) $page->ID;
}

/**
 * Where the questions live: the FAQ page, or the front-page band while there
 * is no FAQ page.
 *
 * @param string $anchor Optional anchor within the page.
 * @return string
 */
function boi_questions_url( $anchor = '' ) {
	$page = boi_questions_page();
	$base = $page ? get_permalink( $page ) : home_url( '/' );

	if ( '' === $anchor ) {
		return $page ? $base : $base . '#objections';
	}

	return $base . '#' . $anchor;
}

/**
 * Registers the question blocks.
 *
 * @return void
 */
function boi_register_question_blocks() {
	register_block_type( 'bestofislam/article-questions', array( 'render_callback' => 'boi_render_article_questions' ) );
}
add_action( 'init', 'boi_register_question_blocks' );

/**
 * One question as an accordion item.
 *
 * @param array   $item  Question item.
 * @param WP_Post $post  Answering article.
 * @param bool    $id    Whether to give the item an anchor.
 * @param int     $order Position in the list, kept for the one-column phone
 *                       layout when the items are split into two columns.
 * @return string
 */
function boi_question_item( $item, $post, $id = true, $order = 0 ) {
	return sprintf(
		'<details class="boi-objection"%1$s%7$s><summary><span class="boi-objection__label">%2$s</span><span class="boi-objection__claim">%3$s</span></summary><div class="boi-objection__answer"><p>%4$s</p><a href="%5$s">%6$s</a></div></details>',
		$id ? ' id="' . esc_attr( boi_question_anchor( $item['q'] ) ) . '"' : '',
		esc_html__( 'The question', 'bestofislam' ),
		esc_html( $item['q'] ),
		esc_html( $item['a'] ),
		esc_url( get_permalink( $post ) ),
		esc_html( sprintf( /* translators: %s: title. */ __( 'Full answer: %s', 'bestofislam' ), get_the_title( $post ) ) ),
		$order ? ' style="--boi-o:' . (int) $order . '"' : ''
	);
}

/**
 * The front-page band: a teaser of the featured questions, and a button to
 * the FAQ page, where every question is answered. While there is no FAQ page
 * the rest of the questions open below, behind a single disclosure.
 *
 * @return string
 */
function boi_render_objections() {
	$featured = array();
	$total    = 0;
	$page     = boi_questions_page();

	foreach ( boi_questions() as $group ) {
		foreach ( $group['items'] as $item ) {
			$post = boi_question_post( $item );

			if ( ! $post ) {
				continue;
			}

			++$total;

			if ( ! empty( $item['feat'] ) ) {
				$featured[] = boi_question_item( $item, $post, ! $page, count( $featured ) + 1 );
			}
		}
	}

	if ( ! $total ) {
		return '';
	}

	if ( $page ) {
		$more = sprintf(
			'<p class="boi-objections__more"><a class="boi-objections__all" href="%1$s">%2$s</a></p>',
			esc_url( boi_questions_url() ),
			esc_html( sprintf( /* translators: %d: number of questions. */ _n( 'See the %d question answered', 'See all %d questions answered', $total, 'bestofislam' ), $total ) )
		);
	} else {
		$more = boi_render_question_groups( true, $total - count( $featured ) );
	}

	// Two columns that each stack on their own: in a grid of rows, an opened
	// question made its whole row taller and left a gap beside its closed
	// neighbour. Items alternate between the columns so the reading order
	// runs across; on a phone the columns merge and --boi-o restores it.
	$columns = array( '', '' );

	foreach ( $featured as $i => $html ) {
		$columns[ $i % 2 ] .= $html;
	}

	$band = '' === $columns[0] ? '' : '<div class="boi-objections boi-objections--cols"><div class="boi-objections__col">' . $columns[0] . '</div><div class="boi-objections__col">' . $columns[1] . '</div></div>';

	return $band . $more;
}

/**
 * The questions grouped by theme, each with its anchor.
 *
 * @param bool $rest_only Leave out the featured questions and wrap the
 *                        groups in a "show more" disclosure (the front-page
 *                        fallback); otherwise every question, open (the FAQ
 *                        page).
 * @param int  $rest      Number of questions behind the disclosure.
 * @return string
 */
function boi_render_question_groups( $rest_only = false, $rest = 0 ) {
	$groups = '';

	foreach ( boi_questions() as $key => $group ) {
		$items = '';

		foreach ( $group['items'] as $item ) {
			$post = boi_question_post( $item );

			if ( ! $post || ( $rest_only && ! empty( $item['feat'] ) ) ) {
				continue;
			}

			$items .= boi_question_item( $item, $post );
		}

		if ( '' !== $items ) {
			$groups .= sprintf(
				'<section class="boi-qgroup" id="qg-%1$s"><h2 class="boi-qgroup__title">%2$s</h2><div class="boi-objections boi-objections--list">%3$s</div></section>',
				esc_attr( $key ),
				esc_html( $group['label'] ),
				$items
			);
		}
	}

	if ( '' === $groups || ! $rest_only ) {
		return '' === $groups ? '' : '<div class="boi-faq-questions">' . $groups . '</div>';
	}

	return sprintf(
		'<details class="boi-qmore"><summary class="boi-objections__all">%1$s</summary><div class="boi-qmore__body">%2$s</div></details>',
		esc_html( sprintf( /* translators: %d: number of further questions. */ _n( 'Show %d more question', 'Show %d more questions', $rest, 'bestofislam' ), $rest ) ),
		str_replace( array( '<h2 class="boi-qgroup__title">', '</h2>' ), array( '<h3 class="boi-qgroup__title">', '</h3>' ), $groups )
	);
}

/**
 * Adds every question to the FAQ page, after the page's own text, so the
 * owner can edit the page freely and the list stays current.
 *
 * @param string $content Page content.
 * @return string
 */
function boi_questions_page_content( $content ) {
	if ( ! boi_is_questions_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	return $content . boi_render_question_groups();
}
add_filter( 'the_content', 'boi_questions_page_content', 20 );

/**
 * The panel at the head of an article, naming the questions it answers and
 * linking each to its answer on the front page.
 *
 * @return string
 */
function boi_render_article_questions() {
	$post = get_post();

	if ( ! $post ) {
		return '';
	}

	$links = '';

	foreach ( boi_questions() as $group ) {
		foreach ( $group['items'] as $item ) {
			if ( $item['slug'] === $post->post_name ) {
				$links .= sprintf(
					'<li><a href="%1$s">%2$s</a></li>',
					esc_url( boi_questions_url( boi_question_anchor( $item['q'] ) ) ),
					esc_html( $item['q'] )
				);
			}
		}
	}

	if ( '' === $links ) {
		return '';
	}

	return sprintf(
		'<aside class="boi-answers" aria-label="%1$s"><p class="boi-answers__label">%1$s</p><ul>%2$s</ul><a class="boi-answers__more" href="%3$s">%4$s</a></aside>',
		esc_html__( 'Questions this piece answers', 'bestofislam' ),
		$links,
		esc_url( boi_questions_url() ),
		esc_html__( 'See every question answered', 'bestofislam' )
	);
}

/**
 * The questions answered, as Question nodes for the graph of the FAQ page
 * (inc/schema-graph.php), which is typed FAQPage when there are any. Each
 * has the @id of its anchor on that page.
 *
 * @return array Question nodes.
 */
function boi_questions_schema_nodes() {
	$entities = array();

	foreach ( boi_questions() as $group ) {
		foreach ( $group['items'] as $item ) {
			$post = boi_question_post( $item );

			if ( ! $post ) {
				continue;
			}

			$entities[] = array(
				'@type'          => 'Question',
				'@id'            => boi_questions_url( boi_question_anchor( $item['q'] ) ),
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['a'] . ' ' . get_permalink( $post ),
					'url'   => get_permalink( $post ),
				),
			);
		}
	}

	return $entities;
}
