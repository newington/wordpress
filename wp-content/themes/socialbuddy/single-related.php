    <?php $orig_post = $post;
    global $post;
    $categories = get_the_category($post->ID);
    if ($categories) {
    $category_ids = array();
    foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;

    $args=array(
    'category__in' => $category_ids,
    'post__not_in' => array($post->ID),
    'posts_per_page'=> 3, // Number of related posts that will be shown.
    'ignore_sticky_posts'=>1
    );

    $my_query = new wp_query( $args );
    if( $my_query->have_posts() ) { ?>
   <section id="related-posts">
   <h3 class="widget-title"><?php _e('You Might Also Like', 'framework') ?></h3>
   <div class="row-fixed half-gutter">
    <?php
    while( $my_query->have_posts() ) {
    $my_query->the_post();?>
    
    

        <div class="column col-third">

    <?php if ( has_post_thumbnail() ) { ?>
    <div class="entry-thumb"><a class="overlay" href="<?php the_permalink()?>" rel="nofollow" title="<?php the_title(); ?>"><?php the_post_thumbnail('post-thumb'); ?><div class="overlay-caption"></div></a></div>
    <?php } else { ?>
    <div class="entry-thumb no-thumb <?php echo get_post_format() ?>">
     <a class="overlay" rel="nofollow" href="<?php the_permalink(); ?>">
     <div class="overlay-caption"></div>
     </a>
     </div>
    <?php } ?>
    <h4 class="entry-title"><a href="<?php the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h4>
    
    </div>
    <?php
    }?>
   </div></section>
   <?php }
    }
    $post = $orig_post;
    wp_reset_query(); ?>
   