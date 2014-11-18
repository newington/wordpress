<?php get_header(); ?>

<?php get_template_part( 'page-header' ); ?>

<!-- #site-container -->
<div id="site-container" class="clearfix">

<!-- #primary -->
<div id="primary" class="container sidebar-right clearfix"> 
  <!-- #content -->
  <div id="content" role="main">


<?php if ( have_posts() ) { ?>

<?php /* Start the Loop */ ?>
<?php while ( have_posts() ) : the_post(); ?>

<?php get_template_part( 'content', get_post_format() ); ?>
         			
<?php endwhile;  ?>

<?php st_content_nav( 'nav-below' );?>

<?php } else { ?>

<?php get_template_part( 'content', 'none' ); ?>

<?php } ?>
    
</div>
 <!-- /#content -->

<?php get_sidebar(); ?>

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer(); ?>