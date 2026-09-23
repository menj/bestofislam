/**
 * Settings screen behaviour: dependent field states on the Voting tab.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var master = document.querySelector( 'input[name$="[voting_enabled]"]' );

		if ( ! master ) {
			return;
		}

		var dependents = [
			document.querySelector( 'input[name$="[voting_login_only]"]' ),
			document.getElementById( 'boi-rate-limit' )
		].filter( Boolean );

		/**
		 * Enables or disables the dependent controls.
		 *
		 * @return {void}
		 */
		function sync() {
			dependents.forEach( function ( field ) {
				field.disabled = ! master.checked;
				field.closest( 'tr' ).style.opacity = master.checked ? '1' : '0.5';
			} );
		}

		master.addEventListener( 'change', sync );
		sync();
	} );
} )();
