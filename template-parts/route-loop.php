<?php
/**
 * Route content: post loop (home, archive, search)
 * Uses theme/Gutenberg design - the_content()/the_excerpt() for block output.
 *
 * @package Interactivity_Theme
 */

if ( have_posts() ) :
	if ( is_home() && ! is_front_page() ) :
		?>
		<header>
			<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
		</header>
		<?php
	endif;

	if ( is_archive() ) :
		?>
		<header class="page-header">
			<?php
			the_archive_title( '<h1 class="page-title">', '</h1>' );
			the_archive_description( '<div class="archive-description">', '</div>' );
			?>
		</header>
		<?php
	endif;

	if ( is_search() ) :
		?>
		<header class="page-header">
			<h1 class="page-title">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Search Results for: %s', 'interactivity-theme' ),
					'<span>' . get_search_query() . '</span>'
				);
				?>
			</h1>
		</header>
		<?php
	endif;
	?>

	<div class="posts-list">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
				<header class="entry-header">
					<?php
					the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
					?>
					<div class="entry-meta">
						<span class="posted-on">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
								<?php echo esc_html( get_the_date() ); ?>
							</time>
						</span>
						<span class="byline"><?php esc_html_e( 'by', 'interactivity-theme' ); ?> <?php the_author(); ?></span>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="post-thumbnail">
						<a href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
						</a>
					</div>
				<?php endif; ?>

				<div class="entry-content">
					<?php the_excerpt(); ?>
				</div>

				<div class="read-more">
					<a href="<?php the_permalink(); ?>" class="button">
						<?php esc_html_e( 'Read More', 'interactivity-theme' ); ?>
					</a>
				</div>
			</article>
		<?php endwhile; ?>
	</div>

	<?php
	the_posts_navigation(
		array(
			'prev_text' => __( '&larr; Older Posts', 'interactivity-theme' ),
			'next_text' => __( 'Newer Posts &rarr;', 'interactivity-theme' ),
		)
	);
else :
	?>
	<section class="no-results not-found">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'interactivity-theme' ); ?></h1>
		</header>
		<div class="page-content">
			<?php if ( is_search() ) : ?>
				<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'interactivity-theme' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try a search?', 'interactivity-theme' ); ?></p>
			<?php endif; ?>
			<?php get_search_form(); ?>
		</div>
	</section>
	<?php
endif;
