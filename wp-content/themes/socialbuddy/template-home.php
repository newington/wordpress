<?php
/*
Template Name: Home
*/
?>
<?php get_header(); ?>


<?php	$args = array(
		'post_type' => 'st_hpslider',
		'posts_per_page' => '-1',
		'orderby' => 'menu_order',
			'order' => 'ASC',
			'paged' => $paged
		);
	$wp_query = new WP_Query($args);
	if($wp_query->have_posts()) : ?>
<!-- #hpslider -->
<div id="hpslider" class="flexslider">
<div class="container">
<?php st_hpslider(); ?>
    <ul class="slides">    
   <?php while($wp_query->have_posts()) : $wp_query->the_post(); ?>

<li>
     <?php if (get_post_meta(get_the_ID(), 'st_hpslider_link', true) ) { ?><a href="<?php echo get_post_meta(get_the_ID(), 'st_hpslider_link', true) ?>"><?php } ?>
     
      <?php the_post_thumbnail('slider'); ?>
      
              <?php if ( (get_post_meta(get_the_ID(), 'st_hpslider_caption_title', true) == '') && ( get_post_meta(get_the_ID(), 'st_hpslider_caption', true) == '') ) { ?>
              
              <?php } else { ?>
              <div class="flex-caption <?php echo get_post_meta(get_the_ID(), 'st_hpslider_caption_pos', true) ?>">
              
                <?php if (get_post_meta(get_the_ID(), 'st_hpslider_caption_title', true) ) { ?>
                <h2><?php echo get_post_meta(get_the_ID(), 'st_hpslider_caption_title', true) ?></h2>
                <?php } ?>
                
                <?php if (get_post_meta(get_the_ID(), 'st_hpslider_caption', true) ) { ?>
                <p><?php echo get_post_meta(get_the_ID(), 'st_hpslider_caption', true) ?></p>
                <?php } ?>
                
              </div>
              <?php } ?>
              
       <?php if (get_post_meta(get_the_ID(), 'st_hpslider_link', true) != '') { ?></a><?php } ?>
       </li>
              
<?php endwhile; ?>
</ul>
</div>
</div>
<!-- /#hpslider -->
<?php endif; wp_reset_postdata(); ?>


<!-- #site-container -->
<div id="site-container" class="clearfix">

<!-- #primary -->
<div id="primary" class="container sidebar-off clearfix"> 
  <!-- #content -->
  <div id="content" role="main">
 
<?php get_template_part( 'hp', 'callout' ); ?>	

<?php get_template_part( 'hp', 'feature-blocks' ); ?>

<?php get_template_part( 'hp', 'widget-block' ); ?>


</div>
<!-- /#content -->

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer(); ?>
