
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
				<h2 class="post-title"><?php the_title(); ?></h2>
					
				<div class="postmeta"><?php themezee_display_postmeta(); ?></div>
				
				<div class="entry">
					<?php the_post_thumbnail('medium', array('class' => 'alignleft')); ?>
					<?php the_content(); ?>
					<div class="clear"></div>
					<?php wp_link_pages(); ?>
					<!-- <?php trackback_rdf(); ?> -->			
				</div>
				
				<div class="postinfo"><?php themezee_display_postinfo(); ?></div>

			</div>