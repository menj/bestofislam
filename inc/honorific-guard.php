<?php
/**
 * Honorific guard: a site plugin that adds an Arabic honorific after the
 * names of prophets and angels cannot tell the archangel from a surname. In
 * a court citation such as "In re Marriage of Gabriel" the name is a
 * family's, so any honorific added after it is removed. Runs last.
 *
 * @package BestOfIslam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes an honorific placed after a surname in a case name.
 *
 * @param string $content Post content.
 * @return string
 */
function boi_honorific_guard( $content ) {
	if ( false === strpos( (string) $content, 'Marriage of' ) ) {
		return $content;
	}
	$out = preg_replace(
		'#(Marriage of\s+(?:<[^>]+>)*Gabriel(?:</em>)?)((?:\s|&nbsp;|</?(?:span|sup|small)[^>]*>|[\x{0600}-\x{06FF}\x{FDF0}-\x{FDFF}\x{FE70}-\x{FEFF}()\x{FD3E}\x{FD3F}])*)(?=</em>|,|\s|$)#u',
		'$1',
		(string) $content
	);
	return null === $out ? $content : $out;
}
add_filter( 'the_content', 'boi_honorific_guard', PHP_INT_MAX );
add_filter( 'the_excerpt', 'boi_honorific_guard', PHP_INT_MAX );
