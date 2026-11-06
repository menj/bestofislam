/**
 * Reading progress bar: fills the bar printed by inc/reading-progress.php as
 * the reader scrolls. One passive scroll listener schedules at most one
 * update per animation frame; the page height is measured again on resize
 * and whenever the body changes size.
 */
( function () {
	'use strict';

	var bar = document.querySelector( '.boi-progress__fill' );

	if ( ! bar ) {
		return;
	}

	var root = document.documentElement;
	var queued = false;

	function update() {
		queued = false;
		var max = root.scrollHeight - window.innerHeight;
		var ratio = max > 0 ? Math.min( 1, Math.max( 0, window.scrollY / max ) ) : 1;
		bar.style.transform = 'scaleX(' + ratio.toFixed( 4 ) + ')';
	}

	function schedule() {
		if ( ! queued ) {
			queued = true;
			window.requestAnimationFrame( update );
		}
	}

	window.addEventListener( 'scroll', schedule, { passive: true } );
	window.addEventListener( 'resize', schedule, { passive: true } );
	window.addEventListener( 'load', schedule );

	// Images, embeds and late styles change the page height without a
	// scroll or a resize; measure again whenever it does.
	if ( 'ResizeObserver' in window ) {
		new window.ResizeObserver( schedule ).observe( document.body );
	}
	update();
}() );
