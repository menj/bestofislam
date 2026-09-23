<?php
/**
 * Search.
 *
 * Three departures from the WordPress default. Results are ordered by where
 * the term matched instead of by date. Listicle entries are matched
 * individually, so a result can point at the entry that answers the query
 * rather than at the top of the post. And results can be narrowed by section
 * or by kind.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Restricts search to posts and pages, and applies the filters.
 *
 * @param WP_Query $query The query.
 * @return void
 */
function boi_search_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'post_type', array( 'post', 'page' ) );

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$kind = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : '';

	if ( 'lists' === $kind ) {
		$query->set(
			'meta_query',
			array(
				array(
					'key'     => BOI_LISTICLE_META,
					'compare' => 'EXISTS',
				),
			)
		);
	} elseif ( 'reflections' === $kind ) {
		$query->set(
			'meta_query',
			array(
				array(
					'key'     => BOI_LISTICLE_META,
					'compare' => 'NOT EXISTS',
				),
			)
		);
	}

	$section = isset( $_GET['section'] ) ? sanitize_title( wp_unslash( $_GET['section'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	if ( '' !== $section ) {
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => 'listicle-topic',
					'field'    => 'slug',
					'terms'    => $section,
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'boi_search_query' );

/**
 * Orders results by where the term matched.
 *
 * An exact title match outranks a title that contains the term, which outranks
 * an excerpt match, which outranks a body match. Date breaks ties.
 *
 * @param string   $orderby The ORDER BY clause.
 * @param WP_Query $query   The query.
 * @return string
 */
function boi_search_relevance( $orderby, $query ) {
	global $wpdb;

	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $orderby;
	}

	$term = $query->get( 's' );

	if ( '' === $term ) {
		return $orderby;
	}

	$exact = $wpdb->prepare( '%s', $term );
	$like  = '%' . $wpdb->esc_like( $term ) . '%';
	$like  = $wpdb->prepare( '%s', $like );

	return "CASE
		WHEN {$wpdb->posts}.post_title = {$exact} THEN 1
		WHEN {$wpdb->posts}.post_title LIKE {$like} THEN 2
		WHEN {$wpdb->posts}.post_excerpt LIKE {$like} THEN 3
		ELSE 4
	END ASC, {$wpdb->posts}.post_date DESC";
}
add_filter( 'posts_orderby', 'boi_search_relevance', 10, 2 );

/**
 * Finds listicle entries in a post whose title or body matches the term.
 *
 * @param int    $post_id Post identifier.
 * @param string $term    Search term.
 * @param int    $limit   Maximum entries to return.
 * @return array List of arrays with title and anchor.
 */
function boi_matching_entries( $post_id, $term, $limit = 3 ) {
	$post = get_post( $post_id );

	if ( ! $post || '' === $term ) {
		return array();
	}

	$needle  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $term ) : strtolower( $term );
	$matches = array();

	foreach ( boi_find_listicles( parse_blocks( $post->post_content ) ) as $listicle ) {
		foreach ( $listicle['innerBlocks'] as $inner ) {
			if ( 'bestofislam/entry' !== $inner['blockName'] ) {
				continue;
			}

			$attrs = isset( $inner['attrs'] ) ? $inner['attrs'] : array();
			$title = isset( $attrs['title'] ) ? wp_strip_all_tags( $attrs['title'] ) : '';

			if ( '' === $title ) {
				continue;
			}

			$body = wp_strip_all_tags( render_block( $inner ) );
			$hay  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title . ' ' . $body ) : strtolower( $title . ' ' . $body );

			if ( false === strpos( $hay, $needle ) ) {
				continue;
			}

			$matches[] = array(
				'title'  => $title,
				'anchor' => boi_entry_anchor( $attrs ),
			);

			if ( count( $matches ) >= $limit ) {
				return $matches;
			}
		}
	}

	return $matches;
}

/**
 * Renders the matching entries beneath a search result.
 *
 * @return string
 */
function boi_render_entry_matches() {
	if ( ! is_search() ) {
		return '';
	}

	$entries = boi_matching_entries( get_the_ID(), get_search_query(), 3 );

	if ( empty( $entries ) ) {
		return '';
	}

	$items = '';

	foreach ( $entries as $entry ) {
		$items .= sprintf(
			'<li><a href="%1$s#%2$s">%3$s</a></li>',
			esc_url( get_permalink() ),
			esc_attr( $entry['anchor'] ),
			esc_html( $entry['title'] )
		);
	}

	return sprintf(
		'<div class="boi-result__entries"><p class="boi-result__label">%1$s</p><ul>%2$s</ul></div>',
		esc_html__( 'Matching entries', 'bestofislam' ),
		$items
	);
}

/**
 * Registers the block that renders the filters and the entry matches.
 *
 * @return void
 */
function boi_register_search_blocks() {
	register_block_type(
		'bestofislam/search-filters',
		array(
			'render_callback' => 'boi_render_search_filters',
			'skip_inner_blocks' => true,
		)
	);

	register_block_type(
		'bestofislam/entry-matches',
		array( 'render_callback' => 'boi_render_entry_matches' )
	);
}
add_action( 'init', 'boi_register_search_blocks' );

/**
 * Renders the section and kind filters, preserving the current query.
 *
 * @return string
 */
function boi_render_search_filters() {
	if ( ! is_search() ) {
		return '';
	}

	$term = get_search_query();
	$base = get_search_link( $term );

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$kind    = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : '';
	$section = isset( $_GET['section'] ) ? sanitize_title( wp_unslash( $_GET['section'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$kinds = array(
		''            => __( 'Everything', 'bestofislam' ),
		'lists'       => __( 'Lists', 'bestofislam' ),
		'reflections' => __( 'Reflections', 'bestofislam' ),
	);

	$chips = '';

	foreach ( $kinds as $value => $label ) {
		$url = $base;

		if ( '' !== $value ) {
			$url = add_query_arg( 'kind', $value, $url );
		}

		if ( '' !== $section ) {
			$url = add_query_arg( 'section', $section, $url );
		}

		$chips .= sprintf(
			'<a class="boi-chip%1$s" href="%2$s">%3$s</a>',
			$value === $kind ? ' is-active' : '',
			esc_url( $url ),
			esc_html( $label )
		);
	}

	$terms  = get_terms(
		array(
			'taxonomy'   => 'listicle-topic',
			'hide_empty' => true,
			'parent'     => 0,
		)
	);
	$second = '';

	if ( ! is_wp_error( $terms ) && $terms ) {
		$url     = '' !== $kind ? add_query_arg( 'kind', $kind, $base ) : $base;
		$second .= sprintf(
			'<a class="boi-chip%1$s" href="%2$s">%3$s</a>',
			'' === $section ? ' is-active' : '',
			esc_url( $url ),
			esc_html__( 'All sections', 'bestofislam' )
		);

		foreach ( $terms as $t ) {
			$url = add_query_arg( 'section', $t->slug, '' !== $kind ? add_query_arg( 'kind', $kind, $base ) : $base );

			$second .= sprintf(
				'<a class="boi-chip%1$s" href="%2$s">%3$s</a>',
				$t->slug === $section ? ' is-active' : '',
				esc_url( $url ),
				esc_html( $t->name )
			);
		}
	}

	return sprintf(
		'<div class="boi-filters"><div class="boi-filters__row">%1$s</div><div class="boi-filters__row">%2$s</div></div>',
		$chips,
		$second
	);
}
