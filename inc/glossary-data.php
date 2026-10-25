<?php
/**
 * Glossary entries.
 *
 * The entries live in one JSON file per initial letter under
 * inc/glossary-data/, so they are easy to write and review without PHP
 * escaping. Each entry has: slug, title, type (term, person, prophet, place,
 * event, group, topic or book), Arabic script and transliteration where they
 * apply, a one-line definition (also the page excerpt), the explanation, the
 * common misunderstanding it answers, what the sources say, other names it is
 * listed under (aliases), and links to related terms and articles. A person
 * or prophet may carry an "epithet", the Arabic formula that follows the name
 * (see boi_glossary_epithet_html()), and "epithet_rubric" sets it in red
 * ink. References
 * are to the Quran by chapter and verse and to hadith by collection. Written
 * for this site.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The glossary's entries, in letter order.
 *
 * @return array
 */
function boi_glossary_terms() {
	static $terms = null;

	if ( null !== $terms ) {
		return $terms;
	}

	$terms = array();
	$files = glob( BOI_DIR . '/inc/glossary-data/*.json' );
	sort( $files );

	foreach ( $files as $file ) {
		$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( is_array( $data ) ) {
			$terms = array_merge( $terms, $data );
		}
	}

	return $terms;
}

/**
 * The kinds of entry, for the label on the index.
 *
 * @return array Type => label.
 */
function boi_glossary_types() {
	return array(
		'term'    => __( 'Term', 'bestofislam' ),
		'person'  => __( 'Person', 'bestofislam' ),
		'prophet' => __( 'Prophet', 'bestofislam' ),
		'place'   => __( 'Place', 'bestofislam' ),
		'event'   => __( 'Event', 'bestofislam' ),
		'group'   => __( 'Group', 'bestofislam' ),
		'book'    => __( 'Book', 'bestofislam' ),
		'topic'   => __( 'Topic', 'bestofislam' ),
	);
}
