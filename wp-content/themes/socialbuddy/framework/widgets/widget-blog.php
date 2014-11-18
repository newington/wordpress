<?php

// Add function to widgets_init that'll load our widget.
add_action( 'widgets_init', 'st_blog_widgets' );


// Register widget.
function st_blog_widgets() {
	register_widget( 'st_blog_widget' );
}

// Widget class.
class st_blog_widget extends WP_Widget {


/*-----------------------------------------------------------------------------------*/
/*	Widget Setup
/*-----------------------------------------------------------------------------------*/
	
	function st_blog_widget() {
	
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'st_blog_widget', 'description' => __('A widget that displays your latest posts with image.', 'framework') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'st_blog_widget' );

		/* Create the widget. */
		$this->WP_Widget( 'st_blog_widget', __('Custom Blog Widget', 'framework'), $widget_ops, $control_ops );
	}


/*-----------------------------------------------------------------------------------*/
/*	Display Widget
/*-----------------------------------------------------------------------------------*/
	
	function widget( $args, $instance ) {
		extract( $args );
		
		$title = apply_filters('widget_title', $instance['title'] );

		/* Our variables from the widget settings. */
		$number = $instance['number'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display Widget */
		?> 
        <?php /* Display the widget title if one was input (before and after defined by themes). */
				if ( $title )
					echo $before_title . $title . $after_title;
				?>
                            
                <ul class="clearfix">
                
					<?php 
                    $query = new WP_Query();
                    $query->query('posts_per_page='.$number.'&ignore_sticky_posts=1');
                    ?>
                    <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>
                    <li class="clearfix <?php if (  (function_exists('has_post_thumbnail')) && (has_post_thumbnail())  ) {  ?>has_thumb<?php } ?>">
                        
                        
                        <?php if (  (function_exists('has_post_thumbnail')) && (has_post_thumbnail())  ) {  ?>
                        <div class="entry-thumb">
                        <a href="<?php the_permalink(); ?>" rel="nofollow">
                        <?php the_post_thumbnail(); ?>
                        </a>
                        </div>
                        <?php } else { ?>
                        <div class="entry-thumb no-thumb <?php echo get_post_format() ?>">
                        <a href="<?php the_permalink(); ?>" rel="nofollow"><i class="icon-file-alt"></i></a>
                        </div>
                        <?php } ?>
                        
					<a class="entry-title" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
<div class="entry-meta"><time datetime="<?php the_time('Y-m-d')?>"><?php the_time( get_option('date_format') ); ?></time></div>
                    
                    </li>
                    <?php endwhile; endif; ?>
                    
                    <?php wp_reset_query(); ?>

                </ul>
		
		<?php

		/* After widget (defined by themes). */
		echo $after_widget;
	}


/*-----------------------------------------------------------------------------------*/
/*	Update Widget
/*-----------------------------------------------------------------------------------*/
	
	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
		/* Strip tags to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = strip_tags( $new_instance['number'] );

		/* No need to strip tags for.. */

		return $instance;
	}
	

/*-----------------------------------------------------------------------------------*/
/*	Widget Settings
/*-----------------------------------------------------------------------------------*/
	 
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
		'title' => 'Latest From The Blog',
		
		'number' => 4
		
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
        <!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'framework') ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
        
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e('Amount to show:', 'framework') ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>" />
		</p>

	
	<?php
	}
}
?>