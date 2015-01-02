<?php

// Set Content Width
if ( ! isset( $content_width ) )
	$content_width = 480;

/*==================================== THEME SETUP ====================================*/

// Load default style.css and Javascripts
add_action('wp_enqueue_scripts', 'themezee_enqueue_scripts');

if ( ! function_exists( 'themezee_enqueue_scripts' ) ):
function themezee_enqueue_scripts() { 
	
	// Register and Enqueue Stylesheet
	wp_register_style('zeeBusiness_stylesheet', get_stylesheet_uri());
	wp_enqueue_style('zeeBusiness_stylesheet');
	
	// Enqueue jQuery Framework
	wp_enqueue_script('jquery');
	
	// Register and enqueue the Malsup Cycle Plugin
	wp_register_script('zee_jquery-cycle', get_template_directory_uri() .'/includes/js/jquery.cycle.all.min.js', array('jquery'));
	wp_enqueue_script('zee_jquery-cycle');
}
endif;


// Load comment-reply.js if comment form is loaded and threaded comments activated
add_action( 'comment_form_before', 'themezee_enqueue_comment_reply' );
	
function themezee_enqueue_comment_reply() {
	if( get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}


// Setup Function: Registers support for various WordPress features
add_action( 'after_setup_theme', 'themezee_setup' );

if ( ! function_exists( 'themezee_setup' ) ):
function themezee_setup() { 
	
	// init Localization
	load_theme_textdomain('themezee_lang', get_template_directory() . '/includes/lang' );
	
	// Add Theme Support
	add_theme_support('post-thumbnails');
	add_theme_support('automatic-feed-links');
	add_editor_style();
	
	// Add Custom Background
	add_theme_support('custom-background', array('default-color' => 'eee'));

	// Add Custom Header
	add_theme_support('custom-header', array(
		'default-image' => get_template_directory_uri() . '/images/default_header.jpg',
		'header-text' => false,
		'width'	=> 900,
		'height' => 140,
		'flex-height' => true));
		
	// Register Navigation Menus
	register_nav_menu( 'top_navi', __('Top Navigation', 'themezee_lang') );
	register_nav_menu( 'main_navi', __('Main Navigation', 'themezee_lang') );
	register_nav_menu( 'foot_navi', __('Footer Navigation', 'themezee_lang') );
}
endif;


// Register Sidebars
add_action( 'widgets_init', 'themezee_register_sidebars' );

if ( ! function_exists( 'themezee_register_sidebars' ) ):
function themezee_register_sidebars() { 
	
	// Register Sidebars
	register_sidebar(array('name' => __('Sidebar Blog', 'themezee_lang'), 'id' => 'sidebar-blog'));
	register_sidebar(array('name' => __('Sidebar Pages', 'themezee_lang'), 'id' => 'sidebar-pages'));
	
	// Register Footer Bars
	register_sidebar(array('name' => __('Footer Left', 'themezee_lang'), 'id' => 'sidebar-footer-left'));
	register_sidebar(array('name' => __('Footer Center', 'themezee_lang'), 'id' => 'sidebar-footer-center'));
	register_sidebar(array('name' => __('Footer Right', 'themezee_lang'), 'id' => 'sidebar-footer-right'));
}
endif;


/*==================================== INCLUDE FILES ====================================*/

// Includes all files needed for theme options, custom JS/CSS and Widgets
add_action( 'after_setup_theme', 'themezee_include_files' );

if ( ! function_exists( 'themezee_include_files' ) ):
function themezee_include_files() { 

	// include Admin Files
	locate_template('/includes/admin/theme-functions.php', true);
	locate_template('/includes/admin/theme-admin.php', true);

	// include custom Javascript and custom CSS Handler files
	locate_template('/includes/js/jscript.php', true);
	locate_template('/includes/css/csshandler.php', true);
	
	// include Theme Hooks
	locate_template('/includes/theme-hooks.php', true);
	
	// include Widget Files
	locate_template('/includes/widgets/theme-widget-ads.php', true);
	locate_template('/includes/widgets/theme-widget-socialmedia.php', true);
}
endif;


/*==================================== THEME FUNCTIONS ====================================*/

// Creates a better title element text for output in the head section
add_filter( 'wp_title', 'themezee_wp_title', 10, 2 );

function themezee_wp_title( $title, $sep = '' ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'themezee' ), max( $paged, $page ) );

	return $title;
}


// Add Default Menu Fallback Function
function themezee_default_menu() {
	echo '<ul id="nav" class="menu">'. wp_list_pages('title_li=&echo=0') .'</ul>';
}


// Display Credit Link Function
function themezee_credit_link() { ?>
	<a href="http://themezee.com/"><?php _e('Wordpress Theme by ThemeZee', 'themezee_lang'); ?></a>
<?php
}


// Change Excerpt Length
add_filter('excerpt_length', 'themezee_excerpt_length');
function themezee_excerpt_length($length) {
    return 45;
}


// Change Excerpt More
add_filter('excerpt_more', 'themezee_excerpt_more');
function themezee_excerpt_more($more) {
    return '';
}


// Add Postmeta Data
if ( ! function_exists( 'themezee_display_postmeta' ) ):
function themezee_display_postmeta() { ?>
	<a href="<?php the_permalink() ?>"><?php the_time(get_option('date_format')); ?></a> / 
<?php
	the_author_posts_link(); echo ' / ';
	comments_popup_link( __('No comments', 'themezee_lang'),__('One comment','themezee_lang'),__('% comments','themezee_lang') );
	edit_post_link(__( 'Edit', 'themezee_lang' ), ' / ');
}
endif;


// Add Postinfo Data
if ( ! function_exists( 'themezee_display_postinfo' ) ):
function themezee_display_postinfo() {
	_e('Category: ', 'themezee_lang'); the_category(', '); echo ' / ';
	if (get_the_tags()) the_tags(__('Tags: ', 'themezee_lang'), ', ');
}
endif;

?>