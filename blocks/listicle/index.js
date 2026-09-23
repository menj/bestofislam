( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var ALLOWED = [ 'bestofislam/entry' ];
	var TEMPLATE = [ [ 'bestofislam/entry' ], [ 'bestofislam/entry' ], [ 'bestofislam/entry' ] ];

	blocks.registerBlockType( 'bestofislam/listicle', {
		edit: function ( props ) {
			var blockProps = blockEditor.useBlockProps( {
				className: 'boi-listicle is-order-' + props.attributes.order
			} );

			var inner = blockEditor.useInnerBlocksProps( blockProps, {
				allowedBlocks: ALLOWED,
				template: TEMPLATE,
				templateLock: false
			} );

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'List settings', 'bestofislam' ) },
						el( components.SelectControl, {
							label: __( 'Numbering', 'bestofislam' ),
							value: props.attributes.order,
							options: [
								{ label: __( 'Countdown (highest number first)', 'bestofislam' ), value: 'descending' },
								{ label: __( 'Ascending (1 first)', 'bestofislam' ), value: 'ascending' }
							],
							onChange: function ( value ) {
								props.setAttributes( { order: value } );
							}
						} ),
						el( components.SelectControl, {
							label: __( 'Schema type', 'bestofislam' ),
							value: props.attributes.schemaType,
							options: [
								{ label: 'ItemList', value: 'ItemList' },
								{ label: __( 'None', 'bestofislam' ), value: 'none' }
							],
							onChange: function ( value ) {
								props.setAttributes( { schemaType: value } );
							}
						} ),
						el( components.ToggleControl, {
							label: __( 'Show jump list', 'bestofislam' ),
							checked: props.attributes.showJumpList,
							onChange: function ( value ) {
								props.setAttributes( { showJumpList: value } );
							}
						} )
					)
				),
				el( 'div', inner )
			);
		},
		save: function () {
			return el( blockEditor.InnerBlocks.Content );
		}
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);
