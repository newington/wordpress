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


<?php if ( have_posts() ) : ?>

<?php /* Start the Loop */ ?>
<?php while ( have_posts() ) : the_post(); ?>

<?php get_template_part( 'content', get_post_format() ); ?>

<?php endwhile; ?>

<?php st_content_nav( 'nav-below' ); ?>

<?php else : ?>

<?php get_template_part( 'content', 'none' ); ?>

<?php endif; ?>
      
</div>
<!-- /#content -->

<?php if ($st_page_sidebar_pos != 'off') {
  get_sidebar();
} ?>

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer(); ?>