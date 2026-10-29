<?php
/**
 * Questions answered.
 *
 * One list of the questions put to Muslims, each with a short answer and the
 * article that argues it in full. The list feeds two places: the questions
 * band on the front page, and a panel at the head of each article naming
 * the questions it answers. The list is data, in inc/data/questions.json;
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
 * Each item: question, answer, answering article slug, and whether it is
 * featured on the front page.
 *
 * @return array
 */
function boi_questions() {
	return boi_read_data( 'questions' );
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
 * Where the questions live: the band on the front page.
 *
 * @return string
 */
function boi_questions_url() {
	return home_url( '/#objections' );
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
 * @param array   $item Question item.
 * @param WP_Post $post Answering article.
 * @param bool    $id   Whether to give the item an anchor.
 * @return string
 */
function boi_question_item( $item, $post, $id = true ) {
	return sprintf(
		'<details class="boi-objection"%1$s><summary><span class="boi-objection__label">%2$s</span><span class="boi-objection__claim">%3$s</span></summary><div class="boi-objection__answer"><p>%4$s</p><a href="%5$s">%6$s</a></div></details>',
		$id ? ' id="' . esc_attr( boi_question_anchor( $item['q'] ) ) . '"' : '',
		esc_html__( 'The question', 'bestofislam' ),
		esc_html( $item['q'] ),
		esc_html( $item['a'] ),
		esc_url( get_permalink( $post ) ),
		esc_html( sprintf( /* translators: %s: title. */ __( 'Full answer: %s', 'bestofislam' ), get_the_title( $post ) ) )
	);
}

/**
 * The front-page band: the featured questions, then the rest grouped by
 * theme behind a single disclosure. Every question carries an anchor, so an
 * article can link straight to its answer.
 *
 * @return string
 */
function boi_render_objections() {
	$featured = '';
	$groups   = '';
	$rest     = 0;

	foreach ( boi_questions() as $key => $group ) {
		$items = '';

		foreach ( $group['items'] as $item ) {
			$post = boi_question_post( $item );

			if ( ! $post ) {
				continue;
			}

			if ( ! empty( $item['feat'] ) ) {
				$featured .= boi_question_item( $item, $post );
			} else {
				$items .= boi_question_item( $item, $post );
				++$rest;
			}
		}

		if ( '' !== $items ) {
			$groups .= sprintf(
				'<section class="boi-qgroup" id="qg-%1$s"><h3 class="boi-qgroup__title">%2$s</h3><div class="boi-objections boi-objections--list">%3$s</div></section>',
				esc_attr( $key ),
				esc_html( $group['label'] ),
				$items
			);
		}
	}

	if ( '' === $featured && '' === $groups ) {
		return '';
	}

	$more = '' === $groups ? '' : sprintf(
		'<details class="boi-qmore"><summary class="boi-objections__all">%1$s</summary><div class="boi-qmore__body">%2$s</div></details>',
		esc_html( sprintf( /* translators: %d: number of further questions. */ _n( 'Show %d more question', 'Show %d more questions', $rest, 'bestofislam' ), $rest ) ),
		$groups
	);

	return '<div class="boi-objections">' . $featured . '</div>' . $more;
}

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
					esc_url( home_url( '/#' . boi_question_anchor( $item['q'] ) ) ),
					esc_html( $item['q'] )
				);
			}
		}
	}

	if ( '' === $links ) {
		return '';
	}

	return sprintf(
		'<aside class="boi-answers"><p class="boi-answers__label">%1$s</p><ul>%2$s</ul><a class="boi-answers__more" href="%3$s">%4$s</a></aside>',
		esc_html__( 'Questions this piece answers', 'bestofislam' ),
		$links,
		esc_url( boi_questions_url() ),
		esc_html__( 'See every question answered', 'bestofislam' )
	);
}

/**
 * FAQPage structured data on the front page, where the answers are.
 *
 * @return void
 */
function boi_print_questions_schema() {
	if ( ! is_front_page() ) {
		return;
	}

	$entities = array();

	foreach ( boi_questions() as $group ) {
		foreach ( $group['items'] as $item ) {
			$post = boi_question_post( $item );

			if ( ! $post ) {
				continue;
			}

			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['a'] . ' ' . get_permalink( $post ),
				),
			);
		}
	}

	if ( empty( $entities ) ) {
		return;
	}

	echo "\n<script type=\"application/ld+json\">" . boi_json_ld(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		)
	) . "</script>\n";
}
add_action( 'wp_head', 'boi_print_questions_schema', 21 );
