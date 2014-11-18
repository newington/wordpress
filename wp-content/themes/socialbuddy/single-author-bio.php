<?php 
if (get_the_author_meta('description') != '') { ?>
      <section id="entry-author" class="clearfix">
        <h3 class="widget-title"><?php _e('About The Author', 'framework') ?></h3>
        <div class="gravatar">
          <?php if(function_exists('get_avatar')) { echo get_avatar( get_the_author_meta('email'), '70' );   } ?>
        </div>
        <h4><?php the_author_posts_link() ?></h4>
        <div class="entry-author-desc">
          <?php the_author_meta('description') ?>
        </div>
      </section>
<?php }  ?>