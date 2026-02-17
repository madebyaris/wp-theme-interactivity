/**
 * Counter Block - View Script
 *
 * A simple counter demonstrating Interactivity API state management.
 *
 * @package
 */

import { store, getContext } from '@wordpress/interactivity';

interface CounterContext {
	count: number;
	min: number;
	max: number;
	step: number;
}

store( 'interactivity-theme/counter', {
	actions: {
		increment() {
			const context = getContext< CounterContext >();
			const next = context.count + context.step;
			if ( next <= context.max ) {
				context.count = next;
			}
		},

		decrement() {
			const context = getContext< CounterContext >();
			const next = context.count - context.step;
			if ( next >= context.min ) {
				context.count = next;
			}
		},

		reset() {
			const context = getContext< CounterContext >();
			context.count = context.min;
		},
	},

	callbacks: {
		isMin() {
			const context = getContext< CounterContext >();
			return context.count <= context.min;
		},

		isMax() {
			const context = getContext< CounterContext >();
			return context.count >= context.max;
		},
	},
} );
