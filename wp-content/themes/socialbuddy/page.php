<?php get_header(); ?>

<?php 
$st_page_sidebar_pos = '';	
$st_page_sidebar_pos = get_post_meta( get_the_ID(), '_st_page_sidebar', true ); 
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
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <div class="entry-content">
        <?php the_content(); ?>
        <?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'framework' ), 'after' => '</div>' ) ); ?>
      </div>

    </article>
    
    <?php
	// If comments are open or we have at least one comment, load up the comment template
	if ( comments_open() || '0' != get_comments_number() )
	comments_template();
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