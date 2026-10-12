/**
 * Header search overlay.
 *
 * The search icon in the header opens an overlay holding the search box. Esc,
 * the close button or a click outside the box closes it, focus moves to the
 * field on opening and back to the icon on closing, and Tab stays inside while
 * it is open. The "/" key opens it from anywhere outside a text field.
 */
( function () {
	'use strict';

	var overlay = document.getElementById( 'boi-search' );
	var toggle = document.querySelector( '.boi-search-toggle' );

	if ( ! overlay || ! toggle ) {
		return;
	}

	var closeButton = overlay.querySelector( '.boi-search-overlay__close' );
	var panel = overlay.querySelector( '.boi-search-overlay__panel' );

	/**
	 * Controls that can take focus inside the overlay.
	 *
	 * @return {HTMLElement[]} Focusable elements in tab order.
	 */
	function focusable() {
		return Array.prototype.slice.call( overlay.querySelectorAll( 'a[href], button:not([disabled]), input:not([disabled])' ) );
	}

	function open() {
		overlay.classList.add( 'is-open' );
		document.body.classList.add( 'boi-search-open' );
		toggle.setAttribute( 'aria-expanded', 'true' );

		var field = overlay.querySelector( 'input[type="search"]' );

		if ( field ) {
			field.focus();
			field.select();
		}
	}

	function close() {
		overlay.classList.remove( 'is-open' );
		document.body.classList.remove( 'boi-search-open' );
		toggle.setAttribute( 'aria-expanded', 'false' );

		// Clear a #boi-search fragment left by the no-script fallback.
		if ( window.location.hash === '#boi-search' && window.history.replaceState ) {
			window.history.replaceState( null, '', window.location.pathname + window.location.search );
		}

		toggle.focus();
	}

	function isOpen() {
		return overlay.classList.contains( 'is-open' );
	}

	toggle.addEventListener( 'click', function ( event ) {
		event.preventDefault();
		open();
	} );

	if ( closeButton ) {
		closeButton.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			close();
		} );
	}

	// A click on the dimmed area, outside the box, closes it.
	overlay.addEventListener( 'click', function ( event ) {
		if ( panel && ! panel.contains( event.target ) ) {
			close();
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		var tag = event.target && event.target.tagName ? event.target.tagName.toLowerCase() : '';

		if ( ! isOpen() ) {
			if ( event.key === '/' && ! event.ctrlKey && ! event.metaKey && ! event.altKey && tag !== 'input' && tag !== 'textarea' && tag !== 'select' && ! ( event.target && event.target.isContentEditable ) ) {
				event.preventDefault();
				open();
			}
			return;
		}

		if ( event.key === 'Escape' ) {
			event.preventDefault();
			close();
			return;
		}

		if ( event.key === 'Tab' ) {
			var items = focusable();

			if ( ! items.length ) {
				return;
			}

			var first = items[ 0 ];
			var last = items[ items.length - 1 ];

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}
	} );

	// Opened through the fragment (no-script link followed before this ran).
	if ( window.location.hash === '#boi-search' ) {
		open();
	}
}() );
