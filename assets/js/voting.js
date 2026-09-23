/**
 * Entry voting.
 *
 * Votes are posted to the REST endpoint; server-side checks remain
 * authoritative, and the local record only suppresses obvious repeat clicks.
 */
( function () {
	'use strict';

	if ( typeof window.boiVoting === 'undefined' ) {
		return;
	}

	var config = window.boiVoting;
	var storageKey = 'boi-voted-' + config.postId;

	/**
	 * Reads the locally recorded votes for this post.
	 *
	 * @return {Array} Entry identifiers.
	 */
	function readVoted() {
		try {
			return JSON.parse( window.localStorage.getItem( storageKey ) ) || [];
		} catch ( error ) {
			return [];
		}
	}

	/**
	 * Records a local vote.
	 *
	 * @param {string} entryId Entry identifier.
	 * @return {void}
	 */
	function rememberVote( entryId ) {
		try {
			var voted = readVoted();

			if ( voted.indexOf( entryId ) === -1 ) {
				voted.push( entryId );
				window.localStorage.setItem( storageKey, JSON.stringify( voted ) );
			}
		} catch ( error ) {
			// Storage unavailable; the server still enforces the limit.
		}
	}

	/**
	 * Marks a control as already voted.
	 *
	 * @param {HTMLElement} button Vote button.
	 * @return {void}
	 */
	function markVoted( button ) {
		button.classList.add( 'is-voted' );
		button.disabled = true;
	}

	/**
	 * Sends a vote for one entry.
	 *
	 * @param {HTMLElement} widget Vote wrapper.
	 * @return {void}
	 */
	function sendVote( widget ) {
		var button = widget.querySelector( '.boi-vote__button' );
		var count = widget.querySelector( '.boi-vote__count' );
		var message = widget.querySelector( '.boi-vote__message' );
		var entryId = widget.getAttribute( 'data-entry-id' );

		if ( config.loginOnly && ! config.loggedIn ) {
			message.textContent = config.strings.loginOnly;
			return;
		}

		button.disabled = true;

		window.fetch( config.endpoint, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': config.nonce
			},
			body: JSON.stringify( {
				post_id: config.postId,
				entry_id: entryId
			} )
		} )
			.then( function ( response ) {
				return response.json().then( function ( data ) {
					return { ok: response.ok, status: response.status, data: data };
				} );
			} )
			.then( function ( result ) {
				if ( result.ok ) {
					count.textContent = result.data.count;
					message.textContent = '';
					rememberVote( entryId );
					markVoted( button );
					return;
				}

				if ( result.status === 409 ) {
					message.textContent = config.strings.duplicate;
					rememberVote( entryId );
					markVoted( button );
					return;
				}

				if ( result.status === 429 ) {
					message.textContent = config.strings.throttled;
				} else if ( result.status === 401 ) {
					message.textContent = config.strings.loginOnly;
				} else {
					message.textContent = config.strings.error;
				}

				button.disabled = false;
			} )
			.catch( function () {
				message.textContent = config.strings.error;
				button.disabled = false;
			} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var voted = readVoted();

		document.querySelectorAll( '.boi-vote' ).forEach( function ( widget ) {
			var button = widget.querySelector( '.boi-vote__button' );

			if ( ! button ) {
				return;
			}

			if ( voted.indexOf( widget.getAttribute( 'data-entry-id' ) ) !== -1 ) {
				markVoted( button );
				return;
			}

			button.addEventListener( 'click', function () {
				sendVote( widget );
			} );
		} );
	} );
} )();
