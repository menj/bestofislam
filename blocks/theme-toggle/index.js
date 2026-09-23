( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'bestofislam/theme-toggle', {
		edit: function ( props ) {
			var blockProps = blockEditor.useBlockProps( { className: 'boi-appearance' } );

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Appearance toggle', 'bestofislam' ) },
						el( components.ToggleControl, {
							label: __( 'Show text label', 'bestofislam' ),
							checked: props.attributes.showLabel,
							onChange: function ( value ) {
								props.setAttributes( { showLabel: value } );
							}
						} )
					)
				),
				el(
					'div',
					blockProps,
					el(
						'div',
						{ className: 'boi-appearance__frame' },
						el( 'span', { className: 'boi-appearance__block' } ),
						el( 'span', { className: 'boi-appearance__cell' }, '\u2600' ),
						el( 'span', { className: 'boi-appearance__cell' }, '\u263E' )
					)
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);
