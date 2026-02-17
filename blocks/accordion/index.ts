/**
 * Accordion Block - Editor Script
 *
 * @package
 */

/// <reference path="../../types/wordpress.d.ts" />

interface AccordionItem {
	title: string;
	content: string;
}

( function () {
	if ( typeof wp === 'undefined' ) {
		return;
	}

	const { registerBlockType } = wp.blocks;
	const { createElement: el, useState } = wp.element;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl, TextControl, TextareaControl } =
		wp.components;
	const { __ } = wp.i18n;

	const AccordionEdit = function ( props: {
		attributes: { items: AccordionItem[]; allowMultiple: boolean };
		setAttributes: ( attrs: Record< string, unknown > ) => void;
	} ) {
		const { attributes, setAttributes } = props;

		// Use internal state for active tab - simulating useState
		const [ activeTab, setActiveTab ] = useState( 0 );

		const addItem = function () {
			const newItems: AccordionItem[] = [
				...attributes.items,
				{
					title: 'Section ' + ( attributes.items.length + 1 ),
					content: '',
				},
			];
			setAttributes( { items: newItems } );
			setActiveTab( newItems.length - 1 );
		};

		const updateItem = function (
			index: number,
			key: keyof AccordionItem,
			value: string
		) {
			const newItems = attributes.items.map( function ( item, i ) {
				return i === index ? { ...item, [ key ]: value } : item;
			} );
			setAttributes( { items: newItems } );
		};

		const removeItem = function ( index: number ) {
			const newItems = attributes.items.filter( function ( _, i ) {
				return i !== index;
			} );
			setAttributes( { items: newItems } );
			if ( activeTab >= newItems.length ) {
				setActiveTab( Math.max( 0, newItems.length - 1 ) );
			}
		};

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
							'Accordion Settings',
							'interactivity-theme'
						),
					},
					el( ToggleControl, {
						label: __(
							'Allow Multiple Open',
							'interactivity-theme'
						),
						checked: attributes.allowMultiple,
						onChange( value: boolean ) {
							setAttributes( { allowMultiple: value } );
						},
						help: __(
							'Allow multiple accordion sections to be open at the same time',
							'interactivity-theme'
						),
					} )
				)
			),
			el(
				'div',
				{ className: 'wp-block-accordion' },
				el(
					'div',
					{
						className: 'accordion-tabs',
						style: {
							display: 'flex',
							gap: '0.5rem',
							marginBottom: '1rem',
							flexWrap: 'wrap',
						},
					},
					attributes.items.map( function ( item, index ) {
						return el(
							'button',
							{
								key: index,
								onClick() {
									setActiveTab( index );
								},
								style: {
									padding: '0.5rem 1rem',
									background:
										activeTab === index
											? '#0073aa'
											: '#e9ecef',
									color:
										activeTab === index ? '#fff' : '#333',
									border: 'none',
									borderRadius: '4px',
									cursor: 'pointer',
								},
							},
							index + 1
						);
					} ),
					el(
						'button',
						{
							onClick: addItem,
							style: {
								padding: '0.5rem 1rem',
								background: '#28a745',
								color: '#fff',
								border: 'none',
								borderRadius: '4px',
								cursor: 'pointer',
							},
						},
						'+'
					)
				),
				attributes.items.length > 0
					? el(
							'div',
							{ className: 'accordion-tab-content' },
							el( TextControl, {
								label: __( 'Title', 'interactivity-theme' ),
								value: attributes.items[ activeTab ].title,
								onChange( value: string ) {
									updateItem( activeTab, 'title', value );
								},
							} ),
							el( TextareaControl, {
								label: __( 'Content', 'interactivity-theme' ),
								value: attributes.items[ activeTab ].content,
								onChange( value: string ) {
									updateItem( activeTab, 'content', value );
								},
								rows: 4,
							} ),
							attributes.items.length > 1
								? el(
										'button',
										{
											onClick() {
												removeItem( activeTab );
											},
											style: {
												marginTop: '0.5rem',
												padding: '0.5rem 1rem',
												background: '#dc3545',
												color: '#fff',
												border: 'none',
												borderRadius: '4px',
												cursor: 'pointer',
											},
										},
										__(
											'Remove Section',
											'interactivity-theme'
										)
								  )
								: null
					  )
					: null
			)
		);
	};

	registerBlockType( 'interactivity-theme/accordion', {
		attributes: {
			items: {
				type: 'array',
				default: [
					{
						title: 'Section 1',
						content: 'This is the content for section 1.',
					},
					{
						title: 'Section 2',
						content: 'This is the content for section 2.',
					},
					{
						title: 'Section 3',
						content: 'This is the content for section 3.',
					},
				],
			},
			allowMultiple: { type: 'boolean', default: false },
		},

		edit: AccordionEdit,

		save() {
			return null;
		},
	} );
} )();
