<?php

/*-----------------------------------------------------------------------------------*/
/*	Add Homepage Block Post Type
/*-----------------------------------------------------------------------------------*/
add_action( 'init', 'st_create_hpblock_post_type' );
function st_create_hpblock_post_type() {
	

	register_post_type( 'st_hpfeatures',
		array(
		'description' => '',
		'labels' => array(
				'name' => __( 'Homepage Feature Blocks', 'framework' ),
				'singular_name' => __( 'Homepage Feature Block', 'framework' ),
				'add_new' => __('Add New Feature Block', 'framework'),  
  				'add_new_item' => __('Add New Feature Block', 'framework'),  
   				'edit_item' => __('Edit Feature Block', 'framework'),  
   				'new_item' => __('New Feature Block', 'framework'),  
   				'view_item' => __('View Feature Block', 'framework'),  
   				'search_items' => __('Search Feature Block', 'framework'),  
   				'not_found' =>  __('No Feature Block found', 'framework'),  
   				'not_found_in_trash' => __('No Feature Block found in Trash', 'framework')
			),
		'public' => false,
        'menu_position' => 6,
		'supports' => array(
			'title',
			'editor',
			'page-attributes',
			'thumbnail'),
		'public' => true,
		'show_ui' => true,
		'publicly_queryable' => false,
		'exclude_from_search' => true
		)
	);
}



?>