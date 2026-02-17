<?php
/**
 * Search Block Render Template
 *
 * Variables available: $attributes, $content, $block
 *
 * @package Interactivity_Theme
 */

$placeholder = isset( $attributes['placeholder'] )
	? $attributes['placeholder']
	: __( 'Search...', 'interactivity-theme' );

$context = array(
	'searchQuery' => '',
	'isLoading'   => false,
	'showResults' => false,
	'resultsHtml' => '',
);
?>

<div
	<?php echo wp_interactivity_data_wp_context( $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_interactivity_data_wp_context escapes for JSON attribute. ?>
	data-wp-interactive="interactivity-theme/search"
	class="wp-block-search"
>
	<div class="wp-block-search__input-wrapper">
		<input
			type="search"
			class="wp-block-search__input"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			data-wp-on--input="actions.handleInput"
			data-wp-on--keydown="actions.handleKeydown"
			data-wp-bind--value="context.searchQuery"
			data-wp-on--focus="actions.showResults"
			data-wp-on--blur="actions.hideResults"
			aria-label="<?php echo esc_attr( $placeholder ); ?>"
		/>

		<span
			class="wp-block-search__loading-indicator"
			data-wp-class--is-loading="context.isLoading"
		>
			<svg class="spinner" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
				<circle class="path" cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2"></circle>
			</svg>
		</span>

		<button
			type="button"
			class="wp-block-search__button"
			data-wp-on--click="actions.submitSearch"
			aria-label="<?php esc_attr_e( 'Search', 'interactivity-theme' ); ?>"
		>
			<?php esc_html_e( 'Search', 'interactivity-theme' ); ?>
		</button>
	</div>

	<div
		class="wp-block-search__results"
		data-wp-class--is-visible="context.showResults"
	>
		<div data-wp-html="context.resultsHtml"></div>
	</div>
</div>
