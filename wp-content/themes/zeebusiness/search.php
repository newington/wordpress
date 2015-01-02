<?php get_header(); ?>

	<div id="content">
		
		<?php if (have_posts()) : ?>
		<h2 class="arh"><?php _e('Search results for ', 'themezee_lang'); echo get_search_query(); ?></h2>
		
		<?php while (have_posts()) : the_post();
		
			get_template_part( 'loop', 'index' );
		
		endwhile; ?>
			
			<?php if(function_exists('wp_pagenavi')) { // if PageNavi is activated ?>
				<div class="more_posts">
					<?php wp_pagenavi(); ?>
				</div>
			<?php } else { // Otherwise, use traditional Navigation ?>
				<div class="more_posts">
					<span class="post_links"><?php next_posts_link(__('&laquo; Older Entries', 'themezee_lang')) ?> &nbsp; <?php previous_posts_link (__('Recent Entries &raquo;', 'themezee_lang')) ?></span>
				</div>
			<?php }?>

			<?php else : ?>

			<h2 class="arh"><?php _e('Search results for ', 'themezee_lang'); echo get_search_query(); ?></h2>
			
			<div class="post">
				
				<div class="entry">
					<p><?php _e('No matches. Please try again, or use the navigation menus to find what you search for.', 'themezee_lang'); ?></p>
				</div>
				
			</div>

			<?php endif; ?>
			
	</div>
		
	<?php get_sidebar(); ?>
<?php get_footer(); ?>	