/**
 * Images tab of Theme Options: search Pexels with the official client and copy
 * a plan entry for bin/stock-images.json. The key comes from boiImages.key,
 * which the theme prints for administrators only.
 */
( function () {
	'use strict';

	var grid = document.getElementById( 'boi-images-grid' );
	var input = document.getElementById( 'boi-images-q' );
	var go = document.getElementById( 'boi-images-go' );
	var status = document.getElementById( 'boi-images-status' );

	if ( ! grid || ! input || ! go || ! status ) {
		return;
	}

	var key = window.boiImages && window.boiImages.key ? window.boiImages.key : '';
	var lib = window.pexels;

	function say( text ) {
		status.textContent = text;
	}

	function card( photo ) {
		var li = document.createElement( 'figure' );
		li.className = 'boi-images__card';

		var img = document.createElement( 'img' );
		img.src = photo.src.medium;
		img.alt = photo.alt || '';
		img.loading = 'lazy';
		li.appendChild( img );

		var cap = document.createElement( 'figcaption' );
		var by = document.createElement( 'a' );
		by.href = photo.photographer_url;
		by.target = '_blank';
		by.rel = 'noopener';
		by.textContent = photo.photographer;
		cap.appendChild( by );
		cap.appendChild( document.createTextNode( ' on ' ) );
		var page = document.createElement( 'a' );
		page.href = photo.url;
		page.target = '_blank';
		page.rel = 'noopener';
		page.textContent = 'Pexels';
		cap.appendChild( page );
		li.appendChild( cap );

		var alt = document.createElement( 'p' );
		alt.className = 'boi-images__alt';
		alt.textContent = photo.alt || '';
		li.appendChild( alt );

		var copy = document.createElement( 'button' );
		copy.type = 'button';
		copy.className = 'button';
		copy.textContent = 'Copy plan entry';
		copy.addEventListener( 'click', function () {
			var entry = JSON.stringify( { src: 'pexels', id: String( photo.id ), alt: photo.alt || '', caption: '' }, null, 1 );
			var done = function () {
				say( 'Copied the plan entry for photograph ' + photo.id + '. Paste it into bin/stock-images.json and write the caption.' );
			};
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( entry ).then( done, function () {
					say( entry );
				} );
			} else {
				say( entry );
			}
		} );
		li.appendChild( copy );

		return li;
	}

	function search() {
		var q = input.value.trim();

		if ( ! key ) {
			say( 'Save a Pexels key first, above.' );
			return;
		}

		if ( ! lib || ! lib.createClient ) {
			say( 'The Pexels client did not load.' );
			return;
		}

		if ( ! q ) {
			say( 'Type what to look for.' );
			return;
		}

		say( 'Searching' );
		grid.textContent = '';

		lib.createClient( key ).photos.search( { query: encodeURIComponent( q ), per_page: 18, orientation: 'landscape' } ).then( function ( res ) {
			if ( res.error || ! res.photos ) {
				say( 'Pexels answered: ' + ( res.error || 'no photographs' ) );
				return;
			}

			res.photos.forEach( function ( photo ) {
				grid.appendChild( card( photo ) );
			} );
			say( res.photos.length + ' photographs, of about ' + res.total_results + '.' );
		} ).catch( function ( err ) {
			say( 'Pexels refused the request (' + err.message + '). Check the key.' );
		} );
	}

	go.addEventListener( 'click', search );
	input.addEventListener( 'keydown', function ( e ) {
		if ( 'Enter' === e.key ) {
			e.preventDefault();
			search();
		}
	} );
}() );
