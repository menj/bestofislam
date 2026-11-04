/**
 * Theme Options screen behaviour.
 *
 * - Tabs: switch panels without a reload (the heavy Content and Security
 *   panels load their page when opened), with the WAI-ARIA tabs pattern
 *   (arrow keys, Home and End move between tabs; the chosen tab is kept in
 *   the address so a reload or a save returns to it). Without this script
 *   the tabs are plain links and the server opens the chosen panel.
 * - Sliders: show their value as they move.
 * - Dependent rows: rows marked data-boi-depends are disabled while the
 *   switch they depend on is off. A disabled field is not submitted, and
 *   the sanitiser keeps its saved value.
 *
 * No globals, no dependencies.
 */
( function () {
	'use strict';

	/**
	 * Wires the tab list.
	 *
	 * @param {HTMLElement} root The .boi-settings wrapper.
	 * @return {void}
	 */
	function initTabs( root ) {
		var list = root.querySelector( '[role="tablist"]' );

		if ( ! list ) {
			return;
		}

		var tabs = Array.prototype.slice.call( list.querySelectorAll( '[role="tab"]' ) );

		/**
		 * Shows one panel and marks its tab as selected.
		 *
		 * @param {HTMLElement} tab     The tab to select.
		 * @param {boolean}     focus   Whether to move focus to it.
		 * @return {void}
		 */
		function select( tab, focus ) {
			tabs.forEach( function ( other ) {
				var on = other === tab;
				var panel = document.getElementById( other.getAttribute( 'aria-controls' ) );

				other.setAttribute( 'aria-selected', on ? 'true' : 'false' );
				other.setAttribute( 'tabindex', on ? '0' : '-1' );
				other.classList.toggle( 'is-active', on );

				if ( panel ) {
					panel.hidden = ! on;
				}
			} );

			if ( focus ) {
				tab.focus();
			}

			if ( window.history && window.history.replaceState ) {
				var url = new URL( window.location.href );
				url.searchParams.set( 'tab', tab.getAttribute( 'data-boi-tab' ) );
				// A notice from the last save belongs to the tab that saved.
				url.searchParams.delete( 'settings-updated' );
				window.history.replaceState( null, '', url.toString() );
			}
		}

		tabs.forEach( function ( tab, index ) {
			tab.addEventListener( 'click', function ( event ) {
				// Let a modified click open the tab in a new window as a link.
				if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || 0 !== event.button ) {
					return;
				}
				// A heavy panel is rendered only when opened: follow the link.
				if ( tab.hasAttribute( 'data-boi-reload' ) ) {
					return;
				}
				event.preventDefault();
				select( tab, false );
			} );

			tab.addEventListener( 'keydown', function ( event ) {
				var next = null;

				switch ( event.key ) {
					case 'ArrowRight':
					case 'ArrowDown':
						next = tabs[ ( index + 1 ) % tabs.length ];
						break;
					case 'ArrowLeft':
					case 'ArrowUp':
						next = tabs[ ( index - 1 + tabs.length ) % tabs.length ];
						break;
					case 'Home':
						next = tabs[ 0 ];
						break;
					case 'End':
						next = tabs[ tabs.length - 1 ];
						break;
					case ' ':
					case 'Enter':
						event.preventDefault();
						if ( tab.hasAttribute( 'data-boi-reload' ) ) {
							window.location.href = tab.href;
							return;
						}
						select( tab, true );
						return;
				}

				if ( next ) {
					event.preventDefault();
					select( next, true );
					next.scrollIntoView( { block: 'nearest', inline: 'nearest' } );
				}
			} );
		} );
	}

	/**
	 * Keeps each slider's printed value in step with the slider.
	 *
	 * @param {HTMLElement} root The .boi-settings wrapper.
	 * @return {void}
	 */
	function initRanges( root ) {
		Array.prototype.forEach.call( root.querySelectorAll( '.boi-range input[type="range"]' ), function ( input ) {
			var output = document.getElementById( input.id + '-value' );
			var unit = input.getAttribute( 'data-boi-unit' ) || '';

			if ( ! output ) {
				return;
			}

			function sync() {
				output.textContent = unit ? input.value + ' ' + unit : input.value;
				input.setAttribute( 'aria-valuetext', output.textContent );
			}

			input.addEventListener( 'input', sync );
			sync();
		} );
	}

	/**
	 * Disables rows whose master switch is off.
	 *
	 * @param {HTMLElement} root The .boi-settings wrapper.
	 * @return {void}
	 */
	function initDependents( root ) {
		Array.prototype.forEach.call( root.querySelectorAll( '[data-boi-depends]' ), function ( row ) {
			var master = document.getElementById( row.getAttribute( 'data-boi-depends' ) );

			if ( ! master ) {
				return;
			}

			function sync() {
				row.classList.toggle( 'is-disabled', ! master.checked );
				Array.prototype.forEach.call( row.querySelectorAll( 'input, select, textarea' ), function ( field ) {
					field.disabled = ! master.checked;
				} );
			}

			master.addEventListener( 'change', sync );
			sync();
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var root = document.querySelector( '.boi-settings' );

		if ( ! root ) {
			return;
		}

		initTabs( root );
		initRanges( root );
		initDependents( root );
	} );
} )();
