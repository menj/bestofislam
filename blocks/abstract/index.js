( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var STYLES = [
		{ label: __( 'As in Theme Options', 'bestofislam' ), value: '' },
		{ label: __( 'Ruled', 'bestofislam' ), value: 'ruled' },
		{ label: __( 'Card', 'bestofislam' ), value: 'card' },
		{ label: __( 'Academic', 'bestofislam' ), value: 'academic' },
		{ label: __( 'Minimal', 'bestofislam' ), value: 'minimal' }
	];

	blocks.registerBlockType( 'bestofislam/abstract', {
		edit: function ( props ) {
			var atts = props.attributes;
			var style = atts.style || 'ruled';

			return [
				el(
					blockEditor.InspectorControls,
					{ key: 'inspector' },
					el(
						components.PanelBody,
						{ title: __( 'Abstract', 'bestofislam' ), initialOpen: true },
						el( components.TextControl, {
							label: __( 'Label', 'bestofislam' ),
							help: __( 'Leave empty for the label set in Theme Options.', 'bestofislam' ),
							value: atts.title,
							onChange: function ( v ) { props.setAttributes( { title: v } ); }
						} ),
						el( components.SelectControl, {
							label: __( 'Style', 'bestofislam' ),
							value: atts.style,
							options: STYLES,
							onChange: function ( v ) { props.setAttributes( { style: v } ); }
						} )
					)
				),
				el(
					'aside',
					blockEditor.useBlockProps( { key: 'box', className: 'boi-abstract boi-abstract--' + style } ),
					el( 'p', { className: 'boi-abstract__title' }, atts.title || __( 'Abstract', 'bestofislam' ) ),
					el( blockEditor.RichText, {
						tagName: 'p',
						className: 'boi-abstract__body',
						value: atts.text,
						allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
						placeholder: __( 'The claim, the points the article walks through and where it comes out, in 60 to 100 words…', 'bestofislam' ),
						onChange: function ( v ) { props.setAttributes( { text: v } ); }
					} )
				)
			];
		},
		save: function () {
			return null;
		}
	} );
}( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n ) );
