/**
 * Search Block - Editor Script
 *
 * @package
 */

/// <reference path="../../types/wordpress.d.ts" />

( function () {
	if ( typeof wp === 'undefined' ) {
		return;
	}

	const { registerBlockType } = wp.blocks;
	const { createElement: el } = wp.element;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, TextControl, NumberControl } = wp.components;
	const { __ } = wp.i18n;

	registerBlockType( 'interactivity-theme/search', {
		attributes: {
			placeholder: { type: 'string', default: 'Search...' },
			postType: { type: 'string', default: 'post' },
			maxResults: { type: 'number', default: 5 },
		},

		edit( props: {
			attributes: {
				placeholder: string;
				postType: string;
				maxResults: number;
			};
			setAttributes: ( attrs: Record< string, unknown > ) => void;
		} ) {
			const { attributes, setAttributes } = props;

			return el(
				'div',
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{
							title: __(
								'Search Settings',
								'interactivity-theme'
							),
						},
						el( TextControl, {
							label: __( 'Placeholder', 'interactivity-theme' ),
							value: attributes.placeholder,
							onChange: ( value: string ) =>
								setAttributes( { placeholder: value } ),
						} ),
						el( TextControl, {
							label: __( 'Post Type', 'interactivity-theme' ),
							value: attributes.postType,
							onChange: ( value: string ) =>
								setAttributes( { postType: value } ),
							help: __(
								'WordPress post type to search',
								'interactivity-theme'
							),
						} ),
						el( NumberControl, {
							label: __(
								'Maximum Results',
								'interactivity-theme'
							),
							value: attributes.maxResults,
							onChange: ( value: number ) =>
								setAttributes( { maxResults: value } ),
							min: 1,
							max: 20,
						} )
					)
				),
				el(
					'div',
					{ className: 'wp-block-search__input-wrapper' },
					el( 'input', {
						type: 'search',
						className: 'wp-block-search__input',
						placeholder: attributes.placeholder,
						disabled: true,
					} )
				),
				el(
					'p',
					{
						className: 'description',
						style: { marginTop: '1rem', fontSize: '0.875rem' },
					},
					__(
						'Live search will display results as users type. Results are fetched from WordPress REST API.',
						'interactivity-theme'
					)
				)
			);
		},

		save() {
			return null;
		},
	} );
} )();
