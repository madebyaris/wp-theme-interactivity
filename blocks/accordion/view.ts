/**
 * Accordion Block - View Script
 *
 * An accordion with collapsible sections using the Interactivity API.
 *
 * @package
 */

import { store, getContext, getElement } from '@wordpress/interactivity';

interface AccordionItemContext {
	itemIndex: number;
	isOpen: boolean;
	allowMultiple: boolean;
}

store( 'interactivity-theme/accordion', {
	actions: {
		toggleItem() {
			const context = getContext< AccordionItemContext >();
			const willOpen = ! context.isOpen;

			// When allowMultiple is false, close all other items first
			if ( willOpen && ! context.allowMultiple ) {
				const { ref } = getElement();
				// Walk up to the accordion wrapper, then close all sibling items
				const accordion = ref?.closest( '.wp-block-accordion' );
				if ( accordion ) {
					const items = accordion.querySelectorAll(
						'.wp-block-accordion__content.is-open'
					);
					items.forEach( ( el ) => {
						el.classList.remove( 'is-open' );
					} );
					const icons = accordion.querySelectorAll(
						'.wp-block-accordion__icon.is-open'
					);
					icons.forEach( ( el ) => {
						el.classList.remove( 'is-open' );
					} );
					// Reset aria-expanded on all headers
					const headers = accordion.querySelectorAll(
						'.wp-block-accordion__header'
					);
					headers.forEach( ( el ) => {
						el.setAttribute( 'aria-expanded', 'false' );
					} );
				}
			}

			context.isOpen = willOpen;
		},
	},
} );
