<?php 

if ( !function_exists( 'st_buddypress_enqueue_scripts' ) ) :
/**
 * Enqueue theme javascript safely
 *
 * @see http://codex.wordpress.org/Function_Reference/wp_enqueue_script
 * @since BuddyPress (1.5)
 */
function st_buddypress_enqueue_scripts() {

	// Enqueue various scripts
	wp_enqueue_script( 'bp-jquery-query' );
	wp_enqueue_script( 'bp-jquery-cookie' );

	// Enqueue scrollTo only on activity pages
	if ( bp_is_activity_component() ) {
		wp_enqueue_script( 'bp-jquery-scroll-to' );
	}

	// A similar check is done in BP_Core_Members_Widget, but due to a load order
	// issue, we do it again here
	if ( is_active_widget( false, false, 'bp_core_members_widget' ) && ! is_admin() && ! is_network_admin() ) {
		wp_enqueue_script( 'bp-widget-members' );
	}

	// Enqueue the global JS - Ajax will not work without it
	wp_enqueue_script( 'dtheme-ajax-js', get_template_directory_uri() . '/_inc/global.js', array( 'jquery' ), bp_get_version() );

	// Add words that we need to use in JS to the end of the page so they can be translated and still used.
	$params = array(
		'my_favs'           => __( 'My Favorites', 'buddypress' ),
		'accepted'          => __( 'Accepted', 'buddypress' ),
		'rejected'          => __( 'Rejected', 'buddypress' ),
		'show_all_comments' => __( 'Show all comments for this thread', 'buddypress' ),
		'show_all'          => __( 'Show all', 'buddypress' ),
		'comments'          => __( 'comments', 'buddypress' ),
		'close'             => __( 'Close', 'buddypress' ),
		'view'              => __( 'View', 'buddypress' ),
		'mark_as_fav'	    => __( 'Favorite', 'buddypress' ),
		'remove_fav'	    => __( 'Remove Favorite', 'buddypress' ),
		'unsaved_changes'   => __( 'Your profile has unsaved changes. If you leave the page, the changes will be lost.', 'buddypress' ),
	);
	wp_localize_script( 'buddypress-ajax-js', 'BP_DTheme', $params );

	// Maybe enqueue comment reply JS
	if ( is_singular() && bp_is_blog_page() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'st_buddypress_enqueue_scripts' );
endif;
	
	
	
if ( !function_exists( 'st_buddypress_enqueue_styles' ) ) :
/**
 * Enqueue theme CSS safely
 *
 * For maximum flexibility, BuddyPress Default's stylesheet is enqueued, using wp_enqueue_style().
 * If you're building a child theme of bp-default, your stylesheet will also be enqueued,
 * automatically, as dependent on bp-default's CSS. For this reason, bp-default child themes are
 * not recommended to include bp-default's stylesheet using @import.
 *
 * If you would prefer to use @import, or would like to change the way in which stylesheets are
 * enqueued, you can override st_buddypress_enqueue_styles() in your theme's functions.php file.
 *
 * @see http://codex.wordpress.org/Function_Reference/wp_enqueue_style
 * @see http://codex.buddypress.org/releases/1-5-developer-and-designer-information/
 * @since BuddyPress (1.5)
 */
function st_buddypress_enqueue_styles() {

	// Register our main stylesheet
	wp_register_style( 'bp-default-main', get_template_directory_uri() . '/buddypress/style-buddypress.css', array(), bp_get_version() );

	// If the current theme is a child of bp-default, enqueue its stylesheet
	if ( is_child_theme() && 'bp-default' == get_template() ) {
		wp_enqueue_style( get_stylesheet(), get_stylesheet_uri(), array( 'bp-default-main' ), bp_get_version() );
	}

	// Enqueue the main stylesheet
	wp_enqueue_style( 'bp-default-main' );
	

}
add_action( 'wp_enqueue_scripts', 'st_buddypress_enqueue_styles' );
endif;
	
	
if ( !function_exists( 'st_buddypress_activity_secondary_avatars' ) ) :
/**
 * Add secondary avatar image to this activity stream's record, if supported.
 *
 * @param string $action The text of this activity
 * @param BP_Activity_Activity $activity Activity object
 * @package BuddyPress Theme
 * @return string
 * @since BuddyPress (1.2.6)
 */
function st_buddypress_activity_secondary_avatars( $action, $activity ) {
	switch ( $activity->component ) {
		case 'groups' :
		case 'friends' :
			// Only insert avatar if one exists
			if ( $secondary_avatar = bp_get_activity_secondary_avatar() ) {
				$reverse_content = strrev( $action );
				$position        = strpos( $reverse_content, 'a<' );
				$action          = substr_replace( $action, $secondary_avatar, -$position - 2, 0 );
			}
			break;
	}

	return $action;
}
add_filter( 'bp_get_activity_action_pre_meta', 'st_buddypress_activity_secondary_avatars', 10, 2 );
endif;




if ( !function_exists( 'st_buddypress_sidebar_login_redirect_to' ) ) :
/**
 * Adds a hidden "redirect_to" input field to the sidebar login form.
 *
 * @since BuddyPress (1.5)
 */
function st_buddypress_sidebar_login_redirect_to() {
	$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
	$redirect_to = apply_filters( 'bp_no_access_redirect', $redirect_to ); ?>

	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

<?php
}
add_action( 'bp_sidebar_login_form', 'st_buddypress_sidebar_login_redirect_to' );
endif;



function remove_calendar_widget() {
	unregister_widget('WP_Widget_Calendar');
}
//add_action( 'widgets_init', 'remove_calendar_widget' );

/**
 * Ensure that multiselect boxes have trailing brackets in their 'id' and 'name' attributes.
 *
 * These brackets are required for an array of values to be sent in the POST
 * request. Previously, bp_get_the_profile_field_input_name() contained the
 * necessary logic, but since BP 2.0 that logic has been moved into
 * BP_XProfile_Field_Type_Multiselectbox. Since bp-default does not use the
 * BP_XProfile_Field_Type classes to build its markup, it did not inherit
 * the brackets from their new location. Thus this workaround.
 */
function bp_dtheme_add_brackets_to_multiselectbox_attributes( $name ) {
	global $field;

	if ( 'multiselectbox' === $field->type ) {
		$name .= '[]';
	}

	return $name;
}
add_filter( 'bp_get_the_profile_field_input_name', 'bp_dtheme_add_brackets_to_multiselectbox_attributes' );