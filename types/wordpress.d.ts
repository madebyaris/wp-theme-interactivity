/**
 * WordPress global type declarations.
 * The `wp` object is provided by WordPress at runtime in the block editor.
 *
 * @package
 */

declare const wp: {
	blocks: { registerBlockType: ( name: string, config: object ) => void };
	element: {
		createElement: ( type: unknown, props?: object, ...children: unknown[] ) => unknown;
		useState: <T>( initial: T ) => [ T, ( value: T ) => void ];
	};
	blockEditor: { InspectorControls: unknown };
	components: {
		PanelBody: unknown;
		ToggleControl: unknown;
		TextControl: unknown;
		TextareaControl: unknown;
		NumberControl: unknown;
	};
	i18n: { __: ( text: string, domain?: string ) => string };
};
