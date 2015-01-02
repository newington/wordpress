		
			<div <?php post_class(); ?>>
			
				<h2 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
			
				<div class="postmeta"><?php themezee_display_postmeta(); ?></div>

				<div class="entry">
					<a href="<?php the_permalink() ?>" rel="bookmark"><?php the_post_thumbnail('thumbnail', array('class' => 'alignleft')); ?></a>
					<?php the_excerpt(); ?>
					<div class="clear"></div>
				</div>
				
				<div class="postinfo"><?php themezee_display_postinfo(); ?></div>

			</div>