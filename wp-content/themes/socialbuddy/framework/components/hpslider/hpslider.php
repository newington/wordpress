<?php

/*-----------------------------------------------------------------------------------*/
/*	Add Slider Post Types
/*-----------------------------------------------------------------------------------*/
add_action( 'init', 'st_create_hpslider_post_type' );

function st_create_hpslider_post_type() {
	

	//Register Portfolio Post Type
	register_post_type( 'st_hpslider',
		array(
		'description' => __( '', 'framework' ),
		'labels' => array(
				'name' => __( 'Homepage Slider', 'framework' ),
				'singular_name' => __( 'Homepage Slider', 'framework' ),
				'add_new' => _x('Add New Slide', 'framework'),  
  				'add_new_item' => __('Add New Slide', 'framework'),  
   				'edit_item' => __('Edit Slide', 'framework'),  
   				'new_item' => __('New Slide', 'framework'),  
   				'view_item' => __('View Slider', 'framework'),  
   				'search_items' => __('Search Slider', 'framework'),  
   				'not_found' =>  __('No slide found', 'framework'),  
   				'not_found_in_trash' => __('No slide found in Trash', 'framework')
			),
		'public' => true,
        'menu_position' => 6,
		'supports' => array(
			'title',
			'page-attributes',
			'thumbnail'),
		'public' => true,
		'show_ui' => true,
		'publicly_queryable' => false,
		'exclude_from_search' => true
		)
	);
}


/*-----------------------------------------------------------------------------------*/
/*	Add featured image thumbnail column to admin panel
/*-----------------------------------------------------------------------------------*/

// Save the featured image for later
    function st_get_featured_image($post_ID) {  
        $st_post_thumbnail_id = get_post_thumbnail_id($post_ID);  
        if ($st_post_thumbnail_id) {  
            $st_post_thumbnail_img = wp_get_attachment_image_src($st_post_thumbnail_id, 'post-thumbnail');  
            return $st_post_thumbnail_img[0];  
        }  
    }  

//Portfolio Post Type
function st_hpslider_edit_columns($columns){  

        $columns = array(  
            "cb" => "<input type=\"checkbox\" />",  
            "title" => __( 'Slide', 'framework' ),
			"featured_image" => __( 'Slide Image', 'framework' )
        );  
  
        return $columns;  
}  
  
function st_hpslider_custom_columns($column){  
        global $post;  
        switch ($column)  
        {    
				
			case __( 'featured_image', 'framework' ):  
				$st_post_featured_image = st_get_featured_image($post_ID);  
        			if ($st_post_featured_image) {  
            	echo '<img src="' . $st_post_featured_image . '" />';  
					}
        }  
}
add_filter("manage_edit-st_hpslider_columns", "st_hpslider_edit_columns");  
add_action("manage_st_hpslider_posts_custom_column",  "st_hpslider_custom_columns");


/*-----------------------------------------------------------------------------------*/
/*	Homepage Slider Script Function
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'st_hpslider' ) ) {
function st_hpslider(){
	 wp_enqueue_script('flexslider');
?>
<script type="text/javascript" charset="utf-8">
  jQuery(window).load(function() {
    jQuery('#hpslider').flexslider({
		keyboardNav: false,
		prevText: "&lt;",
		nextText: "&gt;",
		controlNav: true,               
		directionNav: false
	});
  });
</script>
<?php }
}



/*-----------------------------------------------------------------------------------*/
/*	Homepage Post Meta
/*-----------------------------------------------------------------------------------*/

function st_hp_slider_metaboxes( $meta_boxes ) {
	$prefix = 'st_'; // Prefix for all fields
	$meta_boxes[] = array(
		'id' => 'st_meta_box_hpslider',
		'title' => 'Slide Options',
		'pages' => array('st_hpslider'), // post type
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(
			array(
				'name' => 'Slide Title',
				'desc' => 'Enter the slide title (optional - is shown above the caption)',
				'id'   => $prefix . 'hpslider_caption_title',
				'type' => 'text_medium',
			),
			array(
				'name' => 'Slide Caption',
				'desc' => 'Enter the slide caption (optional)',
				'id'   => $prefix . 'hpslider_caption',
				'type' => 'textarea_small',
			),
			array(
				'name'    => 'Caption Position',
				'desc'    => 'Select where you would like the caption to be positioned.',
				'id'      => $prefix . 'hpslider_caption_pos',
				'type'    => 'radio_inline',
				'options' => array(
					array( 'name' => 'Left', 'value' => 'left', ),
					array( 'name' => 'Centered', 'value' => 'center', ),
					array( 'name' => 'Right', 'value' => 'right', ),
				),
			),
			array(
				'name' => 'Slide Link',
				'desc' => 'Enter the slide title (optional - is shown above the caption)',
				'id'   => $prefix . 'hpslider_link',
				'type' => 'text_medium',
			),

		),
	);

	return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'st_hp_slider_metaboxes' );

