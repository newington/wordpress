<?php
/**
 * Enqueues styles for front-end.
 */
function st_theme_styles() {

	/*
	 * Loads our main stylesheet.
	 */
	wp_enqueue_style( 'theme-style', get_stylesheet_uri() );
	
	/*
	* Load responsive stylesheet
	*/
	wp_enqueue_style( 'theme-style-response', get_template_directory_uri() . '/style-responsive.css', array('theme-style') );
	

	/*
	 * Loads our Google Font.
	 */
	$subsets = 'latin,latin-ext';

		/* translators: To add an additional Open Sans character subset specific to your language, translate
		   this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language. */
		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'twentytwelve' );

		if ( 'cyrillic' == $subset )
			$subsets .= ',cyrillic,cyrillic-ext';
		elseif ( 'greek' == $subset )
			$subsets .= ',greek,greek-ext';
		elseif ( 'vietnamese' == $subset )
			$subsets .= ',vietnamese';

		$protocol = is_ssl() ? 'https' : 'http';
		$query_args = array(
			'family' => 'Open+Sans:400,600,700',
			'subset' => $subsets,
		);
	wp_enqueue_style( 'theme-font', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null );
	
	/*
	* Add font awesome CSS
	*/
	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome.min.css', array('theme-style') );
	
	
	/*
	* Adds stylesheet for shortcodes
	* (will be moved to plugin soon)
	*/
	wp_enqueue_style( 'shortcodes', get_template_directory_uri() . '/framework/shortcodes/shortcodes.css' );
	
	
	
}
add_action( 'wp_enqueue_scripts', 'st_theme_styles' );