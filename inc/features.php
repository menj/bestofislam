<?php
/**
 * Article features.
 *
 * Reading time, related pieces, share links, an author card, and a featured
 * lead for the front page. All render server-side; none needs JavaScript.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the feature blocks.
 *
 * @return void
 */
function boi_register_feature_blocks() {
	$blocks = array(
		'reading-time'  => 'boi_render_reading_time',
		'related'       => 'boi_render_related',
		'share'         => 'boi_render_share',
		'author-card'   => 'boi_render_author_card',
		'featured-lead' => 'boi_render_featured_lead',
	);

	foreach ( $blocks as $name => $callback ) {
		register_block_type( 'bestofislam/' . $name, array( 'render_callback' => $callback ) );
	}
}
add_action( 'init', 'boi_register_feature_blocks' );

/**
 * Estimates reading time at 220 words a minute.
 *
 * @return string
 */
function boi_render_reading_time() {
	$post = get_post();

	if ( ! $post ) {
		return '';
	}

	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	$minutes = max( 1, (int) round( $words / 220 ) );

	return sprintf(
		'<span class="boi-reading-time">%s</span>',
		esc_html( sprintf( /* translators: %d: minutes. */ _n( '%d min read', '%d min read', $minutes, 'bestofislam' ), $minutes ) )
	);
}

/**
 * Renders up to three pieces from the same section.
 *
 * @return string
 */
function boi_render_related() {
	$post_id = get_the_ID();
	$terms   = get_the_terms( $post_id, 'listicle-topic' );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}

	$related = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post__not_in'   => array( $post_id ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'listicle-topic',
					'field'    => 'term_id',
					'terms'    => wp_list_pluck( $terms, 'term_id' ),
				),
			),
		)
	);

	if ( empty( $related ) ) {
		return '';
	}

	$items = '';

	foreach ( $related as $item ) {
		$thumb = get_the_post_thumbnail( $item, 'medium_large', array( 'loading' => 'lazy' ) );

		if ( '' === $thumb ) {
			$thumb = boi_featured_image_fallback( '', $item->ID );
		}

		$items .= sprintf(
			'<li class="boi-related__item">%1$s<a class="boi-related__link" href="%2$s">%3$s</a></li>',
			$thumb,
			esc_url( get_permalink( $item ) ),
			esc_html( get_the_title( $item ) )
		);
	}

	return sprintf(
		'<section class="boi-related" aria-labelledby="boi-related-heading"><h2 id="boi-related-heading" class="boi-related__heading">%1$s</h2><ul class="boi-related__list">%2$s</ul></section>',
		esc_html__( 'More in this section', 'bestofislam' ),
		$items
	);
}

/**
 * Renders plain share links. No scripts, no tracking.
 *
 * @return string
 */
function boi_render_share() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( wp_strip_all_tags( get_the_title() ) );

	$links = array(
		array( 'x', 'X', 'https://x.com/intent/post?url=' . $url . '&text=' . $title ),
		array( 'facebook', 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
		array( 'whatsapp', 'WhatsApp', 'https://wa.me/?text=' . $title . '%20' . $url ),
		array( 'telegram', 'Telegram', 'https://t.me/share/url?url=' . $url . '&text=' . $title ),
		array( 'email', __( 'Email', 'bestofislam' ), 'mailto:?subject=' . $title . '&body=' . $url ),
	);

	$items = '';

	foreach ( $links as $link ) {
		$items .= sprintf(
			'<a class="boi-share__link" href="%1$s" rel="noopener nofollow" target="_blank" aria-label="%2$s" title="%2$s">%3$s</a>',
			esc_url( $link[2] ),
			esc_attr( 'email' === $link[0] ? __( 'Share by email', 'bestofislam' ) : sprintf( /* translators: %s: network name. */ __( 'Share on %s', 'bestofislam' ), $link[1] ) ),
			boi_icon( $link[0] )
		);
	}

	return sprintf(
		'<div class="boi-share"><span class="boi-share__label">%1$s</span>%2$s</div>',
		esc_html__( 'Share', 'bestofislam' ),
		$items
	);
}

/**
 * Renders the author card beneath a post.
 *
 * @return string
 */
function boi_render_author_card() {
	$post = get_post();

	if ( ! $post ) {
		return '';
	}

	$id   = (int) $post->post_author;
	$bio  = get_the_author_meta( 'description', $id );
	$name = get_the_author_meta( 'display_name', $id );

	return sprintf(
		'<aside class="boi-author"><div class="boi-author__avatar">%1$s</div><div class="boi-author__body"><p class="boi-author__label">%2$s</p><p class="boi-author__name"><a href="%3$s">%4$s</a></p>%5$s</div></aside>',
		get_avatar( $id, 72 ),
		esc_html__( 'Written by', 'bestofislam' ),
		esc_url( get_author_posts_url( $id ) ),
		esc_html( $name ),
		'' !== $bio ? '<p class="boi-author__bio">' . esc_html( $bio ) . '</p>' : ''
	);
}

/**
 * Renders the most recent piece as a wide lead card on the front page.
 *
 * @return string
 */
function boi_render_featured_lead() {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
		)
	);

	if ( empty( $posts ) ) {
		return '';
	}

	$post  = $posts[0];
	$terms = get_the_terms( $post->ID, 'listicle-topic' );
	$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$thumb = get_the_post_thumbnail( $post, 'large' );

	if ( '' === $thumb ) {
		$thumb = boi_featured_image_fallback( '', $post->ID );
	}

	return sprintf(
		'<article class="boi-lead alignwide">
			<div class="boi-lead__media">%1$s</div>
			<div class="boi-lead__body">
				%2$s
				<h2 class="boi-lead__title"><a href="%3$s">%4$s</a></h2>
				<p class="boi-lead__excerpt">%5$s</p>
				<a class="boi-lead__cta" href="%3$s">%6$s</a>
			</div>
		</article>',
		$thumb,
		$term ? sprintf( '<a class="boi-lead__kicker" href="%1$s">%2$s</a>', esc_url( get_term_link( $term ) ), esc_html( $term->name ) ) : '',
		esc_url( get_permalink( $post ) ),
		esc_html( get_the_title( $post ) ),
		esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ),
		esc_html__( 'Read the argument', 'bestofislam' )
	);
}

/**
 * Registers the front-page modules that do not depend on post count.
 *
 * @return void
 */
function boi_register_front_modules() {
	register_block_type( 'bestofislam/reflections-strip', array( 'render_callback' => 'boi_render_reflections_strip' ) );
	register_block_type( 'bestofislam/mission', array( 'render_callback' => 'boi_render_mission' ) );
	register_block_type( 'bestofislam/follow', array( 'render_callback' => 'boi_render_follow' ) );
}
add_action( 'init', 'boi_register_front_modules' );

/**
 * Renders the three most recent Reflections, or nothing if there are none.
 *
 * @return string
 */
function boi_render_reflections_strip() {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'meta_query'     => array(
				array(
					'key'     => BOI_LISTICLE_META,
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	if ( empty( $posts ) ) {
		return '';
	}

	$items = '';

	foreach ( $posts as $post ) {
		$items .= sprintf(
			'<li class="boi-strip__item"><a href="%1$s"><span class="boi-strip__title">%2$s</span><span class="boi-strip__date">%3$s</span></a></li>',
			esc_url( get_permalink( $post ) ),
			esc_html( get_the_title( $post ) ),
			esc_html( get_the_date( '', $post ) )
		);
	}

	return sprintf(
		'<section class="boi-strip alignwide"><div class="boi-strip__head"><h2 class="boi-eyebrow">%1$s</h2><a class="boi-strip__more" href="%2$s">%3$s</a></div><ul class="boi-strip__list">%4$s</ul></section>',
		esc_html__( 'Reflections', 'bestofislam' ),
		esc_url( get_permalink( get_option( 'page_for_posts' ) ) ),
		esc_html__( 'All reflections', 'bestofislam' ),
		$items
	);
}

/**
 * Renders the mission band.
 *
 * @return string
 */
function boi_render_mission() {
	$about = get_page_by_path( 'about', OBJECT, 'page' );

	return sprintf(
		'<section class="boi-mission alignwide">
			<div class="boi-mission__inner">
				<p class="boi-mission__label">%1$s</p>
				<h2 class="boi-mission__title">%2$s</h2>
				<p class="boi-mission__text">%3$s</p>
				%4$s
			</div>
		</section>',
		esc_html__( 'What this site is for', 'bestofislam' ),
		esc_html__( 'Every objection stated in its own terms, then answered from the sources.', 'bestofislam' ),
		esc_html__( 'Best of Islam takes the arguments made against Islam seriously enough to quote them accurately, and answers them from the Quran, the hadith, the historical record and the scholarly tradition. Each piece is a ranked list: one objection per entry, one answer per objection, with the sources named.', 'bestofislam' ),
		$about ? sprintf( '<a class="boi-mission__cta" href="%1$s">%2$s</a>', esc_url( get_permalink( $about ) ), esc_html__( 'About the site', 'bestofislam' ) ) : ''
	);
}

/**
 * Renders follow links: RSS, plus any channels set in the theme options.
 *
 * @return string
 */
function boi_render_follow() {
	$items = '';

	foreach ( boi_follow_links() as $link ) {
		$items .= sprintf(
			'<a class="boi-follow__link" href="%1$s" rel="%2$s">%3$s<span>%4$s</span></a>',
			esc_url( $link[2] ),
			'mastodon' === $link[0] ? 'me noopener' : 'noopener',
			boi_icon( $link[0] ),
			esc_html( $link[1] )
		);
	}

	return sprintf(
		'<section class="boi-follow alignwide"><h2 class="boi-eyebrow">%1$s</h2><p class="boi-follow__text">%2$s</p><div class="boi-follow__links">%3$s</div></section>',
		esc_html__( 'Follow', 'bestofislam' ),
		esc_html__( 'New pieces are published as they are finished. Follow wherever suits you.', 'bestofislam' ),
		$items
	);
}

/**
 * Registers the footer notice.
 *
 * @return void
 */
function boi_register_footer_notice() {
	register_block_type( 'bestofislam/footer-notice', array( 'render_callback' => 'boi_render_footer_notice' ) );
}
add_action( 'init', 'boi_register_footer_notice' );

/**
 * The notice at the foot of every page: the current year and site name, the
 * terms on which articles may be quoted, the source of the images, and a
 * link back to the top. The year is rendered at request time, so it never
 * goes stale.
 *
 * @return string
 */
function boi_render_footer_notice() {
	$sources = get_page_by_path( 'sources-and-standards', OBJECT, 'page' );

	$images = $sources
		? sprintf(
			/* translators: %s: link to the Sources and standards page. */
			esc_html__( 'Images are in the public domain, credited on each page and in %s.', 'bestofislam' ),
			'<a href="' . esc_url( get_permalink( $sources ) ) . '">' . esc_html__( 'Sources and standards', 'bestofislam' ) . '</a>'
		)
		: esc_html__( 'Images are in the public domain and credited on each page.', 'bestofislam' );

	return sprintf(
		'<div class="boi-notice"><p class="boi-notice__text">%1$s %2$s %3$s</p><a class="boi-notice__top" href="#top">%4$s</a></div>',
		esc_html( sprintf( /* translators: 1: year, 2: site name. */ __( '© %1$s %2$s.', 'bestofislam' ), wp_date( 'Y' ), get_bloginfo( 'name' ) ) ),
		esc_html__( 'Articles may be quoted with attribution and a link.', 'bestofislam' ),
		$images,
		esc_html__( 'Back to top', 'bestofislam' )
	);
}

/**
 * Gives the page an anchor for the Back to top link.
 *
 * @return void
 */
function boi_top_anchor() {
	echo '<span id="top" class="screen-reader-text"></span>';
}
add_action( 'wp_body_open', 'boi_top_anchor' );
