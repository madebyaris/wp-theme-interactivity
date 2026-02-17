/**
 * Counter Block - Editor Script
 *
 * @package
 */

( function () {
	if ( typeof wp === 'undefined' ) {
		return;
	}

	const { registerBlockType } = wp.blocks;
	const { createElement: el } = wp.element;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, NumberControl } = wp.components;
	const { __ } = wp.i18n;

	registerBlockType( 'interactivity-theme/counter', {
		attributes: {
			initialValue: { type: 'number', default: 0 },
			min: { type: 'number', default: 0 },
			max: { type: 'number', default: 100 },
			step: { type: 'number', default: 1 },
		},

		edit( props: {
			attributes: {
				initialValue: number;
				min: number;
				max: number;
				step: number;
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
								'Counter Settings',
								'interactivity-theme'
							),
						},
						el( NumberControl, {
							label: __( 'Initial Value', 'interactivity-theme' ),
							value: attributes.initialValue,
							onChange: ( value: number ) =>
								setAttributes( { initialValue: value } ),
						} ),
						el( NumberControl, {
							label: __( 'Minimum', 'interactivity-theme' ),
							value: attributes.min,
							onChange: ( value: number ) =>
								setAttributes( { min: value } ),
						} ),
						el( NumberControl, {
							label: __( 'Maximum', 'interactivity-theme' ),
							value: attributes.max,
							onChange: ( value: number ) =>
								setAttributes( { max: value } ),
						} ),
						el( NumberControl, {
							label: __( 'Step', 'interactivity-theme' ),
							value: attributes.step,
							onChange: ( value: number ) =>
								setAttributes( { step: value } ),
						} )
					)
				),
				el(
					'div',
					{ className: 'wp-block-counter' },
					el(
						'div',
						{ className: 'wp-block-counter__display' },
						el(
							'span',
							{ className: 'wp-block-counter__value' },
							String( attributes.initialValue )
						)
					),
					el(
						'div',
						{ className: 'wp-block-counter__controls' },
						el(
							'button',
							{
								className:
									'wp-block-counter__button wp-block-counter__button--decrement',
								disabled: true,
							},
							'-'
						),
						el(
							'button',
							{
								className:
									'wp-block-counter__button wp-block-counter__button--reset',
							},
							'Reset'
						),
						el(
							'button',
							{
								className:
									'wp-block-counter__button wp-block-counter__button--increment',
							},
							'+'
						)
					)
				)
			);
		},

		save() {
			return null;
		},
	} );
} )();
