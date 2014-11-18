<?php
/**
 * The template for displaying 404 pages (Not Found).
**/
get_header(); ?>

<!-- #page-header -->
<div id="page-header" class="clearfix">
<div class="container">
<div id="page-header-content" class="clearfix">
<h1><?php _e( 'Oops! That page can&rsquo;t be found.', 'framework' ); ?></h1>
</div>
</div>
</div>
<!-- /#page-header -->

<!-- #primary -->
<div id="primary" class="sidebar-off container clearfix"> 

 <!-- #primary --> 
  <div id="content" role="main">
  
    <article id="post-0" class="post error404 not-found">
        <h2 class="entry-title">
          <?php _e( "Why don't you try searching?", "framework" ); ?>
        </h2>
      <div class="entry-content clearfix">
        <?php get_search_form(); ?>
     </div>

    </article>
     
  </div>
  <!-- /#primary-->
  

<?php get_footer(); ?>
