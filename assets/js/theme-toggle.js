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

	// Set when the reader chooses, so a choice that could not be saved still
	// holds for this page view against later changes to the system setting.
	var chosen = false;

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
	 * Applies an appearance, and records it as the reader's choice unless it
	 * is only following the system setting.
	 *
	 * @param {string}  mode   Appearance to apply.
	 * @param {boolean} follow True when applying the system setting.
	 * @return {void}
	 */
	function apply( mode, follow ) {
		document.documentElement.setAttribute( 'data-theme', mode );

		if ( ! follow ) {
			chosen = true;

			try {
				window.localStorage.setItem( KEY, mode );
			} catch ( error ) {
				// Storage unavailable; the choice lasts for this page view only.
			}
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
			if ( chosen ) {
				return;
			}

			try {
				if ( window.localStorage.getItem( KEY ) ) {
					return;
				}
			} catch ( error ) {
				// Storage is blocked, so there is no saved choice: keep following.
			}

			// Following the system is not a choice, so it is not saved; later
			// changes to the system setting then keep being followed.
			apply( event.matches ? 'dark' : 'light', true );
		} );
	} );
} )();
