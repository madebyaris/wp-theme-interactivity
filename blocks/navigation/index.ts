/**
 * Navigation Block - Editor Script
 *
 * @package
 */

( function () {
	if ( typeof wp === 'undefined' ) {
		return;
	}

	const { registerBlockType } = wp.blocks;
	const { createElement: el } = wp.element;

	registerBlockType( 'interactivity-theme/navigation', {
		edit() {
			return el(
				'div',
				{ className: 'wp-block-interactivity-theme-navigation' },
				el( 'p', {}, 'Interactive Navigation Block' ),
				el(
					'p',
					{ className: 'description' },
					'This block renders a responsive navigation menu. The interactive toggle works on the frontend.'
				)
			);
		},
		save() {
			return null;
		},
	} );
} )();
