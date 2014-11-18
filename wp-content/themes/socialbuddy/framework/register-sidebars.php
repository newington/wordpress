<?php
/**
 * Register Sidebars 
 */
 
add_action( 'widgets_init', 'st_register_sidebars' );

function st_register_sidebars() {
	register_sidebar(array(
		'name' => 'Default Sidebar',
		'description'   => 'This is the default sidebar that shows on all pages except BuddyPress & BBPress pages.',
		'id' => 'st_primary',
		'before_widget' => '<div id="%1$s" class="widget %2$s clearfix">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title"><span>',
		'after_title' => '</span></h4>',
		)
	);
	
	// Setup footer widget column option variable
	if (of_get_option('st_footer_widgets_layout') == '2col') {
		$footer_widget_col = 'col-half';
		$footer_widget_col_descirption = 'Two Columns';
	} elseif (of_get_option('st_footer_widgets_layout') == '3col') {
		$footer_widget_col = 'col-third';
		$footer_widget_col_descirption = 'Three Columns';
	} elseif (of_get_option('st_footer_widgets_layout') == '4col') {
		$footer_widget_col = 'col-fourth';
		$footer_widget_col_descirption = 'Fours Columns';
	} else {
		$footer_widget_col = 'col-third';
		$footer_widget_col_descirption = 'Three Columns';
	}
	
	register_sidebar(array(
		'name' => 'Footer Widgets',
		'description'   => 'The footer widget area is currently set to: '.$footer_widget_col_descirption.'. To change it go to the theme options panel.',
		'id' => 'st_footer',
		'before_widget' => '<div id="%1$s" class="column '.$footer_widget_col.' widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title"><span>',
		'after_title' => '</span></h4>',
		)
	);
	
	// Setup footer widget column option variable
	if (of_get_option('st_hp_widget_block_layout') == '2col') {
		$hp_widget_col = 'col-half';
		$hp_widget_col_descirption = 'Two Columns';
	} elseif (of_get_option('st_hp_widget_block_layout') == '3col') {
		$hp_widget_col = 'col-third';
		$hp_widget_col_descirption = 'Three Columns';
	} elseif (of_get_option('st_hp_widget_block_layout') == '4col') {
		$hp_widget_col = 'col-fourth';
		$hp_widget_col_descirption = 'Fours Columns';
	} else {
		$hp_widget_col = 'col-third';
		$hp_widget_col_descirption = 'Three Columns';
	}
	
	register_sidebar(array(
		'name' => 'Homepage Widgets',
		'description'   => 'The homepage widget area is currently set to: '.$hp_widget_col_descirption.'. To change it go to the theme options panel.',
		'id' => 'st_homepage_widgets',
		'before_widget' => '<div id="%1$s" class="column '.$hp_widget_col.' widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title"><span>',
		'after_title' => '</span></h4>',
		)
	);
	
	
	register_sidebar(array(
		'name' => 'BuddyPress Sidebar',
		'description'   => 'This is the sidebar for BuddyPress pages,',
		'id' => 'st_buddypress_sidebar',
		'before_widget' => '<div id="%1$s" class="widget %2$s clearfix">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title"><span>',
		'after_title' => '</span></h4>',
		)
	);
	
	register_sidebar(array(
		'name' => 'BBPress Sidebar',
		'description'   => 'This is the sidebar for BBPress pages,',
		'id' => 'st_bbpress_sidebar',
		'before_widget' => '<div id="%1$s" class="widget %2$s clearfix">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title"><span>',
		'after_title' => '</span></h4>',
		)
	);

}

