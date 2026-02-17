<?php
/**
 * Accordion Block Render Template
 *
 * Variables available: $attributes, $content, $block
 *
 * @package Interactivity_Theme
 */

$items = isset( $attributes['items'] ) ? $attributes['items'] : array(
	array(
		'title'   => __( 'Section 1', 'interactivity-theme' ),
		'content' => __( 'This is the content for section 1.', 'interactivity-theme' ),
	),
	array(
		'title'   => __( 'Section 2', 'interactivity-theme' ),
		'content' => __( 'This is the content for section 2.', 'interactivity-theme' ),
	),
	array(
		'title'   => __( 'Section 3', 'interactivity-theme' ),
		'content' => __( 'This is the content for section 3.', 'interactivity-theme' ),
	),
);

$allow_multiple = isset( $attributes['allowMultiple'] ) && $attributes['allowMultiple'];
?>

<div
	data-wp-interactive="interactivity-theme/accordion"
	class="wp-block-accordion"
>
	<?php
	foreach ( $items as $index => $item ) :
		$item_context = array(
			'itemIndex'     => $index,
			'isOpen'        => false,
			'allowMultiple' => $allow_multiple,
		);
		?>
		<div
			class="wp-block-accordion__item"
			<?php echo wp_interactivity_data_wp_context( $item_context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_interactivity_data_wp_context escapes for JSON attribute. ?>
		>
			<button
				class="wp-block-accordion__header"
				data-wp-on--click="actions.toggleItem"
				data-wp-bind--aria-expanded="context.isOpen"
				aria-expanded="false"
				aria-controls="accordion-content-<?php echo esc_attr( $index ); ?>"
			>
				<span class="wp-block-accordion__title">
					<?php echo esc_html( $item['title'] ); ?>
				</span>
				<span
					class="wp-block-accordion__icon"
					data-wp-class--is-open="context.isOpen"
				>
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
						<path d="M8 11L3 6h10l-5 5z"/>
					</svg>
				</span>
			</button>

			<div
				id="accordion-content-<?php echo esc_attr( $index ); ?>"
				class="wp-block-accordion__content"
				data-wp-class--is-open="context.isOpen"
				role="region"
			>
				<div class="wp-block-accordion__body">
					<?php echo wp_kses_post( $item['content'] ); ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
