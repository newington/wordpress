<?php get_header(); ?>

<?php 
// get the id of the posts page
$st_index_id = get_option('page_for_posts');
$st_page_sidebar_pos = get_post_meta( $st_index_id, '_st_page_sidebar', true );
if (empty( $st_page_sidebar_pos )) {
$st_page_sidebar_pos = 'right';	
}
?>

<?php get_template_part( 'page-header' ); ?>

<!-- #site-container -->
<div id="site-container" class="clearfix">

<!-- #primary -->
<div id="primary" class="sidebar-<?php echo $st_page_sidebar_pos; ?> clearfix"> 

<!-- #content -->
<div id="content" role="main">
  
    <?php while ( have_posts() ) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>> 
    
    <h1 class="entry-title">
        <?php the_title(); ?>
      </h1>
      <?php get_template_part('content', 'meta'); ?>
      
      <?php if ( 'has_post_thumbnail' ) { ?>
  
  <div class="entry-thumb"> 
  <a class="overlay" href="<?php the_permalink(); ?>" rel="nofollow">
    <?php the_post_thumbnail( 'post' ); ?>
    <div class="overlay-caption"></div>
    </a>
    </div>

      <?php } ?>
      
      <div class="entry-content clearfix">
        <?php the_content(); ?>
        <?php wp_link_pages( array( 'before' => '<span class="div-small"></span><div class="page-links">' . __( 'Pages:', 'framework' ), 'after' => '</div>' ) ); ?>
      </div>
      <?php if (is_single() && has_tag()) { ?>
      <div class="tags">
        <?php the_tags('Tagged: ','',''); ?>
      </div>
      <?php } ?>
    </article>
    <?php if (of_get_option('st_single_authorbox')) { ?>
    <?php get_template_part('single', 'author-bio'); ?>
    <?php } ?>
    <?php if (of_get_option('st_single_related')) { ?>
    <?php get_template_part('single', 'related'); ?>
    <?php } ?>
    <?php
					// If comments are open or we have at least one comment, load up the comment template
					if ( comments_open() || '0' != get_comments_number() )
						comments_template( '', true );
				?>
    <?php endwhile; // end of the loop. ?>
    
</div>
<!-- #content -->
  
<?php if ($st_page_sidebar_pos != 'off') {
  get_sidebar();
} ?>

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer(); ?>