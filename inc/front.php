<?php
/**
 * Front-page modules.
 *
 * The front page is a sequence of full-bleed bands, each with one job, in the
 * manner of the Abrahamic theme. Every module here renders from live data, so
 * the page fills as the archive grows and never shows a hard-coded count.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the front-page blocks.
 *
 * @return void
 */
function boi_register_front_blocks() {
	$blocks = array(
		'hero-stack'   => 'boi_render_hero_stack',
		'stats'        => 'boi_render_stats',
		'picks'        => 'boi_render_picks',
		'objections'   => 'boi_render_objections',
		'start-here'   => 'boi_render_start_here',
		'html-sitemap' => 'boi_render_html_sitemap',
	);

	foreach ( $blocks as $name => $callback ) {
		register_block_type( 'bestofislam/' . $name, array( 'render_callback' => $callback ) );
	}
}
add_action( 'init', 'boi_register_front_blocks' );

/**
 * Hero visual: the first three entries of the newest listicle, stacked as
 * numbered cards. It shows the format before the reader has scrolled.
 *
 * @return string
 */
function boi_render_hero_stack() {
	$post = null;

	// The hero opens the reading order, so a first visit starts where the site starts.
	if ( function_exists( 'boi_reading_positions' ) ) {
		foreach ( array_keys( boi_reading_positions() ) as $slug ) {
			$candidate = get_page_by_path( $slug, OBJECT, 'post' );

			if ( $candidate && 'publish' === $candidate->post_status && ! boi_is_unlisted( $candidate ) ) {
				$post = $candidate;
				break;
			}
		}
	}

	if ( ! $post ) {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => BOI_LISTICLE_META,
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( empty( $posts ) ) {
			return '';
		}

		$post = $posts[0];
	}


	$entries = array();

	foreach ( boi_find_listicles( parse_blocks( $post->post_content ) ) as $listicle ) {
		foreach ( $listicle['innerBlocks'] as $inner ) {
			if ( 'bestofislam/entry' === $inner['blockName'] && ! empty( $inner['attrs']['title'] ) ) {
				$entries[] = $inner['attrs'];
			}

			if ( count( $entries ) >= 3 ) {
				break 2;
			}
		}
	}

	if ( empty( $entries ) ) {
		return '';
	}

	$cards = '';

	foreach ( $entries as $i => $entry ) {
		$cards .= sprintf(
			'<a class="boi-stack__card" href="%1$s#%2$s" style="--i:%3$d"><span class="boi-stack__num">%4$s</span><span class="boi-stack__title">%5$s</span></a>',
			esc_url( get_permalink( $post ) ),
			esc_attr( boi_entry_anchor( $entry ) ),
			(int) $i,
			esc_html( number_format_i18n( $i + 1 ) ),
			esc_html( wp_strip_all_tags( $entry['title'] ) )
		);
	}

	return sprintf(
		'<div class="boi-stack"><p class="boi-stack__from">%1$s <a href="%2$s">%3$s</a></p>%4$s</div>',
		esc_html__( 'From', 'bestofislam' ),
		esc_url( get_permalink( $post ) ),
		esc_html( get_the_title( $post ) ),
		$cards
	);
}

/**
 * Counts published listicles, entries and sections. Cached for twelve hours
 * and cleared whenever a post is saved.
 *
 * @return array
 */
function boi_site_counts() {
	$cached = get_transient( 'boi_site_counts' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$ids = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$entries   = 0;
	$listicles = 0;

	foreach ( $ids as $id ) {
		$n = substr_count( (string) get_post_field( 'post_content', $id ), '<!-- wp:bestofislam/entry ' );

		if ( $n > 0 ) {
			++$listicles;
			$entries += $n;
		}
	}

	$sections = get_terms(
		array(
			'taxonomy'   => 'listicle-topic',
			'hide_empty' => false,
			'parent'     => 0,
			'fields'     => 'count',
		)
	);

	$counts = array(
		'pieces'   => count( $ids ),
		'lists'    => $listicles,
		'entries'  => $entries,
		'sections' => is_wp_error( $sections ) ? 0 : (int) $sections,
	);

	set_transient( 'boi_site_counts', $counts, 12 * HOUR_IN_SECONDS );

	return $counts;
}

/**
 * Clears the counts cache.
 *
 * @return void
 */
function boi_flush_site_counts() {
	delete_transient( 'boi_site_counts' );
}
add_action( 'save_post_post', 'boi_flush_site_counts' );
add_action( 'deleted_post', 'boi_flush_site_counts' );

/**
 * Renders the stats strip.
 *
 * @return string
 */
function boi_render_stats() {
	$c     = boi_site_counts();
	$items = array(
		array( $c['pieces'], __( 'pieces published', 'bestofislam' ) ),
		array( $c['entries'], __( 'objections and answers', 'bestofislam' ) ),
		array( $c['sections'], __( 'sections', 'bestofislam' ) ),
		array( __( '0', 'bestofislam' ), __( 'ads or trackers', 'bestofislam' ) ),
	);

	$html = '';

	foreach ( $items as $item ) {
		$html .= sprintf(
			'<li class="boi-stats__item"><span class="boi-stats__num">%1$s</span><span class="boi-stats__label">%2$s</span></li>',
			esc_html( is_numeric( $item[0] ) ? number_format_i18n( (int) $item[0] ) : $item[0] ),
			esc_html( $item[1] )
		);
	}

	return '<ul class="boi-stats">' . $html . '</ul>';
}

/**
 * Editor's picks. Slugs are set on the Display tab; without them the three
 * listicles with the most entries stand in.
 *
 * @return string
 */
function boi_render_picks() {
	$options = boi_get_options();
	$slugs   = array_filter( array_map( 'sanitize_title', explode( ',', (string) $options['picks'] ) ) );
	$posts   = array();

	foreach ( $slugs as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		if ( $post && 'publish' === $post->post_status && ! boi_is_unlisted( $post ) ) {
			$posts[] = $post;
		}
	}

	if ( empty( $posts ) ) {
		$all = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'meta_query'     => array(
					array(
						'key'     => BOI_LISTICLE_META,
						'compare' => 'EXISTS',
					),
				),
			)
		);

		usort(
			$all,
			function ( $a, $b ) {
				return substr_count( $b->post_content, '<!-- wp:bestofislam/entry ' ) <=> substr_count( $a->post_content, '<!-- wp:bestofislam/entry ' );
			}
		);

		$posts = array_slice( $all, 0, 3 );
	}

	if ( empty( $posts ) ) {
		return '';
	}

	$items = '';

	foreach ( array_slice( $posts, 0, 3 ) as $i => $post ) {
		$terms = get_the_terms( $post->ID, 'listicle-topic' );
		$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
		$n     = substr_count( $post->post_content, '<!-- wp:bestofislam/entry ' );

		$items .= sprintf(
			'<li class="boi-picks__item"><span class="boi-picks__rank">%1$s</span><div class="boi-picks__body"><p class="boi-picks__term">%2$s</p><a class="boi-picks__title" href="%3$s">%4$s</a><p class="boi-picks__meta">%5$s</p></div></li>',
			esc_html( '0' . ( $i + 1 ) ),
			esc_html( $term ),
			esc_url( get_permalink( $post ) ),
			esc_html( get_the_title( $post ) ),
			esc_html( sprintf( /* translators: %d: entries. */ _n( '%d entry', '%d entries', $n, 'bestofislam' ), $n ) )
		);
	}

	return '<ol class="boi-picks">' . $items . '</ol>';
}

/**
 * A three-step path for a first visit: the opening articles of the first
 * three parts of the reading order, where the rest of each part follows by
 * way of the Read next card.
 *
 * @return string
 */
function boi_render_start_here() {
	$steps = array(
		array( __( 'Start with what Islam teaches', 'bestofislam' ), __( 'The name of God, the pillars and the prayer, explained without jargon.', 'bestofislam' ) ),
		array( __( 'Then test it against reason', 'bestofislam' ), __( 'The arguments for belief, and the atheist objections answered in their own terms.', 'bestofislam' ) ),
		array( __( 'Then set it beside the Bible', 'bestofislam' ), __( 'Where the Quran and the Bible agree, where they part, and why.', 'bestofislam' ) ),
	);

	$order = function_exists( 'boi_reading_order' ) ? boi_reading_order() : array();
	$items = '';

	foreach ( $steps as $i => $step ) {
		$url   = home_url( '/' );
		$first = '';

		if ( isset( $order[ $i ] ) ) {
			foreach ( $order[ $i ]['slugs'] as $slug ) {
				$post = get_page_by_path( $slug, OBJECT, 'post' );

				if ( $post && 'publish' === $post->post_status && ! boi_is_unlisted( $post ) ) {
					$url   = get_permalink( $post );
					$first = get_the_title( $post );
					break;
				}
			}
		}

		$items .= sprintf(
			'<li class="boi-path__step"><span class="boi-path__num">%1$s</span><h3 class="boi-path__title"><a href="%2$s">%3$s</a></h3><p class="boi-path__text">%4$s</p>%5$s</li>',
			esc_html( number_format_i18n( $i + 1 ) ),
			esc_url( $url ),
			esc_html( $step[0] ),
			esc_html( $step[1] ),
			'' !== $first ? '<p class="boi-path__first">' . esc_html( sprintf( /* translators: %s: article title. */ __( 'Begins with: %s', 'bestofislam' ), $first ) ) . '</p>' : ''
		);
	}

	return '<ol class="boi-path">' . $items . '</ol>';
}

/**
 * HTML site map, as the Search Engine Optimization Starter Guide recommends:
 * every section with its pieces, then the standing pages.
 *
 * @return string
 */
function boi_render_html_sitemap() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'listicle-topic',
			'hide_empty' => false,
			'parent'     => 0,
			'orderby'    => 'term_id',
		)
	);

	$html = '';

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => -1,
					'tax_query'      => array(
						array(
							'taxonomy'         => 'listicle-topic',
							'field'            => 'term_id',
							'terms'            => $term->term_id,
							'include_children' => true,
						),
					),
				)
			);

			$list = '';

			foreach ( $posts as $post ) {
				$list .= sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink( $post ) ), esc_html( get_the_title( $post ) ) );
			}

			$html .= sprintf(
				'<section class="boi-sitemap__group"><h2><a href="%1$s">%2$s</a></h2>%3$s</section>',
				esc_url( get_term_link( $term ) ),
				esc_html( $term->name ),
				'' !== $list ? '<ul>' . $list . '</ul>' : '<p>' . esc_html__( 'First pieces coming.', 'bestofislam' ) . '</p>'
			);
		}
	}

	$pages = get_pages( array( 'sort_column' => 'menu_order,post_title' ) );
	$list  = '';

	foreach ( $pages as $page ) {
		$list .= sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink( $page ) ), esc_html( get_the_title( $page ) ) );
	}

	$html .= sprintf( '<section class="boi-sitemap__group"><h2>%1$s</h2><ul>%2$s</ul></section>', esc_html__( 'Pages', 'bestofislam' ), $list );

	return '<div class="boi-sitemap">' . $html . '</div>';
}

/**
 * The article whose image sits behind the hero, or null for none.
 *
 * The Display tab chooses the source: a named article's featured image, the
 * newest article's, or none. The default is the Birmingham Quran leaves from
 * "Manuscript transmission", chosen because the script sits quietly under the
 * overlay and keeps the headline legible.
 *
 * @return WP_Post|null
 */
function boi_hero_post() {
	$options = boi_get_options();
	$choice  = isset( $options['hero_image'] ) ? (string) $options['hero_image'] : '';

	if ( 'none' === $choice ) {
		return null;
	}

	if ( 'newest' === $choice ) {
		$posts = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 1 ) );

		return $posts ? $posts[0] : null;
	}

	$post = get_page_by_path( '' !== $choice ? $choice : 'manuscript-transmission', OBJECT, 'post' );

	return ( $post && 'publish' === $post->post_status ) ? $post : null;
}

/**
 * The hero background: image URLs for wide and narrow screens, and a credit.
 * Uses the attached featured image when there is one, and the image bundled
 * with the theme when there is not yet.
 *
 * @return array|null Array( large, small, artist, licence, source ), or null.
 */
function boi_hero_image() {
	static $hero = false;

	if ( false !== $hero ) {
		return $hero;
	}

	$hero = null;
	$post = boi_hero_post();

	if ( ! $post ) {
		return $hero;
	}

	$id = (int) get_post_thumbnail_id( $post );

	if ( $id && wp_get_attachment_image_url( $id, 'full' ) ) {
		$credit = get_post_meta( $id, '_boi_credit', true );
		$hero   = array(
			'large'   => wp_get_attachment_image_url( $id, 'full' ),
			'small'   => wp_get_attachment_image_url( $id, 'large' ),
			'artist'  => is_array( $credit ) && ! empty( $credit['artist'] ) ? $credit['artist'] : '',
			'licence' => is_array( $credit ) && ! empty( $credit['licence'] ) ? $credit['licence'] : '',
			'source'  => is_array( $credit ) && ! empty( $credit['source'] ) ? $credit['source'] : '',
		);
		return $hero;
	}

	$bundled = function_exists( 'boi_bundled_image' ) ? boi_bundled_image( $post ) : null;

	if ( $bundled ) {
		$hero = array(
			'large'   => $bundled['url'],
			'small'   => $bundled['url'],
			'artist'  => $bundled['artist'],
			'licence' => $bundled['licence'],
			'source'  => $bundled['source'],
		);
	}

	return $hero;
}

/**
 * Sets the hero background as a custom property on the front page, with a
 * smaller file for narrow screens where one exists.
 *
 * @return void
 */
function boi_print_hero_background() {
	if ( ! is_front_page() ) {
		return;
	}

	$hero = boi_hero_image();

	if ( ! $hero ) {
		return;
	}

	printf(
		"<style id=\"boi-hero-bg\">:root{--boi-hero-img:url(%1\$s)}@media (max-width:781px){:root{--boi-hero-img:url(%2\$s)}}</style>\n",
		esc_url( $hero['large'] ),
		esc_url( $hero['small'] ? $hero['small'] : $hero['large'] )
	);
}
add_action( 'wp_head', 'boi_print_hero_background', 20 );

/**
 * Flags the front page when the hero has an image.
 *
 * @param array $classes Body classes.
 * @return array
 */
function boi_hero_body_class( $classes ) {
	if ( is_front_page() && boi_hero_image() ) {
		$classes[] = 'boi-has-hero-image';
	}

	return $classes;
}
add_filter( 'body_class', 'boi_hero_body_class' );

/**
 * Registers the hero credit block.
 *
 * @return void
 */
function boi_register_hero_credit() {
	register_block_type( 'bestofislam/hero-credit', array( 'render_callback' => 'boi_render_hero_credit' ) );
}
add_action( 'init', 'boi_register_hero_credit' );

/**
 * A small credit for the hero image, in the band's lower corner, taken from
 * the same record as the credit beneath the image on its article.
 *
 * @return string
 */
function boi_render_hero_credit() {
	$hero = boi_hero_image();

	if ( ! $hero || '' === $hero['source'] ) {
		return '';
	}

	return sprintf(
		'<p class="boi-hero__credit">%1$s <a href="%2$s" rel="noopener">%3$s</a></p>',
		esc_html( sprintf( /* translators: 1: artist, 2: licence. */ __( 'Background: %1$s. %2$s,', 'bestofislam' ), $hero['artist'], $hero['licence'] ) ),
		esc_url( $hero['source'] ),
		esc_html__( 'Wikimedia Commons', 'bestofislam' )
	);
}
