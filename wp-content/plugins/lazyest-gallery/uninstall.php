<?php
/**
 * Uninstalls the lazyest-galery options when an uninstall has been requested 
 * from the WordPress admin
 *
 * @package lazyest-gallery
 * @subpackage uninstall
 * @since 0.15.0
 */

/* check if the uninstaller is called from wordpress plugin admin page */
if( ! defined( 'ABSPATH' ) || ! defined ( 'WP_UNINSTALL_PLUGIN' ) )
	exit();


include_once( 'lazyest-gallery.php' );
/**
 * LazyestGalleryUninstaller
 * 
 * @package Lazyest Gallery  
 * @author Marcel Brinkkemper
 * @copyright (c) 2011 Brimosoft
 * @version 1.1.0
 * @since 1.1.0
 * @access public
 */
class LazyestGalleryUninstaller extends LazyestAdmin {
	
	/**
	 * LazyestGalleryUninstaller::__construct()
	 * 
	 * @return void
	 */
	function __construct() {
		LazyestAdmin::__construct();
	}	
	
	/**
	 * LazyestGalleryUninstaller::uninstall()
	 * The uninstall procedure
	 * wp_die s with an error message if one of the uninstall functions fails
	 * 
	 * @since 1.1.0
	 * @uses wp_die()
	 * @return void
	 */
	function uninstall() {
		$error_message = '';
		$error_message .= $this->uninstall_cache();
		$error_message .= $this->uninstall_captions();
		$error_message .= $this->uninstall_comment_meta();
		$error_message .= $this->uninstall_lazyest_table();	
		$this->uninstall_roles();	
		if ( '' != $error_message ) {
			$error_message = __( 'Lazyest Gallery could not uninstall. One or more items could not be removed.', 'lazyest-gallery' ) . '<br />' . $error_message;
			wp_die( $error_message );
		}
		// all data has been removed, delete the options
		delete_option( 'lazyest-gallery' );
		delete_option( 'lazyest-fields' );
		delete_option( 'widget_lg_list_folders' );
		delete_option( 'widget_lg_random_image' );
		delete_option( 'widget_lg_slide_show' );
	}
	
	/**
	 * LazyestGalleryUninstaller::uninstall_cache()
	 * Remove thumbs and slides directories
	 * 
	 * @since 1.1.0
	 * @return string '' if successful
	 */
	function uninstall_cache() {
		return ( $this->clear_cache() ) ? '' : __( ' Could not clear your thumbnails and/or slides cache', 'lazyest-gallery' ) . '<br />';
	}
	
	/**
	 * LazyestGalleryUninstaller::uninstall_captions()
	 * Remove all captions.xml
	 * 
	 * @since 1.1.0
	 * @return string '' if successful
	 */
	function uninstall_captions() {
		$success = true;
		$folderlist = $this->folders();
		if ( 0 != count( $folderlist ) ) { 
	 		foreach ( $folderlist as $folder ) {
	 			$captions = $this->root . $folder->curdir . 'captions.xml';
      	if ( file_exists( $captions ) ) {
        	if ( @unlink( $captions ) == false )
          	$success =  false;
      	}
 	  	}
  	}  	
  	return $success ? '' : __( 'Could not remove folder and image data in captions.xml', 'lazyest-gallery' ) . '<br />'; 
	}
	
	/**
	 * LazyestGalleryUninstaller::uninstall_comment_meta()
	 * Remove comment meta for gallery comments
	 * 
	 * @since 1.1.0
	 * @return string '' if successful
	 */
	function uninstall_comment_meta() {
		global $wpdb;
		$success = true;
		$query = "SELECT COUNT(*) AS cnt FROM $wpdb->commentmeta WHERE meta_key = 'lazyest'";
		$results = $wpdb->get_results( $query );
    $cnt = ( ! empty( $results ) ) ? ( $results[0]->cnt ) : 0;
		if ( 0 != $cnt ) {	      
	 		$query = "DELETE FROM $wpdb->commentmeta WHERE meta_key = 'lazyest'";
	    $success = $wpdb->query( $query, ARRAY_A );
    }
    return ( false !== $success ) ? '' : sprintf( __( 'Could not remove comment meta from %s', 'lazyest-gallery' ), $wpdb->commentmeta ) . '<br />';
	}
	
	/**
	 * LazyestGalleryUninstaller::uninstall_lazyest_table()
	 * Remove the lazyest id -> file table
	 * 
	 * @since 1.1.0
	 * @return string '' if successful
	 */
	function uninstall_lazyest_table() {
		global $wpdb;
		$success = true;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$this->table'") == $this->table ) {
			$success = $wpdb->query( "DROP TABLE $this->table" ); 
		}	
		return ( false !== $success ) ? '' : sprintf( __( 'Could not remove table %s from the WordPress database', 'lazyest-gallery' ), $this->table ) . '<br />';
	}
	
	function uninstall_roles() {
    require_once( ABSPATH . 'wp-includes/pluggable.php' );
    $blogusers = lg_get_users_of_blog();    
    foreach( $blogusers as $user ) { // check if users have one or more roles and add role lazyest_editor
      if ( $user->has_cap( 'lazyest_manager' ) )
				$user->remove_role( 'lazyest_manager' );
			if ( $user->has_cap( 'lazyest_editor' ) )
				$user->remove_role( 'lazyest_editor' );
			if ( $user->has_cap( 'lazyest_author' ) )
				$user->remove_role( 'lazyest_author' );		 
    }
    remove_role( 'lazyest_manager' );
		remove_role( 'lazyest_editor' );
		remove_role( 'lazyest_author' );	
  }
	
} // LazyestGalleryUninstaller
	
$lg_uninstaller = new LazyestGalleryUninstaller();
$lg_uninstaller->uninstall();
unset( $lg_uninstaller );

/* Done, Sorry you had to uninstall Lazyest Gallery */
?>