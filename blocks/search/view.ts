/**
 * Search Block - View Script
 *
 * Live search using the WordPress REST API and the Interactivity API.
 *
 * @package
 */

import { store, getContext } from '@wordpress/interactivity';

interface SearchContext {
	searchQuery: string;
	isLoading: boolean;
	showResults: boolean;
	resultsHtml: string;
}

let debounceTimer: ReturnType< typeof setTimeout > | undefined;

store( 'interactivity-theme/search', {
	actions: {
		handleInput( event: Event ) {
			const context = getContext< SearchContext >();
			const target = event.target as HTMLInputElement;
			const query = target.value.trim();

			context.searchQuery = query;

			clearTimeout( debounceTimer );

			if ( query.length < 2 ) {
				context.resultsHtml = '';
				context.showResults = false;
				context.isLoading = false;
				return;
			}

			context.isLoading = true;
			context.showResults = true;

			debounceTimer = setTimeout( async () => {
				try {
					const response = await fetch(
						`/wp-json/wp/v2/posts?search=${ encodeURIComponent(
							query
						) }&per_page=5&_embed`
					);

					if ( ! response.ok ) {
						throw new Error( 'Network response was not ok' );
					}

					const posts = ( await response.json() ) as Array< {
						id: number;
						title: { rendered: string };
						excerpt: { rendered: string };
						link: string;
					} >;

					if ( posts.length > 0 ) {
						context.resultsHtml = posts
							.map(
								( post ) =>
									`<a href="${ post.link }" class="wp-block-search__result-item">` +
									`<div class="wp-block-search__result-title">${ post.title.rendered }</div>` +
									`<div class="wp-block-search__result-excerpt">${ post.excerpt.rendered.substring(
										0,
										120
									) }&hellip;</div>` +
									`</a>`
							)
							.join( '' );
					} else {
						context.resultsHtml =
							'<div class="wp-block-search__no-results">No results found.</div>';
					}
				} catch {
					context.resultsHtml =
						'<div class="wp-block-search__error">An error occurred while searching.</div>';
				} finally {
					context.isLoading = false;
				}
			}, 300 );
		},

		showResults() {
			const context = getContext< SearchContext >();
			if ( context.searchQuery.length >= 2 ) {
				context.showResults = true;
			}
		},

		hideResults() {
			// Small delay so clicks on results can register
			setTimeout( () => {
				const context = getContext< SearchContext >();
				context.showResults = false;
			}, 200 );
		},

		clearSearch() {
			const context = getContext< SearchContext >();
			context.searchQuery = '';
			context.resultsHtml = '';
			context.showResults = false;
			context.isLoading = false;
		},
	},
} );
