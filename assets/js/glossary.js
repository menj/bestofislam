/**
 * Glossary: find a term as you type, and filter the index by letter and kind.
 *
 * Without this script the Glossary is the full list and the letters are
 * ordinary links to it, so nothing here is needed to read an entry.
 */
( function () {
	'use strict';

	var bar = document.querySelector( '.boi-gbar' );
	if ( ! bar ) {
		return;
	}

	/* Combining marks, and the apostrophes and hyphens that spellings vary in. */
	var chars = function ( codes ) {
		return codes.map( function ( c ) {
			return String.fromCharCode( c );
		} ).join( '' );
	};
	var MARKS = new RegExp( '[' + chars( [ 768 ] ) + '-' + chars( [ 879 ] ) + ']', 'g' );
	var QUOTES = new RegExp( '[' + chars( [ 8217, 8216, 703, 702, 39, 96 ] ) + '\\-]', 'g' );

	var norm = function ( s ) {
		return String( s ).toLowerCase().normalize( 'NFD' ).replace( MARKS, '' ).replace( QUOTES, '' );
	};

	var input = bar.querySelector( 'input' );
	var box = bar.querySelector( '.boi-gbar__results' );
	var letters = bar.querySelectorAll( '.boi-gbar__az a' );
	var index = document.querySelector( '.boi-glossary' );
	var terms = [];

	try {
		terms = JSON.parse( document.getElementById( 'boi-gterms' ).textContent );
	} catch ( e ) {}

	terms.forEach( function ( t ) {
		t.k = norm( t.n );
	} );

	/* The index page: filter in place. */
	var state = { letter: '', type: '', q: '' };
	var items, groups, count, empty, reset, tools;

	if ( index ) {
		items = Array.prototype.slice.call( index.querySelectorAll( '.boi-glossary__item' ) );
		groups = Array.prototype.slice.call( index.querySelectorAll( '.boi-glossary__group' ) );
		count = index.querySelector( '.boi-glossary__count' );
		empty = index.querySelector( '.boi-glossary__empty' );
		reset = index.querySelector( '.boi-glossary__reset' );
		tools = index.querySelector( '.boi-glossary__tools' );
		items.forEach( function ( li ) {
			li.setAttribute( 'data-q', norm( li.textContent ) );
		} );
		tools.hidden = false;
	}

	function apply() {
		var shown = 0;
		var q = norm( state.q.trim() );

		groups.forEach( function ( g ) {
			var any = false;
			var inLetter = ! state.letter || ( g.getAttribute( 'data-letter' ) || g.id ) === state.letter;

			Array.prototype.forEach.call( g.querySelectorAll( '.boi-glossary__item' ), function ( li ) {
				var ok = inLetter && ( ! state.type || li.getAttribute( 'data-type' ) === state.type ) && ( ! q || li.getAttribute( 'data-q' ).indexOf( q ) > -1 );
				li.hidden = ! ok;
				if ( ok ) {
					any = true;
					if ( ! li.classList.contains( 'is-alias' ) ) {
						shown++;
					}
				}
			} );

			g.hidden = ! any;
		} );

		// Hide a part heading (Terms, People) when nothing under it is shown.
		Array.prototype.forEach.call( index.querySelectorAll( '.boi-glossary__part' ), function ( h ) {
			var el = h.nextElementSibling;
			var visible = false;
			while ( el && ! el.classList.contains( 'boi-glossary__part' ) ) {
				if ( el.classList.contains( 'boi-glossary__group' ) && ! el.hidden ) {
					visible = true;
				}
				el = el.nextElementSibling;
			}
			h.hidden = ! visible;
		} );

		var total = index.getAttribute( 'data-total' );
		count.textContent = shown === +total ? total + ' entries' : shown + ' of ' + total + ' entries';
		empty.hidden = shown > 0;
		reset.hidden = ! state.letter;

		Array.prototype.forEach.call( letters, function ( a ) {
			var on = a.getAttribute( 'data-letter' ) === state.letter;
			a.classList.toggle( 'is-active', on );
			if ( on ) {
				a.setAttribute( 'aria-current', 'true' );
			} else {
				a.removeAttribute( 'aria-current' );
			}
		} );
	}

	function setLetter( id, push ) {
		state.letter = id;
		apply();
		if ( push && window.history.replaceState ) {
			window.history.replaceState( null, '', id ? '#' + id : window.location.pathname + window.location.search );
		}
		if ( id ) {
			var top = index.getBoundingClientRect().top + window.scrollY - bar.offsetHeight - 40;
			if ( window.scrollY > top ) {
				window.scrollTo( 0, top );
			}
		}
	}

	if ( index ) {
		Array.prototype.forEach.call( letters, function ( a ) {
			a.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				setLetter( state.letter === a.getAttribute( 'data-letter' ) ? '' : a.getAttribute( 'data-letter' ), true );
			} );
		} );

		Array.prototype.forEach.call( index.querySelectorAll( '.boi-chip' ), function ( chip ) {
			chip.addEventListener( 'click', function () {
				state.type = chip.getAttribute( 'data-type' );
				Array.prototype.forEach.call( index.querySelectorAll( '.boi-chip' ), function ( c ) {
					var on = c === chip;
					c.classList.toggle( 'is-active', on );
					c.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				} );
				apply();
			} );
		} );

		reset.addEventListener( 'click', function () {
			setLetter( '', true );
		} );

		var start = window.location.hash.replace( '#', '' );
		state.letter = /^letter-/.test( start ) ? start : '';
		apply();

		input.addEventListener( 'input', function () {
			state.q = input.value;
			apply();
		} );
		return;
	}

	/* Everywhere else: a list of matches under the box. */
	var current = -1;

	function close() {
		box.hidden = true;
		box.innerHTML = '';
		input.setAttribute( 'aria-expanded', 'false' );
		current = -1;
	}

	function render() {
		var q = norm( input.value.trim() );
		if ( ! q ) {
			close();
			return;
		}

		var hits = terms.filter( function ( t ) {
			return t.k.indexOf( q ) > -1;
		} ).sort( function ( a, b ) {
			return ( a.k.indexOf( q ) === 0 ? 0 : 1 ) - ( b.k.indexOf( q ) === 0 ? 0 : 1 ) || a.k.length - b.k.length;
		} ).slice( 0, 8 );

		box.innerHTML = '';
		current = -1;

		if ( ! hits.length ) {
			close();
			return;
		}

		hits.forEach( function ( t, i ) {
			var li = document.createElement( 'li' );
			var a = document.createElement( 'a' );
			var small = document.createElement( 'small' );
			li.setAttribute( 'role', 'presentation' );
			a.href = t.u;
			a.setAttribute( 'role', 'option' );
			a.id = 'boi-gr-' + i;
			a.textContent = t.n;
			small.textContent = t.s ? 'see ' + t.s : t.t;
			a.appendChild( small );
			li.appendChild( a );
			box.appendChild( li );
		} );

		box.hidden = false;
		input.setAttribute( 'aria-expanded', 'true' );
	}

	function move( d ) {
		var opts = box.querySelectorAll( 'a' );
		if ( ! opts.length ) {
			return;
		}
		if ( opts[ current ] ) {
			opts[ current ].removeAttribute( 'aria-selected' );
		}
		current = ( current + d + opts.length ) % opts.length;
		opts[ current ].setAttribute( 'aria-selected', 'true' );
		input.setAttribute( 'aria-activedescendant', opts[ current ].id );
	}

	input.addEventListener( 'input', render );
	input.addEventListener( 'keydown', function ( e ) {
		if ( 'ArrowDown' === e.key ) {
			e.preventDefault();
			move( 1 );
		} else if ( 'ArrowUp' === e.key ) {
			e.preventDefault();
			move( -1 );
		} else if ( 'Escape' === e.key ) {
			close();
		} else if ( 'Enter' === e.key ) {
			var opts = box.querySelectorAll( 'a' );
			var pick = opts[ current ] || opts[ 0 ];
			if ( pick ) {
				e.preventDefault();
				window.location.href = pick.href;
			}
		}
	} );

	document.addEventListener( 'click', function ( e ) {
		if ( ! bar.querySelector( '.boi-gbar__search' ).contains( e.target ) ) {
			close();
		}
	} );
}() );
