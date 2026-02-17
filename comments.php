<?php
/**
 * The template for displaying comments
 *
 * @package Interactivity_Theme
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php
	// You can start editing here -- including this comment!
	if ( have_comments() ) :
		?>
		<h2 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			if ( 1 === $comments_number ) {
				printf(
					/* translators: %s: Post title. */
					esc_html_x( 'One thought on &ldquo;%s&rdquo;', 'comments title', 'interactivity-theme' ),
					esc_html( get_the_title() )
				);
			} else {
				printf(
					esc_html(
						/* translators: 1: Number of comments, 2: Post title. */
						_nx(
							'%1$s thought on &ldquo;%2$s&rdquo;',
							'%1$s thoughts on &ldquo;%2$s&rdquo;',
							$comments_number,
							'comments title',
							'interactivity-theme'
						)
					),
					esc_html( number_format_i18n( $comments_number ) ),
					esc_html( get_the_title() )
				);
			}
			?>
		</h2>

		<?php the_comments_navigation(); ?>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation();

		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'interactivity-theme' ); ?></p>
			<?php
		endif;

	endif;

	comment_form();
	?>

</div>
