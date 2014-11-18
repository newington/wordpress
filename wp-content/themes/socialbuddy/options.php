<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 */

function optionsframework_option_name() {

	// This gets the theme name from the stylesheet
	$themename = get_option( 'stylesheet' );
	$themename = preg_replace("/\W/", "_", strtolower($themename) );

	$optionsframework_settings = get_option( 'optionsframework' );
	$optionsframework_settings['id'] = $themename;
	update_option( 'optionsframework', $optionsframework_settings );
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 * If you are making your theme translatable, you should replace 'options_framework_theme'
 * with the actual text domain for your theme.  Read more:
 * http://codex.wordpress.org/Function_Reference/load_theme_textdomain
 */

function optionsframework_options() {

	// Test data
	$test_array = array(
		'one' => __('One', 'options_framework_theme'),
		'two' => __('Two', 'options_framework_theme'),
		'three' => __('Three', 'options_framework_theme'),
		'four' => __('Four', 'options_framework_theme'),
		'five' => __('Five', 'options_framework_theme')
	);

	// Multicheck Array
	$multicheck_array = array(
		'one' => __('French Toast', 'options_framework_theme'),
		'two' => __('Pancake', 'options_framework_theme'),
		'three' => __('Omelette', 'options_framework_theme'),
		'four' => __('Crepe', 'options_framework_theme'),
		'five' => __('Waffle', 'options_framework_theme')
	);

	// Multicheck Defaults
	$multicheck_defaults = array(
		'one' => '1',
		'five' => '1'
	);

	// Background Defaults
	$background_defaults = array(
		'color' => '',
		'image' => get_template_directory_uri() . '/images/bg.gif',
		'repeat' => 'repeat',
		'position' => 'center center',
		'attachment'=>'scroll' );

	// Typography Defaults
	$typography_defaults = array(
		'size' => '15px',
		'face' => 'georgia',
		'style' => 'bold',
		'color' => '#bada55' );
		
	// Typography Options
	$typography_options = array(
		'sizes' => array( '6','12','14','16','20' ),
		'faces' => array( 'Helvetica Neue' => 'Helvetica Neue','Arial' => 'Arial' ),
		'styles' => array( 'normal' => 'Normal','bold' => 'Bold' ),
		'color' => false
	);

	// Pull all the categories into an array
	$options_categories = array();
	$options_categories_obj = get_categories( array( 'hide_empty' => 0, ) );
	foreach ($options_categories_obj as $category) {
		$options_categories[$category->cat_ID] = $category->cat_name;
	}
	
	// Pull all tags into an array
	$options_tags = array();
	$options_tags_obj = get_tags();
	foreach ( $options_tags_obj as $tag ) {
		$options_tags[$tag->term_id] = $tag->name;
	}

	// Pull all the pages into an array
	$options_pages = array();
	$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
	$options_pages[''] = 'Select a page:';
	foreach ($options_pages_obj as $page) {
		$options_pages[$page->ID] = $page->post_title;
	}
	
	$wp_editor_settings = array(
		'wpautop' => true, // Default
		'textarea_rows' => 5,
		'tinymce' => array( 'plugins' => 'wordpress' )
	);
	
	$wp_editor_small = array(
		'wpautop' => true, // Default
		'textarea_rows' => 2,
		'tinymce' => array( 'plugins' => 'wordpress' )
	);

	// If using image radio buttons, define a directory path
	$imagepath =  get_template_directory_uri() . '/framework/images/';
		
	$options = array();
	

	$options[] = array( "name" => __("Homepage Options", "framework"),
						"type" => "heading");
						
	$options[] = array( "name" => __("Homepage Callout (First Line)", "framework"),
						"desc" => __("The first line of the callout. (optional - if left blank no callout will be displayed).", "framework"),
						"id" => "st_callout",
						"std" => "Join the world's largest buddy site today!",
						"type" => "text");
						
	$options[] = array( "name" => __("Homepage Callout (Second Line)", "framework"),
						"desc" => __("The text that appears below the callout. ", "framework"),
						"id" => "st_callout_biline",
						"std" => 'We create WordPress themes for ThemeForest. All equipped with useful features to make them customize easier.',
						'type' => 'editor',
						'settings' => $wp_editor_settings );
						
	$options[] = array( "name" => __("Homepage Callout Button Text", "framework"),
						"desc" => __("The callout button text (optional - if left blank no callout button will be displayed).", "framework"),
						"id" => "st_callout_button_txt",
						"std" => "Sign Up Now",
						"type" => "text");
						
	$options[] = array( "name" => __("Homepage Callout Button Link", "framework"),
						"desc" => __("The callout button link.", "framework"),
						"id" => "st_callout_button_link",
						"std" => "http://herothemes.com",
						"type" => "text");
						
	$options[] = array( "name" => __("Homepage Feature Blocks Layout", "framework"),
						"desc" => __("Select the layout of feature blocks. (Add your feature blocks in WP-Admin > Homepage Feature Blocks > Add New)", "framework"),
						"id" => "st_hp_feature_block_layout",
						"std" => "3col",
						"type" => "images",
						"options" => array(
						"2col" => $imagepath . "2col.png",
						"3col" => $imagepath . "3col.png",
						"4col" => $imagepath . "4col.png")
						);	
						
	$options[] = array( "name" => __("Homepage Widget Blocks Layout", "framework"),
						"desc" => __("Select the layout of homepage widget blocks.", "framework"),
						"id" => "st_hp_widget_block_layout",
						"std" => "3col",
						"type" => "images",
						"options" => array(
						"2col" => $imagepath . "2col.png",
						"3col" => $imagepath . "3col.png",
						"4col" => $imagepath . "4col.png")
						);	
						
	
			
					
	$options[] = array( "name" => __("Blog Options", "framework"),
						"type" => "heading");
						
	$options[] = array( "name" => __("Blog Title", "framework"),
						"desc" => __("The title of your blog. (Will appear in the page header of all blog pages)", "framework"),
						"id" => "st_blog_title",
						"std" => "Our Blog",
						"type" => "text");
						
	$options[] = array(
						'name' => __('Show Author Box?', 'framework'),
						'desc' => __('Check to show an author box at the end of blog posts. (Note: The author must have a bio for the box to show).', 'framework'),
						'id' => 'st_single_authorbox',
						'std' => '1',
						'type' => 'checkbox');

	$options[] = array(
						'name' => __('Show Related Posts', 'framework'),
						'desc' => __('Check to show a related posts box at the end of blog posts.', 'framework'),
						'id' => 'st_single_related',
						'std' => '1',
						'type' => 'checkbox');	
						
	$options[] = array( "name" => __("Forum Options (BBPress)", "framework"),
						"type" => "heading");
						
	$options[] = array(
						'name' => __('Forum Title', 'framework'),
						'desc' => __('A text input field.', 'framework'),
						'id' => 'st_forum_title',
						'std' => 'Community Forum',
						'type' => 'text');
						
						
	$options[] = array( "name" => __("Sidebar Position", "framework"),
						"desc" => __("Select the sidebar position for forum pages.", "framework"),
						"id" => "st_forum_sidebar",
						"std" => "right",
						"type" => "images",
						"options" => array(
						"left" => $imagepath . "sidebar-left.png",
						"right" => $imagepath . "sidebar-right.png",
						"off" => $imagepath . "sidebar-off.png")
						);	
						
						
	$options[] = array( "name" => __("BuddyPress Options", "framework"),
						"type" => "heading");								
					
					
	$options[] = array( "name" => __("Member Profile Sidebar Position", "framework"),
						"desc" => __("Select the sidebar position for BuddyPress member pages.", "framework"),
						"id" => "st_bp_member_single_sidebar",
						"std" => "off",
						"type" => "images",
						"options" => array(
						"left" => $imagepath . "sidebar-left.png",
						"right" => $imagepath . "sidebar-right.png",
						"off" => $imagepath . "sidebar-off.png")
						);	
							
						

	$options[] = array( "name" => __("Styling Options", "framework"),
						"type" => "heading");
						
	$options[] = array( "name" => __("Custom Logo", "framework"),
						"desc" => __("Upload a custom logo for your Website.", "framework"),
						"id" => "st_logo",
						"type" => "upload");
						
	$options[] = array( "name" => __("Custom Favicon", "framework"),
						"desc" => __("Upload a 16px x 16px png/ico image that will represent your website's favicon.", "framework"),
						"id" => "st_custom_favicon",
						"type" => "upload");
						
	$options[] = array(
						'name' => __('Background Color/Image', 'framework'),
						'desc' => __('Change the background CSS.', 'framework'),
						'id' => 'st_background_color',
						'std' => array(
									'color' => '#e2e2e2',
									'image' => '',
									'repeat' => 'repeat',
									'position' => 'top center',
									'attachment'=>'scroll' ),
						'type' => 'background' );
						
	$options[] = array( "name" => __("Header Color/Image", "framework"),
						"desc" => __("Select the header background color.", "framework"),
						"id" => "st_header_color",
						'std' => array(
									'color' => '#242E33',
									'image' => '',
									'repeat' => 'repeat',
									'position' => 'top center',
									'attachment'=>'scroll' ),
						"type" => "background");
						
	$options[] = array( "name" => __("Theme Color", "framework"),
						"desc" => __("Select the theme color.", "framework"),
						"id" => "st_theme_color",
						"std" => "#e55005",
						"type" => "color");
						
	$options[] = array( "name" => __("Link Color", "framework"),
						"desc" => __("Select the link color.", "framework"),
						"id" => "st_link_color",
						"std" => "#0086b3",
						"type" => "color");
						
	$options[] = array( "name" => __("Footer Widget Columns", "framework"),
						"desc" => __("Select the number of column the footer widget should be displayed in.", "framework"),
						"id" => "st_footer_widgets_layout",
						"std" => "3col",
						"type" => "images",
						"options" => array(
						"2col" => $imagepath . "2col.png",
						"3col" => $imagepath . "3col.png",
						"4col" => $imagepath . "4col.png")
						);	
						
	$options[] = array( "name" => __("Footer Copyright", "framework"),
						"desc" => __("The copyright notice that appears at the bottom of your website.", "framework"),
						"id" => "st_footer_copyright",
						"std" => '&copy;Copyright 2012, A <a href="http://herothemes.com">Hero Theme</a>.',
						'type' => 'editor',
						'settings' => $wp_editor_settings );
						
	$options[] = array( "name" => __("Custom CSS", "framework"),
						"desc" => __("Add some CSS to your theme by adding it to this block.", "framework"),
						"id" => "st_custom_css",
						"std" => "",
						"type" => "textarea");
						


	return $options;
}



/*
 * This is an example of how to add custom scripts to the options panel.
 * This example shows/hides an option when a checkbox is clicked.
 */

add_action('optionsframework_custom_scripts', 'optionsframework_custom_scripts');

function optionsframework_custom_scripts() { ?>

<script type="text/javascript">
jQuery(document).ready(function($) {

	$('#st_layout').click(function() {
  		$('#section-example_text_hidden').fadeToggle(400);
	});

	if ($('#st_layout:checked').val() !== undefined) {
		$('#section-example_text_hidden').show();
	}


});
</script>

<?php
}