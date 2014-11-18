<?php
/**
 * Enqueues scripts for front-end.
 */
 
function st_enqueue_scripts() {
		
	/*
	* Load our main theme JavaScript file
	*/
	wp_enqueue_script('st_theme_custom', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), false, true);		

	/*
	* Adds JavaScript for flexslider
	*/
	wp_enqueue_script('flexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array( 'jquery' ), false, true);
		
	/*
	* Adds JavaScript for shortcodes
	* (will be mvoed to plugin soon)
	*/
	wp_enqueue_script('shortcodes', get_template_directory_uri() . '/framework/shortcodes/shortcodes.js', array( 'jquery' ), false, true);
	
	/*
	* Adds JavaScript to pages with the comment form to support
	* sites with threaded comments (when in use).
	*/
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
	}	


}
add_action('wp_enqueue_scripts', 'st_enqueue_scripts');


/*
* add ie conditional html5 shim to header
*/
function add_ie_html5_shim () {
    echo '<!--[if lt IE 9]>';
    echo '<script src="'. get_template_directory_uri() .'/js/html5.js"></script>';
    echo '<![endif]-->';
}
add_action('wp_head', 'add_ie_html5_shim');	

/*
* add ie 6-8 conditional selectivizr to header
*/
function add_ie_selectivizr () {
    echo '<!--[if (gte IE 6)&(lte IE 8)]>';
    echo '<script src="'. get_template_directory_uri() .'/js/selectivizr-min.js"></script>';
    echo '<![endif]-->';
}
add_action('wp_head', 'add_ie_selectivizr');	

