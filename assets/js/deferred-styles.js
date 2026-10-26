/**
 * Switches on the stylesheets the theme loads as media="print" so that they
 * do not block the first paint (functions.php, boi_defer_style_tag()).
 */
( function () {
	'use strict';

	var links = document.querySelectorAll( 'link[data-boi-defer]' );

	for ( var i = 0; i < links.length; i++ ) {
		links[ i ].media = 'all';
	}
}() );
