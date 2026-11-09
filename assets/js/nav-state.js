/**
 * Mobile menu button: the core navigation block marks its open button with
 * aria-haspopup but never reports whether the menu is open. Mirror the
 * overlay state into aria-expanded so a screen reader announces it.
 */
( function () {
	'use strict';
	if ( ! window.MutationObserver ) {
		return;
	}
	document.querySelectorAll( 'header .wp-block-navigation' ).forEach( function ( nav ) {
		var button = nav.querySelector( '.wp-block-navigation__responsive-container-open' );
		var panel = nav.querySelector( '.wp-block-navigation__responsive-container' );
		if ( ! button || ! panel ) {
			return;
		}
		function sync() {
			button.setAttribute( 'aria-expanded', panel.classList.contains( 'is-menu-open' ) ? 'true' : 'false' );
		}
		sync();
		new MutationObserver( sync ).observe( panel, { attributes: true, attributeFilter: [ 'class' ] } );
	} );
}() );
