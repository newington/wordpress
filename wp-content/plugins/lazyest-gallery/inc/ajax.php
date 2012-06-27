<?php
/** 
 * This file contains all ajax actions
 * 
 * @package Lazyest Gallery
 * @subpackage Ajax
 * @author Marcel Brinkkemper
 * @copyright 2010-2012 Brimosoft
 * @access public
 * @since 1.1.0
 * 
 */
 
/**
 * bind ajax calls to functions 
 */

add_action( 'wp_ajax_nopriv_lg_swfupload', 'lg_swfupload' );  
add_action( 'wp_ajax_lg_swfupload', 'lg_swfupload' ); 
add_action( 'wp_ajax_lg_admin_list_folders', 'lg_admin_list_folders' );
add_action( 'wp_ajax_lg_admin_contextmenu', 'lg_admin_contextmenu' );
add_action( 'wp_ajax_lg_folder_subcount', 'lg_folder_subcount' );
add_action( 'wp_ajax_nopriv_lg_folder_subcount', 'lg_folder_subcount' );
add_action( 'wp_ajax_lg_insert_folder_shortcode', 'lg_insert_folder_shortcode' );
add_action( 'wp_ajax_lg_upload_showfolder', 'lg_upload_showfolder' );
add_action( 'wp_ajax_lg_insert_image_shortcode', 'lg_insert_image_shortcode' );
add_action( 'wp_ajax_lg_folder_newfolder', 'lg_folder_newfolder' );
add_action( 'wp_ajax_lg_clear_thumbs', 'lg_clear_thumbs' );
add_action( 'wp_ajax_lg_add_user', 'lg_add_user' );
add_action( 'wp_ajax_lg_remove_user', 'lg_remove_user' );
add_action( 'wp_ajax_lg_set_viewer_level', 'lg_set_viewer_level' );
add_action( 'wp_ajax_lg_add_fauthor', 'lg_add_fauthor' );
add_action( 'wp_ajax_lg_remove_fauthor', 'lg_remove_fauthor' );
add_action( 'wp_ajax_lg_rebuild_cache', 'lg_rebuild_cache' );
add_action( 'wp_ajax_lg_next_dirs', 'lg_next_dirs' );
add_action( 'wp_ajax_nopriv_lg_next_dirs', 'lg_next_dirs' );
add_action( 'wp_ajax_lg_next_thumbs', 'lg_next_thumbs' );
add_action( 'wp_ajax_nopriv_lg_next_thumbs', 'lg_next_thumbs' );
add_action( 'wp_ajax_lg_truncate_table', 'lg_truncate_table' );
add_action( 'wp_ajax_lg_rebuild_database', 'lg_rebuild_database' );
add_action( 'wp_ajax_lg_image_request', 'lg_image_request' );
add_action( 'wp_ajax_nopriv_lg_image_request', 'lg_image_request' );
add_action( 'wp_ajax_lg_refresh_folder', 'lg_refresh_folder' );
add_action( 'wp_ajax_lg_media', 'lg_media' );

/**
 * lg_admin_list_folders()
 * Displays an unordered list of folders and subfolders
 * 
 * @since 1.1.0
 * @return void
 */
function lg_admin_list_folders() {
  global $lg_gallery;
  if ( isset( $_POST['folder'] ) ) {
    $folder = new LazyestFolder( urldecode( $_POST['folder'] ) );
    if ( $folder->valid() ) {
      $folder->list_folders( 'hidden', 'dirname', 'admin' );
      die();
    }
  }
  echo ' ';
  die();
}

/**
 * lg_admin_contextmenu()
 * Displays a context menu for folder to move or copy an image into
 * 
 * @since 1.1.0
 * @return void
 */
function lg_admin_contextmenu() {
  global $lg_gallery;
  $result =' ';
  if ( isset( $_POST['folder'] ) ) {
    $manage = new LazyestFolder( urldecode( $_POST['folder'] ) );    
    $folders = $lg_gallery->folders( 'subfolders', 'hidden' );
    $count = 0;
    if ( 0 < count( $folders ) ) {
      foreach ( $folders as $folder ) {
        if ( $folder->curdir != $manage->curdir ) {
          $result .= sprintf('<li class="folderpng"><a href="#%s">%s</a></li>',
            urlencode( $folder->curdir ), 
            htmlentities( $folder->curdir ) );
          $count++;  
        } 
      }   
    }
  }
  echo ( 0 < $count ) ? $result : 'none';
  die();
}

/**
 * lg_folder_subcount()
 * Display the number of images in subfolders
 * 
 * @since 1.1.0
 * @return void
 */
function lg_folder_subcount() {
  global $lg_gallery;
  $result = ' ';  
  if ( isset( $_POST['folder'] ) ) {
    $subcount = $allcount = 0;
    if ( '' != $_POST['folder'] ) { // get # of images in subfolders of folder
      $folder = new LazyestFolder( urldecode( $_POST['folder'] ) );
      $count = (int)$folder->count();
      $allcount = (int)$folder->count( 'subfolders' );
      $subcount = $allcount - $count;
      $allcount = ( 'separate' == $lg_gallery->get_option( 'count_subfolders' ) || 'none' == $lg_gallery->get_option( 'count_subfolders' )  ) ? $count : $allcount;
    } else { // get # of images in gallery
      $folders = $lg_gallery->folders( 'root', 'hidden' );
      for ( $i = 0; $i != count( $folders ); $i++ ) {
  		  $folder = $folders[$i];
  			$subcount += $folder->count( 'subfolders' );
  		} 
    }     
    if ( ! isset( $_POST['allcount'] ) ) {          
      if ( 0 < $subcount ) {
        $result .= sprintf( esc_html__( '%s in folders', 'lazyest-gallery' ), strval( $subcount ) );
      } else {
        if ( '' == $_POST['folder'] )
          $result .= sprintf( esc_html__( '%s in folders', 'lazyest-gallery' ), strval( $subcount ) ); 
      }        
    } else {
      $result .= sprintf( '%s %s', $allcount, $lg_gallery->get_option( 'listed_as' ) );
    }
  }
  echo $result;
  die();
}

/**
 * lg_insert_folder_shortcode()
 * Insert a folder shortcode in a post
 * 
 * @since 1.1.0
 * @return void
 */
function lg_insert_folder_shortcode() {
  global $lg_gallery;
  if ( isset( $_POST['folder'] ) ) {
    $file = urldecode( $_POST['folder'] );    
    $folder = new LazyestFolder( $file ); 
    if ( $folder->valid() ) { 
      require_once ( $lg_gallery->plugin_dir . '/inc/uploadtab.php' );
      $uploadtab = new LazyestUploadTab();
      $uploadtab->insert_folder_shortcode( $folder );
    }  
  }
  die();
}

/**
 * lg_upload_showfolder()
 * Show folder contents in upload tabs
 * 
 * @since 1.1.0
 * @return void
 */
function lg_upload_showfolder() {
  global $lg_gallery;
  if ( isset( $_POST['folder'] ) ) {        
    $current_url = $_POST['current_url'];
    $query = substr( $current_url, strpos( $current_url, '?' ) + 1 ); 
    wp_parse_str( $query, $qs );
    $_REQUEST['post_id'] = isset( $qs['post_id'] ) ? $qs['post_id'] : 0; 
    $file = urldecode( $_POST['folder'] );    
    $folder = new LazyestFolder( $file ); 
    if ( $folder->valid() ) { 
      require_once ( $lg_gallery->plugin_dir . '/inc/uploadtab.php' );
      $uploadtab = new LazyestUploadTab();
      $uploadtab->show_folder( $folder, $current_url );
    }  
  }
  die();
}

/**
 * lg_insert_image_shortcode()
 * Insert an image shortcode in a post
 * 
 * @since 1.1.0
 * @return void
 */
function lg_insert_image_shortcode() {
  global $lg_gallery;
  if ( isset( $_POST['image'] ) ) {
    $file = urldecode( $_POST['image'] );    
    $folder = new LazyestFolder( dirname( $file ) );     
    if ( $folder->valid() ) { 
      $image = $folder->single_image( basename( $file ), 'thumbs' );    
      require_once ( $lg_gallery->plugin_dir . '/inc/uploadtab.php' );
      $uploadtab = new LazyestUploadTab();
      $uploadtab->insert_image_shortcode( $image );
    }  
  }
  die();
}

/**
 * lg_folder_newfolder()
 * Insert a new folder in the gallery
 * 
 * @since 1.1.0
 * @return void
 */
function lg_folder_newfolder() {
  global $lg_gallery;
  
  $nonce = $_POST['_wpnonce'];
  $from_gallery = wp_verify_nonce( $nonce, 'lg_manage_gallery' );
  $from_folder =  wp_verify_nonce( $nonce, 'lg_manage_folder' );
  if ( ! ( $from_gallery || $from_folder ) )
  	die();  		
  if ( isset( $_POST['create_new_folder'] ) ) {    
    if ( isset( $_POST['folder'] ) ) {
      $id = $_POST['folder'];
      $newname = $_POST['new_folder_name'];
      if ( '0' != $id ) { 
      	$file = trailingslashit( utf8_decode( stripslashes( rawurldecode( $_POST['directory'] ) ) ) );
        $_POST['folder'] = $_POST['directory'];
        include_once( $lg_gallery->plugin_dir . '/inc/manager.php' );
        $parentfolder = new LazyestAdminFolder( $file );
        if ( $parentfolder->valid() ) {
          $parentfolder->open();
          $foldername = $parentfolder->curdir . $newname;    
        }
      } else {
        unset( $_POST['folder'] );
        $foldername = $newname;
      } 
    }  
    $message = sprintf( 'Folder %s cannot be opened.', htmlentities( $newname ) );
    trailingslashit( $newname );               
    $result = $lg_gallery->new_gallery_folder( $foldername );
    
    if ( true === $result ) {
      $i = 0;
      $found = false;
      $folders = ( '0' != $id ) ? $parentfolder->subfolders() : $lg_gallery->folders( 'root', 'hidden' );  
      while ( ! $found || $i > count( $folders ) ) { // find pagination information
        $folder = $folders[$i];
        $found = $newname; $folder->dirname();
        $i++; 
      }      
      if ( $found ) {
        $page = ceil( 20 / $i );
        $_POST['lg_paged'] = $page; // set pagination request
        $action = ( '0' != $id ) ? $parentfolder->foldersbox() : $lg_gallery->foldersbox( $folders );
      } else {
        $lg_gallery->message = $message;
        $lg_gallery->success = false;
      	$lg_gallery->options_message();
      }  
    } else {      
      $lg_gallery->message = $result;
      $lg_gallery->success = false;
      $lg_gallery->options_message();
    }
  }
  die();
}  

/**
 * lg_clear_thumbs()
 * Delete cache
 * 
 * @since 1.1.0
 * @return void
 */
function lg_clear_thumbs() {
  global $lg_gallery;
  if ( isset( $_POST['folder'] ) ) {
    $file = $file = trailingslashit( utf8_decode( stripslashes( rawurldecode( $_POST['directory'] ) ) ) );
    $_POST['folder'] = $_POST['directory'];    
    include_once( $lg_gallery->plugin_dir . '/inc/manager.php' );    
    $folder = new LazyestAdminFolder( $file );    
    if ( $folder->valid() ) {
      // $folder->delete_file();  
    } 
  }
  die();
}

/**
 * lg_add_user()
 * Add a user with role to the gallery
 * 
 * @since 1.1.0
 * @return
 */
function lg_add_user() {
  global $lg_gallery;
  $lg_gallery->add_user( $_POST['id'], $_POST['type'] );
  echo 'true';
  die();
}

/**
 * lg_remove_user()
 * Remove a user with role from the gallery
 * 
 * @since 1.1.0
 * @return void
 */
function lg_remove_user() {
  global $lg_gallery;
  $lg_gallery->remove_user( $_POST['id'], $_POST['type'] );
  echo 'true';
  die();
}

/**
 * lg_set_viewer_level()
 * Remove a user with role from the gallery
 * 
 * @since 1.1.0
 * @return void
 */
function lg_set_viewer_level() {
  global $lg_gallery;
  $lg_gallery->set_viewer_level();
  echo 'true';
  die();
}

/**
 * lg_media_upload()
 * Show lazyest-gallery upload window
 * 
 * @return void
 * @since 1.1.3
 */
function lg_media() {
	global $lg_gallery; 
	
	check_ajax_referer();
	
	$folder = isset( $_REQUEST['folder' ] ) ? utf8_decode( stripslashes( rawurldecode( $_GET['folder'] ) ) ) : '';	
	
	if ( '' == $folder )
		die( __( 'Cannot upload images to no folder', 'lazyest-gallery' ) );
		
	require_once( $lg_gallery->plugin_dir . '/inc/manager.php' );
	$lazyest_admin_folder = new LazyestAdminFolder( $folder );
	$lazyest_admin_folder->open();
		
	if (  'TRUE' == $lg_gallery->get_option( 'flash_upload' ) )  {
		$j = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'dev.js' : 'js';
	 	wp_register_script( 'lg_swfupload-handlers', $lg_gallery->plugin_url . "/js/lazyest-swf-handler.$j", array('jquery'), '1.1', false );
	  wp_enqueue_script('swfupload-all');
	  wp_enqueue_script( 'lg_swfupload-handlers' );
	  wp_localize_script( 'lg_swfupload-handlers', 'lg_swfuploadL10n', $lg_gallery->localize_swf() );
	  $c = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'dev.css' : 'ccs';
	    wp_register_style( 'lg_admin_style', $lg_gallery->plugin_url . "/css/_admin.$c" );
	  $lg_gallery->update_option( 'flash_upload', 'TRUE' );
	}  
	if ( isset($_GET['flash'] ) ) {     
	  if ( '0' == $_GET['flash'] ) { 
	    $lg_gallery->update_option( 'flash_upload', 'FALSE' );
	  }
	}
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
	<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<title><?php bloginfo('name') ?> &rsaquo; <?php esc_html_e('Uploads'); ?> &#8212; <?php esc_html_e('Lazyest Gallery', 'lazyest-gallery'); ?></title>
	<?php
	wp_enqueue_style( 'global' );
	wp_enqueue_style( 'wp-admin' );
	wp_enqueue_style( 'colors' );
	wp_enqueue_style( 'media' );
	wp_enqueue_style( 'ie' );
	wp_enqueue_style( 'lg_admin_style' );
	?>
	<script type="text/javascript">
	//<![CDATA[
	addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
	var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time(); ?>'};
	var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>', pagenow = 'media-upload-popup', adminpage = 'media-upload-popup',
	isRtl = <?php echo (int) is_rtl(); ?>;
	//]]>
	</script>
	<?php
	do_action('admin_print_styles-media-upload-popup');
	do_action('admin_print_styles');
	do_action('admin_print_scripts');
	do_action('admin_head-media-upload-popup');
	do_action('admin_head');
	
	?>
	</head>
	<body id="media-upload" class="no-js">
	<script type="text/javascript">
	//<![CDATA[
	(function(){
	var c = document.body.className;
	c = c.replace(/no-js/, 'js');
	document.body.className = c;
	})();
	
	
	//]]>
	</script>
	<div id="media-upload-header">
		<ul id="sidemenu">
		<li id="tab-type"><a href="#" class="current"><?php esc_html_e( 'From Computer', 'lazyest-gallery' ); ?></a></li>	
		</ul>
	</div>
	<?php if ( isset( $_REQUEST['html-upload'] ) ) $lazyest_admin_folder->uploadfiles(); ?>
	<?php if ( $lazyest_admin_folder->user_can( 'editor' ) ) $lazyest_admin_folder->uploadbox(); ?>
	<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>	
	</body>
	</html>
<?php
	die();
}

/**
 * lg_add_fauthor()
 * Add an autor to a folder
 * 
 * @since 1.1.0
 * @return void
 */
function lg_add_fauthor() {
  global $lg_gallery;  
  if ( isset( $_POST['folder'] ) ) {
    include_once( $lg_gallery->plugin_dir . '/inc/manager.php' );
    $folder =  new LazyestAdminFolder( $_POST['folder'] );
    if ( $folder->valid() ) {
      $folder->open();
      $result = $folder->set_author( $_POST['id'] );
      if ( $result ) {
        $folder->change();
        echo 'true';
        die();
      }
    }    
  }
  echo 'false';
  die();
}

/**
 * lg_remove_fauthor()
 * Remove an autor from a folder
 * 
 * @since 1.1.0
 * @return void
 */
function lg_remove_fauthor() {
  global $lg_gallery;  
  if ( isset( $_POST['folder'] ) ) {
    include_once( $lg_gallery->plugin_dir . '/inc/manager.php' );
    $folder =  new LazyestAdminFolder( $_POST['folder'] );
    if ( $folder->valid() ) {
      $folder->open();
      $result = $folder->unset_author( $_POST['id'] );
      if ( $result ) {
        $folder->change();
        echo 'true';
        die();
      }
    }    
  }
  echo 'false';
  die();
}

/**
 * lg_rebuild_cache()
 * Rebuilds the cache for 1 folder and returns the number of folders to go.
 * 
 * @since 1.1.0
 * @return void
 */
function lg_rebuild_cache() {
  global $lg_gallery;
  if ( isset( $_POST['folder'] ) ) {
    $count = $lg_gallery->rebuild_cache( $_POST['folder'], $_POST['image'] );    
    echo $count['folder'].','.$count['image'];
  }
  die();
}


/**
 * lg_next_dirs()
 * Shows next page of folders
 * 
 * @since 1.1.0
 * @return void
 */
function lg_next_dirs() {
  global $lg_gallery;
	if ( 'TRUE' != $lg_gallery->get_option( 'external_request' ) ) {
		check_ajax_referer( 'show_dirs', 'ajax_nonce' );
	}	
	wp_set_current_user( $_POST['user_id'] );
  $lg_plugin_dir = $lg_gallery->plugin_dir;
	define( 'LG_FRONTEND', true ); 
  $_SERVER['REQUEST_URI'] = $_POST['request_uri'];
  require_once( $lg_plugin_dir . '/inc/frontend.php' );
  $lg_gallery = new LazyestFrontend(); 
	if ( '' != $_POST['virtual'] )
		$lg_gallery->set_root( urldecode( $_POST['virtual'] ) );
  $path = urldecode( $_POST['folder'] );  
  $lg_pagei = isset( $_POST['lg_paged'] ) ? $_POST['lg_paged'] : 1; 
  $folder = ( $path != '') ? $folder = new LazyestFolder( $path ) : null;
  if ( !is_null( $folder ) )
		$folder->open();  
 	$lg_gallery->show_dirs( $folder, (int)$_POST['perpage'], (int)$_POST['columns']  );
  die();
}

/**
 * lg_next_thumbs()
 * Shows next page of image thumbnails
 * 
 * @since 1.1.0
 * @return void
 */
function lg_next_thumbs() {
	global $lg_gallery, $post;
	if ( 'TRUE' != $lg_gallery->get_option( 'external_request' ) ) {
		check_ajax_referer( 'show_thumbs', 'ajax_nonce' );
	}
  $lg_plugin_dir = $lg_gallery->plugin_dir;
	define( 'LG_FRONTEND', true );	
  $_SERVER['REQUEST_URI'] = $_POST['request_uri'];
  require_once( $lg_plugin_dir . '/inc/frontend.php' );
  $lg_gallery = new LazyestFrontend();   
	if ( '' != $_POST['virtual'] )
		$lg_gallery->set_root( urldecode( $_POST['virtual'] ) );	
  $start = 1; 
  $lg_pagei = isset( $_POST['lg_pagei'] ) ? $_POST['lg_pagei'] : 1; 
  if ( isset( $_POST['folder'] ) ){ 
    $path = urldecode( $_POST['folder'] );
    $folder = new LazyestFrontendFolder( $path );
		$folder->load( 'thumbs' );
		$post = get_post( intval( $_POST['post_id'] ) );
    $folder->show_thumbs( (int)$_POST['perpage'], (int)$_POST['columns'], true );   
  } else {
    echo -1;    
  }  
  die();
}

/**
 * lg_truncate_table()
 * empty the wp_lazyestfiles table
 * 
 * @since 1.1.0
 * @return void
 */
function lg_truncate_table() {
  global $lg_gallery;
  echo $lg_gallery->truncate_table() ? '1' : '0';
  die();
}


/**
 * lg_rebuild_database()
 * Rebuilds the table for 1 folder and returns the number of folders to go.
 * @since 1.1.0
 * @return void
 */
function lg_rebuild_database() {
  global $lg_gallery;
  if ( isset( $_POST['folder'] ) ) {
    $count = $lg_gallery->rebuild_database( $_POST['folder'] );
    echo $count;
  } 
  die();
}

/**
 * lg_image_request()
 * Used to asynchronously create images
 * If resizing fails, an error image /images/file_alert.png will be returned
 * Sends header 304 Not Modified if image in browser cache 
 * 
 * @since 1.1.0
 * @return void
 */
function lg_image_request() {	
  global $lg_gallery;
  $this_file = '';
  if ( isset( $_GET['file'] ) ) {   
    $this_file = utf8_decode( stripslashes( rawurldecode( $_GET['file'] ) ) ); 
  }   
    
	$original_file = $lg_gallery-> root . $this_file; 
  if ( ( '' == $this_file ) || ! file_exists( $original_file ) ) {
  	header('HTTP/1.1 404 Not Found');	
		esc_html_e( 'Illegal image request', 'lazyest-gallery' );
		die();	
	}
	
	$path = pathinfo( $this_file ); 
	 
	$thumb = isset( $_REQUEST['thumb'] ) && ( $_REQUEST['thumb'] == 1 );
	$cache = ( ( $thumb && ( 'TRUE' == $lg_gallery->get_option( 'enable_cache' ) ) ) || ( ! $thumb && ( 'TRUE' == $lg_gallery->get_option( 'enable_slides_cache' ) ) ) );
	
	$cache_dir = $thumb ? $lg_gallery->get_option( 'thumb_folder' ) : $lg_gallery->get_option( 'slide_folder' );
	$cached_file = $lg_gallery-> root . trailingslashit( $path['dirname'] ) . $cache_dir . $path['basename'];
	
	// send 304 response if file has not been changed
	if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
		if ( $cache && is_file( $cached_file ) ) { 
			if ( strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) == filemtime( $cached_file ) )  {
	  		@header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $cached_file ) ) . ' GMT', true, 304 );
	  		die();
			}	
		}		
		if ( ! $cache ) {
			if ( strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) == filemtime( $original_file ) ) {
	  		@header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $original_file ) ) . ' GMT', true, 304 );
	  		die();				
			}
		}		
	}
	 
	switch( strtolower( $path[ 'extension' ] ) ) {   
		case 'jpeg':
		case 'jpg':
			header( 'Content-type: image/jpeg' );
			break;
		case 'gif':
			@header( 'Content-type: image/gif' );
			break;
		case 'png':
			@header( 'Content-type: image/png' );
			break;
  }
	
	// pass through file if cached file already exists
	if ( is_file( $cached_file ) ) {
		@header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $cached_file ) ) . ' GMT');
		@readfile( $cached_file );
		die();
	} 
	  
  $folder = new LazyestFolder( $path['dirname'] );
  $image = ( $thumb ) ? new LazyestThumb( $folder ) : new LazyestSlide( $folder );
  $image->image = $path['basename'];
  if( $thumb ) {
		$height = $lg_gallery->get_option( 'thumbheight' );
		$width = $lg_gallery->get_option( 'thumbwidth' );
	}
	else {
		$height = $lg_gallery->get_option( 'pictheight' );
		$width = $lg_gallery->get_option( 'pictwidth' );
	}
  
  if ( $cache ) { 
    $memok = $image->cache();
    @header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $cached_file ) ) . ' GMT');
  } else {		
    $memok = $image->newsize( $width, $height );     
    @header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $original_file ) ) . ' GMT');
  } 
  if ( ! $memok ) {    
    $resized = imagecreatefrompng( $lg_gallery->plugin_dir . '/images/file_alert.png' ); 
  } else {
  	$resized = &$image->resized;
  }	
  if ( is_resource( $resized ) ) {
    switch( strtolower( $path[ 'extension' ] ) ) {   
  		case 'jpeg':
  		case 'jpg':
  		  imagejpeg( $resized );
  			break;
  		case 'gif':
      	imagegif( $resized );
  			break;
  		case 'png':
      	imagepng( $resized );
  			break;
  		default:
  			break;
  	}  
    imagedestroy( $resized );
  }  	
  die();
}

/**
 * lg_refresh_folder()
 * refresh image table in folder edit screen when upload thickbox closes
 * @since 1.1.0
 * @return void
 */
function lg_refresh_folder() {
	global $lg_paged, $lg_gallery;
	if ( isset( $_POST['folder'] ) ) {
		$lg_paged = isset( $_POST['lg_paged'] ) ? $_POST['lg_paged'] : 1;		
		$path = utf8_decode( rawurldecode( $_POST['folder'] ) );
		$folder = new LazyestFolder( $path );
		$folder->open();
		$folder->load( 'thumbs' );		
    $imagetable = new LazyestImageTable( $folder->list );
    $imagetable->page( 'lg_paged' );
    $imagetable->display();   
	}
	die();
}

/**
 * lg_swfupload()
 * 
 * used in async upload by flash uploader
 * @param string $path
 * @return string
 * @since 1.0
 */
function lg_swfupload() {
  global $lg_gallery, $file;
  require_once( dirname( __FILE__ ) . '/manager.php' );
	header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ) );
	wp_set_current_user( $_REQUEST['uid'] );
	check_ajax_referer();
	// set the gallery folder
	$file = stripslashes( utf8_decode( rawurldecode( $_POST['file'] ) ) );
	if ( $file == '' ) {		
		esc_html_e( 'No folder to store the image' , 'lazyest-gallery' );
	}	 
  $folder = new LazyestAdminFolder( $file );
  
  $message = $folder->swfuploadfiles();
  unset( $folder );
  echo $message;
  die();
}	
  
?>