/**
 * Opens a question reached by its anchor, including one inside the
 * collapsed "more questions" disclosure, so a link from an article lands on
 * an answer that is visible. No dependencies.
 */
( function () {
	function openTarget() {
		var id = window.location.hash.slice( 1 );

		if ( ! id || id.indexOf( 'q-' ) !== 0 ) {
			return;
		}

		var item = document.getElementById( id );

		if ( ! item || item.tagName !== 'DETAILS' ) {
			return;
		}

		var parent = item.closest( '.boi-qmore' );

		if ( parent ) {
			parent.open = true;
		}

		item.open = true;
		item.scrollIntoView( { block: 'start' } );
	}

	window.addEventListener( 'hashchange', openTarget );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', openTarget );
	} else {
		openTarget();
	}
}() );
