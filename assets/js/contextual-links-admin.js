( function () {
	'use strict';
	var root = document.querySelector( '.cl-admin' );
	if ( ! root ) { return; }
	var tabs = Array.prototype.slice.call( root.querySelectorAll( '.cl-admin__tab' ) );
	var panels = root.querySelectorAll( '.cl-admin__panel' );
	panels.forEach( function ( p ) {
		p.setAttribute( 'role', 'tabpanel' );
		p.id = 'cl-panel-' + p.dataset.panel;
	} );
	tabs.forEach( function ( t ) {
		t.id = 'cl-tab-' + t.dataset.tab;
		t.setAttribute( 'aria-controls', 'cl-panel-' + t.dataset.tab );
	} );
	function show( name, focus ) {
		tabs.forEach( function ( t ) {
			var on = t.dataset.tab === name;
			t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			t.tabIndex = on ? 0 : -1;
			if ( on && focus ) { t.focus(); }
		} );
		panels.forEach( function ( p ) {
			p.hidden = p.dataset.panel !== name;
			p.setAttribute( 'aria-labelledby', 'cl-tab-' + p.dataset.panel );
		} );
		try { window.localStorage.setItem( 'clTab', name ); } catch ( e ) {}
	}
	tabs.forEach( function ( t, i ) {
		t.addEventListener( 'click', function () { show( t.dataset.tab ); } );
		t.addEventListener( 'keydown', function ( e ) {
			var j;
			if ( 'ArrowRight' === e.key ) { j = ( i + 1 ) % tabs.length; }
			else if ( 'ArrowLeft' === e.key ) { j = ( i - 1 + tabs.length ) % tabs.length; }
			else if ( 'Home' === e.key ) { j = 0; }
			else if ( 'End' === e.key ) { j = tabs.length - 1; }
			else { return; }
			e.preventDefault();
			show( tabs[ j ].dataset.tab, true );
		} );
	} );
	var saved = 'domains';
	try { saved = window.localStorage.getItem( 'clTab' ) || saved; } catch ( e ) {}
	show( root.querySelector( '[data-tab="' + saved + '"]' ) ? saved : 'domains' );
}() );
