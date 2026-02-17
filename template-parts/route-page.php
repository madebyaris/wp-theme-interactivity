<?php
/**
 * Route content: single page (SPA + SSR)
 * Uses theme/Gutenberg design - the_content() for block output.
 *
 * @package Interactivity_Theme
 */

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-thumbnail">
				<?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
			</div>
		<?php endif; ?>

		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'interactivity-theme' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<?php
		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: post title */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'interactivity-theme' ),
					array( 'span' => array( 'class' => array() ) )
				),
				wp_kses_post( get_the_title() )
			),
			'<footer class="entry-footer"><span class="edit-link">',
			'</span></footer>'
		);
		?>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
endwhile;
