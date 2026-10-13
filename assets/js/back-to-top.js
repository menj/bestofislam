/**
 * Back to top: a small button that appears once the reader has scrolled a
 * little way down a long page, and takes them to the top.
 */
( function () {
	'use strict';

	var button = document.querySelector( '.boi-totop' );
	if ( ! button ) {
		return;
	}

	var queued = false;

	function update() {
		queued = false;
		var long = document.documentElement.scrollHeight > window.innerHeight * 2.5;
		button.hidden = ! ( long && window.scrollY > window.innerHeight * 0.8 );
	}

	window.addEventListener( 'scroll', function () {
		if ( ! queued ) {
			queued = true;
			window.requestAnimationFrame( update );
		}
	}, { passive: true } );

	window.addEventListener( 'resize', update );
	update();

	button.addEventListener( 'click', function ( e ) {
		e.preventDefault();
		var calm = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		window.scrollTo( { top: 0, behavior: calm ? 'auto' : 'smooth' } );
		var top = document.getElementById( 'top' );
		if ( top ) {
			top.setAttribute( 'tabindex', '-1' );
			top.focus( { preventScroll: true } );
		}
	} );
}() );
