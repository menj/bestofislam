<?php
/**
 * The endmark: a small tombstone at the end of the last line of an article.
 *
 * Every article on the site ends on a verdict, and the endmark closes it the
 * way a printed essay is closed. The mark is the theme's own: a navy square,
 * the typographer's tombstone, carrying the gold eight-pointed star that
 * marks the divisions of the Quran in manuscripts (۞). It is drawn inline, in
 * the theme's colours, and loads nothing.
 *
 * Where it goes. On a single post that holds the listicle block, at the end of
 * the last paragraph of the main text: after "The bottom line" and before the
 * Notes. It is never placed inside a list, table, quotation, figure, code or
 * FAQ (a Details block), and never on a page, so the FAQ, Terms and Policy,
 * Contact and Sitemap pages and every other page stay without it (SSOT D65).
 *
 * @package BestOfIslam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The tombstone, as inline SVG.
 *
 * @return string
 */
function boi_endmark_svg() {
	return '<svg class="boi-endmark__mark" viewBox="0 0 16 16" width="16" height="16" focusable="false" aria-hidden="true">'
		. '<rect class="boi-endmark__stone" x="0" y="0" width="16" height="16" rx="1.2"/>'
		. '<path class="boi-endmark__star" d="M3.6 3.6h8.8v8.8H3.6z M8 1.78 14.22 8 8 14.22 1.78 8z"/>'
		. '<circle class="boi-endmark__eye" cx="8" cy="8" r="1.7"/>'
		. '</svg>';
}

/**
 * The endmark as it is inserted into the text.
 *
 * @return string
 */
function boi_endmark_html() {
	return '<span class="boi-endmark" aria-hidden="true">' . boi_endmark_svg() . '</span>';
}

/**
 * Whether this the_content call should receive the endmark.
 *
 * @param string $content The content.
 * @return bool
 */
function boi_endmark_applies( $content ) {
	if ( ! is_singular( 'post' ) || ! function_exists( 'boi_anatomy_is_main' ) || ! boi_anatomy_is_main() ) {
		return false;
	}
	if ( function_exists( 'boi_anatomy_on' ) && ! boi_anatomy_on( 'endmark' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || ! has_block( 'bestofislam/listicle', $post ) ) {
		return false;
	}

	return false === strpos( $content, 'class="boi-endmark"' );
}

/**
 * Where the Notes begin, or false.
 *
 * @param string $content The content.
 * @return int|false
 */
function boi_endmark_notes_position( $content ) {
	$found = false;
	foreach ( array( '>Notes</h2>', '>Notes</h3>', 'id="notes"', '<ol class="footnotes"', '<div class="footnotes"', 'class="wp-block-footnotes"' ) as $marker ) {
		$pos = stripos( $content, $marker );
		if ( false !== $pos ) {
			$tag = strrpos( substr( $content, 0, $pos ), '<' );
			$pos = false === $tag ? $pos : $tag;
			if ( false === $found || $pos < $found ) {
				$found = $pos;
			}
		}
	}

	return $found;
}

/**
 * Whether a position lies inside an element that must not carry the mark.
 *
 * @param string $content  The content.
 * @param int    $position Offset of the paragraph.
 * @return bool
 */
function boi_endmark_in_container( $content, $position ) {
	$before = substr( $content, 0, $position );
	foreach ( array( 'table', 'ul', 'ol', 'blockquote', 'figure', 'pre', 'code', 'details', 'aside', 'nav' ) as $tag ) {
		$opens  = preg_match_all( '/<' . $tag . '\b[^>]*>/i', $before );
		$closes = preg_match_all( '/<\/' . $tag . '>/i', $before );
		if ( $opens > $closes ) {
			return true;
		}
	}

	return false;
}

/**
 * Where the endmark goes: just inside the closing tag of the last paragraph
 * with text, before the Notes and outside any protected element.
 *
 * @param string $content The content.
 * @return int|false Offset of the paragraph's closing tag.
 */
function boi_endmark_position( $content ) {
	if ( ! preg_match_all( '/<p\b[^>]*>(.*?)<\/p>/is', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
		return false;
	}
	$notes = boi_endmark_notes_position( $content );
	$last  = -1;
	foreach ( $matches[0] as $i => $match ) {
		$start = $match[1];
		$end   = $start + strlen( $match[0] );
		if ( false !== $notes && $end > $notes ) {
			break;
		}
		if ( boi_endmark_in_container( $content, $start ) ) {
			continue;
		}
		$text = trim( wp_strip_all_tags( str_replace( '&nbsp;', ' ', $matches[1][ $i ][0] ) ) );
		if ( '' !== $text ) {
			$last = $i;
		}
	}
	if ( -1 === $last ) {
		return false;
	}

	return $matches[0][ $last ][1] + strripos( $matches[0][ $last ][0], '</p>' );
}

/**
 * Add the endmark to the main text of an article.
 *
 * @param string $content The content.
 * @return string
 */
function boi_endmark_filter( $content ) {
	if ( ! boi_endmark_applies( $content ) ) {
		return $content;
	}
	$at = boi_endmark_position( $content );
	if ( false === $at ) {
		return $content;
	}

	return substr_replace( $content, boi_endmark_html(), $at, 0 );
}
add_filter( 'the_content', 'boi_endmark_filter', 9999 );
