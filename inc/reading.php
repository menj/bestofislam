<?php
/**
 * Reading order.
 *
 * The site's articles, arranged as one path in seven parts: what Islam
 * teaches, whether belief is reasonable, the Bible and the Quran, the
 * transmission of scripture, objections about the Prophet, history, and the
 * Muslim world now. The order drives four things: the "Read next" card at the
 * foot of each article, the Start here path, the hero on the front page, and
 * the publish dates the seed assigns, so that the archive reads in sequence.
 *
 * An article missing from the order simply has no "Read next" card.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The reading order, by part.
 *
 * @return array
 */
function boi_reading_order() {
	return array(
		array(
			'label' => __( 'What Islam teaches', 'bestofislam' ),
			'slugs' => array( 'who-is-allah', 'five-pillars-explained', 'why-five-prayers' ),
		),
		array(
			'label' => __( 'Whether belief is reasonable', 'bestofislam' ),
			'slugs' => array( 'universe-demands-creator', 'morality-without-god', 'who-saw-gabriel' ),
		),
		array(
			'label' => __( 'The Bible and the Quran', 'bestofislam' ),
			'slugs' => array( 'same-god', 'shared-prophets', 'jesus-not-god', 'verses-read-differently', 'paul-and-the-law', 'where-is-the-injil', 'islamic-dilemma', 'ezra-and-mary', 'haman-anachronism' ),
		),
		array(
			'label' => __( 'How scripture was transmitted', 'bestofislam' ),
			'slugs' => array( 'manuscript-transmission', 'second-peter', 'uthman-copies', 'quran-preservation-reddit' ),
		),
		array(
			'label' => __( 'Objections about the Prophet', 'bestofislam' ),
			'slugs' => array( 'prophet-died-poisoning', 'al-zutt-allegation' ),
		),
		array(
			'label' => __( 'History and civilisation', 'bestofislam' ),
			'slugs' => array( 'qibla-change', 'kaaba-witnesses', 'dome-inscriptions', 'jizya', 'library-of-alexandria', 'seven-libraries', 'instruments-islamic-world', 'arabic-words-science', 'moon-craters' ),
		),
		array(
			'label' => __( 'The Muslim world today', 'bestofislam' ),
			'slugs' => array( 'islam-religion-of-peace', 'apostasy-in-islam', 'fastest-growing-religion', 'indonesia', 'ex-muslim-blog-study' ),
		),
	);
}

/**
 * The order flattened: slug => array( position, part index, part label ).
 *
 * @return array
 */
function boi_reading_positions() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$map = array();
	$i   = 0;

	foreach ( boi_reading_order() as $p => $part ) {
		foreach ( $part['slugs'] as $slug ) {
			$map[ $slug ] = array( $i++, $p, $part['label'] );
		}
	}

	return $map;
}

/**
 * The published article that follows a slug in the reading order, skipping
 * any that are not published.
 *
 * @param string $slug Current article slug.
 * @return array|null Array( post, part label, part number ) or null.
 */
function boi_next_in_order( $slug ) {
	$map = boi_reading_positions();

	if ( ! isset( $map[ $slug ] ) ) {
		return null;
	}

	$slugs = array_keys( $map );

	for ( $i = $map[ $slug ][0] + 1; $i < count( $slugs ); $i++ ) {
		$post = get_page_by_path( $slugs[ $i ], OBJECT, 'post' );

		if ( $post && 'publish' === $post->post_status && ! boi_is_unlisted( $post ) ) {
			return array( $post, $map[ $slugs[ $i ] ][2], $map[ $slugs[ $i ] ][1] + 1 );
		}
	}

	return null;
}

/**
 * Registers the Read next block.
 *
 * @return void
 */
function boi_register_reading_blocks() {
	register_block_type( 'bestofislam/read-next', array( 'render_callback' => 'boi_render_read_next' ) );
}
add_action( 'init', 'boi_register_reading_blocks' );

/**
 * The Read next card at the foot of an article. At the end of the order it
 * points the reader to the questions answered on the front page instead.
 *
 * @return string
 */
function boi_render_read_next() {
	$post = get_post();

	if ( ! $post || ! isset( boi_reading_positions()[ $post->post_name ] ) ) {
		return '';
	}

	$next = boi_next_in_order( $post->post_name );

	if ( ! $next ) {
		return sprintf(
			'<nav class="boi-next is-end" aria-label="%1$s"><p class="boi-next__label">%2$s</p><a class="boi-next__title" href="%3$s">%4$s</a></nav>',
			esc_attr__( 'Reading order', 'bestofislam' ),
			esc_html__( 'You have reached the end of the reading order', 'bestofislam' ),
			esc_url( home_url( '/#objections' ) ),
			esc_html__( 'See every question answered', 'bestofislam' )
		);
	}

	list( $next_post, $part_label, $part_number ) = $next;

	return sprintf(
		'<nav class="boi-next" aria-label="%1$s"><p class="boi-next__label">%2$s</p><a class="boi-next__title" href="%3$s">%4$s</a><p class="boi-next__part">%5$s</p></nav>',
		esc_attr__( 'Reading order', 'bestofislam' ),
		esc_html__( 'Read next', 'bestofislam' ),
		esc_url( get_permalink( $next_post ) ),
		esc_html( get_the_title( $next_post ) ),
		esc_html( sprintf( /* translators: 1: part number, 2: part name. */ __( 'Part %1$d: %2$s', 'bestofislam' ), $part_number, $part_label ) )
	);
}

/**
 * Staggers the publish dates of seeded articles so that the archive, the
 * featured lead and the Latest grid follow the reading order, one day apart,
 * ending now. Runs once for each revision of the order, and touches only
 * articles whose content is still exactly what the theme wrote: anything the
 * owner has edited keeps its date.
 *
 * @return void
 */
function boi_stagger_seed_dates() {
	$signature = md5( wp_json_encode( boi_reading_order() ) );

	if ( get_option( 'boi_dates_signature' ) === $signature ) {
		return;
	}

	$hashes = (array) get_option( 'boi_article_hashes', array() );
	$slugs  = array_keys( boi_reading_positions() );
	$total  = count( $slugs );
	$end    = time();

	foreach ( $slugs as $i => $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		if ( ! $post || 'publish' !== $post->post_status ) {
			continue;
		}

		if ( ! isset( $hashes[ $slug ] ) || boi_content_fingerprint( $post->post_content ) !== $hashes[ $slug ] ) {
			continue;
		}

		$gmt = gmdate( 'Y-m-d H:i:s', $end - ( $total - 1 - $i ) * DAY_IN_SECONDS );

		boi_update_post(
			array(
				'ID'            => $post->ID,
				'post_date'     => get_date_from_gmt( $gmt ),
				'post_date_gmt' => $gmt,
			)
		);
	}

	update_option( 'boi_dates_signature', $signature, false );
}

/**
 * Registers the reading-order list used in the footer.
 *
 * @return void
 */
function boi_register_reading_parts() {
	register_block_type( 'bestofislam/reading-parts', array( 'render_callback' => 'boi_render_reading_parts' ) );
}
add_action( 'init', 'boi_register_reading_parts' );

/**
 * The seven parts of the reading order as a numbered list, each linking to
 * the first published article of its part. The numbering is the site's
 * countdown motif; the first numeral is gold, as on the mark.
 *
 * @return string
 */
function boi_render_reading_parts() {
	$items = '';

	foreach ( boi_reading_order() as $i => $part ) {
		$url = '';

		foreach ( $part['slugs'] as $slug ) {
			$post = get_page_by_path( $slug, OBJECT, 'post' );

			if ( $post && 'publish' === $post->post_status && ! boi_is_unlisted( $post ) ) {
				$url = get_permalink( $post );
				break;
			}
		}

		if ( '' === $url ) {
			continue;
		}

		$items .= sprintf(
			'<li><a href="%1$s"><span class="boi-parts__num">%2$s</span><span class="boi-parts__label">%3$s</span></a></li>',
			esc_url( $url ),
			esc_html( number_format_i18n( $i + 1 ) ),
			esc_html( $part['label'] )
		);
	}

	return '' === $items ? '' : '<ol class="boi-parts">' . $items . '</ol>';
}
