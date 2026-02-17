<?php
/**
 * Route content: single post (SPA + SSR)
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
			<div class="entry-meta">
				<span class="posted-on">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
				</span>
				<span class="byline">
					<?php esc_html_e( 'by', 'interactivity-theme' ); ?>
					<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
						<?php the_author(); ?>
					</a>
				</span>
				<?php
				if ( has_category() ) :
					?>
					<span class="cat-links">
						<?php esc_html_e( 'in', 'interactivity-theme' ); ?>
						<?php the_category( ', ' ); ?>
					</span>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-thumbnail">
				<?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
			</div>
		<?php endif; ?>

		<div class="entry-content">
			<?php
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'interactivity-theme' ),
						array( 'span' => array( 'class' => array() ) )
					),
					wp_kses_post( get_the_title() )
				)
			);
			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'interactivity-theme' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<footer class="entry-footer">
			<?php
			if ( has_tag() ) {
				the_tags( '<div class="post-tags"><span class="tags-links">', ', ', '</span></div>' );
			}
			the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'interactivity-theme' ) . '</span> <span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'interactivity-theme' ) . '</span> <span class="nav-title">%title</span>',
				)
			);
			?>
		</footer>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
endwhile;
