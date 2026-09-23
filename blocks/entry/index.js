( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var TEMPLATE = [ [ 'core/paragraph', { placeholder: __( 'Describe this entry…', 'bestofislam' ) } ] ];

	/**
	 * Generates a stable identifier for an entry, used as the voting key.
	 *
	 * @return {string} Identifier.
	 */
	function makeEntryId() {
		if ( window.crypto && window.crypto.randomUUID ) {
			return window.crypto.randomUUID();
		}
		return 'e' + Date.now().toString( 36 ) + Math.random().toString( 36 ).slice( 2, 8 );
	}

	blocks.registerBlockType( 'bestofislam/entry', {
		edit: function ( props ) {
			var atts = props.attributes;

			element.useEffect( function () {
				if ( ! atts.entryId ) {
					props.setAttributes( { entryId: makeEntryId() } );
				}
			}, [] );

			var blockProps = blockEditor.useBlockProps( { className: 'boi-entry' } );
			var inner = blockEditor.useInnerBlocksProps(
				{ className: 'boi-entry__content' },
				{ template: TEMPLATE, templateLock: false }
			);

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Entry', 'bestofislam' ) },
						el( components.TextareaControl, {
							label: __( 'The objection', 'bestofislam' ),
							help: __( 'State the argument being answered, in its own terms.', 'bestofislam' ),
							value: atts.claim,
							onChange: function ( value ) {
								props.setAttributes( { claim: value } );
							}
						} ),
						el( components.TextControl, {
							label: __( 'Attribution', 'bestofislam' ),
							value: atts.claimSource,
							onChange: function ( value ) {
								props.setAttributes( { claimSource: value } );
							}
						} ),
						el( components.TextControl, {
							label: __( 'Link URL', 'bestofislam' ),
							value: atts.linkUrl,
							onChange: function ( value ) {
								props.setAttributes( { linkUrl: value } );
							}
						} ),
						el( components.TextControl, {
							label: __( 'Link text', 'bestofislam' ),
							value: atts.linkText,
							onChange: function ( value ) {
								props.setAttributes( { linkText: value } );
							}
						} )
					)
				),
				el(
					'article',
					blockProps,
					el( 'div', { className: 'boi-entry__rank', 'aria-hidden': 'true' }, '#' ),
					el(
						'div',
						{ className: 'boi-entry__body' },
						el( blockEditor.MediaUploadCheck, null,
							el( blockEditor.MediaUpload, {
								allowedTypes: [ 'image' ],
								value: atts.imageId,
								onSelect: function ( media ) {
									props.setAttributes( {
										imageId: media.id,
										imageUrl: media.url,
										imageAlt: media.alt || ''
									} );
								},
								render: function ( open ) {
									return atts.imageUrl
										? el( 'button', {
											type: 'button',
											className: 'boi-entry__media-button',
											onClick: open.open
										}, el( 'img', { src: atts.imageUrl, alt: atts.imageAlt } ) )
										: el( components.Button, {
											variant: 'secondary',
											onClick: open.open
										}, __( 'Select image', 'bestofislam' ) );
								}
							} )
						),
						el( blockEditor.RichText, {
							tagName: 'h2',
							className: 'boi-entry__title',
							value: atts.title,
							allowedFormats: [],
							placeholder: __( 'Entry title', 'bestofislam' ),
							onChange: function ( value ) {
								props.setAttributes( { title: value } );
							}
						} ),
						el( 'div', inner )
					)
				)
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
