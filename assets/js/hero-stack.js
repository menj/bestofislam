/**
 * Hero stack: cycles the three numbered cards through several listicles,
 * whose entries travel in the data-sets attribute, and with them the hero
 * background and its credit, each article's own image. The next image is
 * fetched before the switch, so it never paints half loaded. Pauses while the pointer
 * or focus is on the stack and while the tab is hidden, and stays on the
 * first set for readers who prefer reduced motion. No dependencies.
 */
( function () {
	var stack = document.querySelector( '.boi-stack[data-sets]' );

	if ( ! stack || ! window.JSON ) {
		return;
	}

	var sets;

	try {
		sets = JSON.parse( stack.getAttribute( 'data-sets' ) );
	} catch ( e ) {
		return;
	}

	if ( ! sets || sets.length < 2 ) {
		return;
	}

	if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	var index = 0;
	var paused = false;
	var delay = 7000;

	var narrow = window.matchMedia ? window.matchMedia( '(max-width: 781px)' ) : null;
	var root = document.documentElement;

	function imageUrl( set ) {
		if ( ! set.img ) {
			return '';
		}

		return narrow && narrow.matches ? set.img.small : set.img.large;
	}

	function preload( set ) {
		var url = imageUrl( set );

		if ( url ) {
			( new Image() ).src = url;
		}
	}

	function showImage( set ) {
		var url = imageUrl( set );
		var hero = stack.closest( '.boi-hero' );

		if ( ! url || ! hero ) {
			return;
		}

		root.style.setProperty( '--boi-hero-img', 'url("' + url.replace( /"/g, '%22' ) + '")' );

		var credit = hero.querySelector( '.boi-hero__credit' );

		if ( ! credit ) {
			credit = document.createElement( 'p' );
			credit.className = 'boi-hero__credit';
			hero.appendChild( credit );
		}

		credit.textContent = set.img.credit + ' ';

		var link = document.createElement( 'a' );
		link.href = set.img.source;
		link.rel = 'noopener';
		link.textContent = /wikimedia\.org/.test( set.img.source ) ? 'Wikimedia Commons' : 'Source';
		credit.appendChild( link );
	}

	function show( set ) {
		showImage( set );

		var from = stack.querySelector( '.boi-stack__from a' );
		var cards = stack.querySelectorAll( '.boi-stack__card' );

		if ( from ) {
			from.textContent = set.title;
			from.setAttribute( 'href', set.url );
		}

		for ( var i = 0; i < cards.length && i < set.cards.length; i++ ) {
			cards[ i ].setAttribute( 'href', set.cards[ i ].href );
			cards[ i ].querySelector( '.boi-stack__title' ).textContent = set.cards[ i ].title;
		}
	}

	function next() {
		if ( paused || document.hidden ) {
			return;
		}

		index = ( index + 1 ) % sets.length;
		preload( sets[ ( index + 1 ) % sets.length ] );
		stack.classList.add( 'is-changing' );

		window.setTimeout( function () {
			show( sets[ index ] );
			stack.classList.remove( 'is-changing' );
		}, 350 );
	}

	stack.addEventListener( 'mouseenter', function () {
		paused = true;
	} );
	stack.addEventListener( 'mouseleave', function () {
		paused = false;
	} );
	stack.addEventListener( 'focusin', function () {
		paused = true;
	} );
	stack.addEventListener( 'focusout', function () {
		paused = false;
	} );

	preload( sets[ 1 ] );
	window.setInterval( next, delay );
}() );
