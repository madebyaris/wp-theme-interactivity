<?php
/**
 * Counter Block Render Template
 *
 * Variables available: $attributes, $content, $block
 *
 * @package Interactivity_Theme
 */

$initial_value = isset( $attributes['initialValue'] ) ? intval( $attributes['initialValue'] ) : 0;
$min           = isset( $attributes['min'] ) ? intval( $attributes['min'] ) : 0;
$max           = isset( $attributes['max'] ) ? intval( $attributes['max'] ) : 100;
$step          = isset( $attributes['step'] ) ? intval( $attributes['step'] ) : 1;

$context = array(
	'count' => $initial_value,
	'min'   => $min,
	'max'   => $max,
	'step'  => $step,
);
?>

<div
	<?php echo wp_interactivity_data_wp_context( $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_interactivity_data_wp_context escapes for JSON attribute. ?>
	data-wp-interactive="interactivity-theme/counter"
	class="wp-block-counter"
>
	<div class="wp-block-counter__display">
		<span
			class="wp-block-counter__value"
			data-wp-text="context.count"
		>
			<?php echo esc_html( $initial_value ); ?>
		</span>
	</div>

	<div class="wp-block-counter__controls">
		<button
			class="wp-block-counter__button wp-block-counter__button--decrement"
			data-wp-on--click="actions.decrement"
			data-wp-bind--disabled="callbacks.isMin"
			aria-label="<?php esc_attr_e( 'Decrease value', 'interactivity-theme' ); ?>"
		>
			<span aria-hidden="true">&minus;</span>
		</button>

		<button
			class="wp-block-counter__button wp-block-counter__button--reset"
			data-wp-on--click="actions.reset"
			aria-label="<?php esc_attr_e( 'Reset counter', 'interactivity-theme' ); ?>"
		>
			<?php esc_html_e( 'Reset', 'interactivity-theme' ); ?>
		</button>

		<button
			class="wp-block-counter__button wp-block-counter__button--increment"
			data-wp-on--click="actions.increment"
			data-wp-bind--disabled="callbacks.isMax"
			aria-label="<?php esc_attr_e( 'Increase value', 'interactivity-theme' ); ?>"
		>
			<span aria-hidden="true">+</span>
		</button>
	</div>
</div>
