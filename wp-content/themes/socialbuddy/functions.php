<?php
/**
* Social Buddy Functions and definitions
* by Swish Themes (http://swishthemes.com)
*/

/**
 * To allow us to query if a plugin is active
 * http://codex.wordpress.org/Function_Reference/is_plugin_active
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
* Set the content width based on the theme's design and stylesheet.
*/
if ( ! isset( $content_width ) ) $content_width = 685;


if ( ! function_exists( 'st_theme_setup' ) ):
/**
* Sets up theme defaults and registers support for various WordPress features.
*/
function st_theme_setup() {

	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 */
	load_theme_textdomain( 'framework', get_template_directory() . '/languages' );
	
	
	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );
	
	/**
	 * Enable support for Post Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 60, 60, true );
	
	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
			'primary-nav' => __( 'Primary Navigation', 'framework' ),
			'footer-nav' => __( 'Footer Navigation', 'framework' )
	));

	
	if (is_plugin_active('buddypress/bp-loader.php')) {
		
	/**
	 * This theme comes with all the BuddyPress goodies
	 */
	add_theme_support( 'buddypress' );
	
		
	// Load the BuddyPress AJAX functions for the theme
	require( get_template_directory() . '/buddypress/ajax.php' );
	
	if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		// Register buttons for the relevant component templates
		// Friends button
		if ( bp_is_active( 'friends' ) )
			add_action( 'bp_member_header_actions',    'bp_add_friend_button',           5 );

		// Activity button
		if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() )
			add_action( 'bp_member_header_actions',    'bp_send_public_message_button',  20 );

		// Messages button
		if ( bp_is_active( 'messages' ) )
			add_action( 'bp_member_header_actions',    'bp_send_private_message_button', 20 );

		// Group buttons
		if ( bp_is_active( 'groups' ) ) {
			add_action( 'bp_group_header_actions',     'bp_group_join_button',           5 );
			add_action( 'bp_group_header_actions',     'bp_group_new_topic_button',      20 );
			add_action( 'bp_directory_groups_actions', 'bp_group_join_button' );
		}

		// Blog button
		if ( bp_is_active( 'blogs' ) )
			add_action( 'bp_directory_blogs_actions',  'bp_blogs_visit_blog_button' );
	}
	
	} // End if BuddyPress active 
	
	/**
	* If BBPress is active, add theme support
	*/
	if ( class_exists( 'bbPress' ) ) {
		add_theme_support( 'bbpress' );
	}
	
}
endif; // st_theme_setup
add_action( 'after_setup_theme', 'st_theme_setup' );

/**
* Custom Theme Options
*/
if ( !function_exists( 'optionsframework_init' ) ) {
	define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/framework/admin/' );
	require_once dirname( __FILE__ ) . '/framework/admin/options-framework.php';
}


/**
 * Thumbnail Sizes
 */

if ( function_exists( 'add_theme_support' ) ) {
	add_image_size( 'post', 685, 300, true ); // Post thumbnail
	add_image_size( 'post-thumb', 310, 310, true ); // Post Square thumbnail
	add_image_size( 'slider', 1000, 500, true ); // Post Square thumbnail
}


/**
 * Enqueues scripts and styles for front-end.
 */
require("framework/scripts.php");
require("framework/styles.php");

/**
 * Register widgetized area and update sidebar with default widgets
 */

require("framework/register-sidebars.php");

add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode');

// Add the Blog Custom Widget
require("framework/widgets/widget-blog.php");

// Add the Flickr Photos Custom Widget
require("framework/widgets/widget-flickr.php");

/**
 * Add metabox library
 */
function st_initialize_cmb_meta_boxes() {
	if ( !class_exists( 'cmb_Meta_Box' ) ) {
		require_once( 'framework/post-meta/library/init.php' );
	}
}

add_action( 'init', 'st_initialize_cmb_meta_boxes', 9999 );

/**
 * Initialize the metabox class.
 */
function cmb_initialize_cmb_meta_boxes() {

	if ( ! class_exists( 'cmb_Meta_Box' ) )
		require_once 'init.php';

}

// Add Meta Box Componenets
require("framework/post-meta/page-meta.php");


/**
* Add BuddyPress functions
*/
if (is_plugin_active('buddypress/bp-loader.php')) {
require("buddypress/functions-buddypress.php");
}

/**
* If BBPress is active, load functions
*/
if ( class_exists( 'bbPress' ) ) {
require_once (get_template_directory() . '/bbpress/bbpress-functions.php');
}

/**
* Add hpslider 
*/
require("framework/components/hpslider/hpslider.php");


/**
* Add hpblocks
*/
require("framework/components/hpblocks/hpblocks.php");


/**
 * Theme Functions
 */

require("framework/theme-functions.php");


/**
 * Adds theme shortcodes
 * (will be mvoed to plugin soon)
 */

require("framework/shortcodes/shortcodes.php");
// Add shortcode manager
require("framework/wysiwyg/wysiwyg.php");


/**
 * Comment Functions
 */
 
require("framework/comment-functions.php");


/**
 * Add class if post has thumbnail
 */

function st_thumb_class($classes) {
	global $post;
	if( has_post_thumbnail($post->ID) ) { $classes[] = 'has_thumb'; }

		return $classes;
}
add_filter('post_class', 'st_thumb_class');

/**
 * Pagination function
 */
 
function st_pagination($pages = '', $range = 2)
{  
     $showitems = ($range * 2)+1;  

     global $paged;
     if(empty($paged)) $paged = 1;

     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages)
         {
             $pages = 1;
         }
     }   

     if(1 != $pages)
     {
         echo "<div class='blog-pagination'>";

         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
             }
         }

         echo "</div>\n";
     }
}


/**
 * Display navigation to next/previous pages when applicable
 */
 
if ( ! function_exists( 'st_content_nav' ) ):
function st_content_nav( $nav_id ) {
	global $wp_query;

	$nav_class = 'site-navigation paging-navigation';
	if ( is_single() )
	$nav_class = 'site-navigation post-navigation';

	?>
    <?php if ($wp_query->max_num_pages > 1) { ?>
	<nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?> clearfix">

	<?php if ( is_single() ) : // navigation links for single posts ?>

		<?php previous_post_link( '<div class="nav-previous">%link</div>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'framework' ) . '</span> %title' ); ?>
		<?php next_post_link( '<div class="nav-next">%link</div>', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'framework' ) . '</span>' ); ?>

	<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

		<?php if ( get_previous_posts_link() ) : ?>
		<div class="nav-next"><?php previous_posts_link( __( 'Previous', 'framework' ) ); ?></div>
		<?php endif; ?>
        
        <?php st_pagination(); ?>
        
        <?php if ( get_next_posts_link() ) : ?>
		<div class="nav-previous"><?php next_posts_link( __( 'Next', 'framework' ) ); ?></div>
		<?php endif; ?>


	<?php endif; ?>

	</nav><!-- #<?php echo $nav_id; ?> -->
	<?php }
}
endif;

