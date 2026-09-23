/**
 * Appearance toggle.
 *
 * The pre-paint script in the document head has already applied the stored
 * preference, so this file only handles the click and keeps the switch state
 * in sync.
 */
( function () {
	'use strict';

	var KEY = 'boi-appearance';

	/**
	 * Returns the appearance currently applied.
	 *
	 * @return {string} Either light or dark.
	 */
	function current() {
		return document.documentElement.getAttribute( 'data-theme' ) === 'dark' ? 'dark' : 'light';
	}

	/**
	 * Updates a control to reflect the applied appearance.
	 *
	 * @param {HTMLElement} button Toggle button.
	 * @param {string}      mode   Applied appearance.
	 * @return {void}
	 */
	function sync( button, mode ) {
		var dark = mode === 'dark';

		button.setAttribute( 'aria-checked', dark ? 'true' : 'false' );
		button.setAttribute(
			'aria-label',
			dark ? 'Switch to light appearance' : 'Switch to dark appearance'
		);
	}

	/**
	 * Applies an appearance and records the choice.
	 *
	 * @param {string} mode Appearance to apply.
	 * @return {void}
	 */
	function apply( mode ) {
		document.documentElement.setAttribute( 'data-theme', mode );

		try {
			window.localStorage.setItem( KEY, mode );
		} catch ( error ) {
			// Storage unavailable; the choice lasts for this page view only.
		}

		document.querySelectorAll( '[data-boi-appearance]' ).forEach( function ( button ) {
			sync( button, mode );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var buttons = document.querySelectorAll( '[data-boi-appearance]' );

		buttons.forEach( function ( button ) {
			sync( button, current() );

			button.addEventListener( 'click', function () {
				apply( current() === 'dark' ? 'light' : 'dark' );
			} );
		} );

		// Follow the system setting until the reader states a preference.
		var query = window.matchMedia( '(prefers-color-scheme: dark)' );

		query.addEventListener( 'change', function ( event ) {
			try {
				if ( window.localStorage.getItem( KEY ) ) {
					return;
				}
			} catch ( error ) {
				return;
			}

			apply( event.matches ? 'dark' : 'light' );
		} );
	} );
} )();
