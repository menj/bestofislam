/**
 * Quick Edit: ticks the Unlist box when the row's Unlisted column says so
 * (inc/unlist.php).
 */
( function ( $ ) {
	'use strict';

	if ( typeof window.inlineEditPost === 'undefined' ) {
		return;
	}

	var original = window.inlineEditPost.edit;

	window.inlineEditPost.edit = function ( id ) {
		original.apply( this, arguments );

		var postId = parseInt( typeof id === 'object' ? this.getId( id ) : id, 10 );

		if ( ! postId ) {
			return;
		}

		var unlisted = String( $( '#post-' + postId ).find( '.boi-unlist-status' ).data( 'unlisted' ) ) === '1';

		$( '#edit-' + postId ).find( 'input[name="boi_unlisted"]' ).prop( 'checked', unlisted );
	};
}( jQuery ) );
