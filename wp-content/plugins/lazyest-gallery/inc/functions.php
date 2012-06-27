<?php
/**
 * Template tags and functions for Lazyest Gallery
 * 
 * @package Lazyest-Gallery  
 * @author Marcel Brinkkemper
 * @copyright 2008-2012 Marcel Brinkkemper
 */
 
 
/**
 * lg_list_folders()
 * 
 * @param string $title
 * @return
 */
function lg_list_folders( $title ) {
  global $lg_gallery;
  if ( ! isset( $lg_gallery ) ) {
    return false;
  }
  $disp =  ( 'TRUE' == $lg_gallery->get_option( 'use_folder_captions' ) ) ? 'title' : 'dirname';
  if ( $title != '' ) {
    ?>
    <h2><?php echo $title; ?></h2>
    <?php
  }
  $folders = $lg_gallery->folders( 'root', 'visible' );
  if ( 0 < count( $folders ) ) {
    ?><ul><?php
    foreach( $folders as $folder ) {
      $folder->open();
      ?>
      <li><a href="<?php echo $folder->uri( 'widget' ); ?>" title="<?php echo $folder->title(); ?>"><?php echo lg_html( $folder->caption() ); ?></a>
      <?php $folder->list_folders( 'visible', $disp, 'widget' ); ?>
      </li>
      <?php 
    }
    ?></ul><?php
  }
}
 
 /**
 * lg_random_image() template tag to display random image
 * 
 * @param string $title
 * @param string $count
 * @param string $folder
 * @param bool $sub
 * @return
 */ 
function lg_random_image( $title, $count='1', $folder='', $sub=true ) {
  global $lg_gallery;
  if ( ! isset( $lg_gallery ) ) {
    return false;
  }
  if ( '' == $folder ) $sub = true;
  $substr = $sub ? 'subfolders' : 'root';
  $count = intval( $count ); 
  $image_list = $lg_gallery->random_image( $folder, $substr, $count );
  if ( $title != '' ) {
    ?>
    <h2><?php echo $title; ?></h2>
    <?php
  }
  ?>
    <div class="lazyest_sidebox">
  <?php 
  foreach ( $image_list as $thumb ) {
    $onclick = $thumb->on_click( 'widget' );
   	$rel = ( '' != $onclick['rel'] ) ? 'rel="' . $onclick['rel'] . '"' : '';   	
		$class= 'thumb';  		  
  	if ( ! file_exists( $thumb->loc() ) ) 
  		$class .= ' lg_ajax';
    ?>
    <p class="lg_thumb_image">
      <a href="<?php echo $onclick['href']; ?>" title="<?php echo $thumb->title() ?>" <?php echo $rel; ?> class="<?php echo $onclick['class'] ?>"><img class="<?php echo $class; ?>" src="<?php echo $thumb->src(); ?>" alt="<?php echo $thumb->alt(); ?>" /></a>
    </p>
    <?php
  }
  ?>
    </div>
  <?php
}


/**
 * lg_random_slideshow() slide show of random images (as a widget) in sidebar
 * 
 * @param string $title
 * @param string $count
 * @param string $display
 * @param string $folder
 * @param bool $sub
 * @return
 */
function lg_random_slideshow($title, $count='2', $display='5', $folder='', $sub=true ) {
   global $lg_gallery;   
  if ( ! isset( $lg_gallery ) ) return false;  	
  if ( '' == $lg_gallery->get_option('enable_slide_show') ) return false;
  if ( '' == $folder ) $sub = true;
  $substr = $sub ? 'subfolders' : 'root'; 
  $image_list = $lg_gallery->random_image( $folder, $substr, intval( $count ) );
  
  $min_width = $lg_gallery->get_option( 'thumbwidth') . 'px';
  $min_height = $lg_gallery->get_option( 'thumbheight') . 'px';
  if ( $title != '' ) {
    ?>
    <h2><?php echo $title; ?></h2>
    <?php
  }
  ?>
    <div class="lg_slideshow" id="lg_sideshow_<?php $lg_gallery->slideshows++; echo $lg_gallery->slideshows; ?>" style="min-width:<?php echo $min_width; ?>; min-height:<?php echo $min_height; ?>;"> 
      <div class="lg_loading"><?php esc_html_e( 'Loading...', 'lazyest-gallery' ); ?></div> 
  <?php
  foreach ( $image_list as $thumb ) {
    $onclick = $thumb->on_click( 'widget' );
		$class= 'thumb'; 
		$rel = ( '' != $onclick['rel'] ) ? 'rel="' . $onclick['rel'] . '"' : '';   	
  	if ( ! file_exists( $thumb->loc() ) ) 
  		$class .= ' lg_ajax';
    ?>
    <a id="<?php echo $onclick['id'] . '_' .$lg_gallery->slideshows ?>" href="<?php echo $onclick['href']; ?>" title="<?php echo $thumb->title() ?>" <?php echo $rel; ?> class="<?php echo $onclick['class'] ?>"><img class="<?php echo $class; ?>" src="<?php echo $thumb->src(); ?>" alt="<?php echo $thumb->alt(); ?>" /></a>
    <?php
  }
  ?></div><?php
}

/**
 * lg_nice_link()
 * encodes the URL but leaves slashes for nicer link in the gallery
 * 
 * @param string $alink 
 * @return string
 */
function lg_nice_link( $alink ) {
	return str_replace( '%2F', '/', rawurlencode( utf8_encode( $alink ) ) );
}

/**
 * lg_html()
 * This function makes sure captions and descriptions will be displayed with only the allowed html elements
 * Users should be albe to use html entities in the caption withou double encoding
 * Anchors should not be encoded
 * 
 * @param mixed $astring
 * @return void
 */
function lg_html( $astring ) {
  if ( $astring == '' ) return $astring;
  $astring  = esc_html( stripslashes( $astring ) ); 
  /* if an anchor is found, just convert all <, >, and quotes. Mind! this will fail if you use quotes or <, > in your text */
  if ( 0 != preg_match( "|&lt;a|", $astring ) ) {
    $astring = str_replace( "&lt;", "<", $astring );
    $astring = str_replace( "&gt;", ">", $astring );
    $astring = str_replace( "&quot;", "\"", $astring );
  } else {  /* else just replace the allowed html tags */    
    $astring = str_replace( "&lt;strong&gt;", "<strong>", $astring );
    $astring = str_replace( "&lt;/strong&gt;", "</strong>", $astring );
    $astring = str_replace( "&lt;br /&gt;", "<br />", $astring );
    $astring = str_replace( "&lt;em&gt;", "<em>", $astring );
    $astring = str_replace( "&lt;/em&gt;", "</em>", $astring );
    $astring = str_replace( "&lt;ul&gt;", "<ul>", $astring );
    $astring = str_replace( "&lt;/ul&gt;", "</ul>", $astring );
    $astring = str_replace( "&lt;ul&gt;", "<ul>", $astring );
    $astring = str_replace( "&lt;/ul&gt;", "</ul>", $astring ); 
  }
  return $astring;
}

/**
 * lg_esc_description()
 * prepares string value for editing in description textarea
 * 
 * @since 1.1.0
 * @param string $text
 * @return string
 */
function lg_esc_description( $text ) {
	$safe_text = esc_textarea( preg_replace('`<br(?: /)?>([\\n\\r])`', '$1', stripslashes( $text ) ) );
	return apply_filters( 'lg_esc_description', $safe_text, $text );
}

/**
 * lg_esc_caption()
 * prepares string value for editing in caption input
 * 
 * @since 1.1.0
 * @param mixed $text
 * @return
 */
function lg_esc_caption( $text ) {
	$safe_text = htmlspecialchars( stripslashes( $text ), ENT_QUOTES );
	return apply_filters( 'lg_esc_caption', $safe_text, $text );
}

/**
 * lg_add_extrafield()
 * Template tag to add an extra field to images or folders
 * should be called after Lazyest Gallery has initialized
 * example: 
 * 
 * add_action( 'lazyest_ready', 'myfunction' );
 * 
 * function myfunction {
 *   lg_add_extrafield( 'myfield', 'My Field', 'image', true );
 * }
 * 
 * @param string $field_name
 * @param string $display_name
 * @param string $target
 * @param bool $can_edit
 * @since 1.1.0
 * @return
 */
function lg_add_extrafield( $field_name, $display_name = '', $target = 'image', $can_edit = false ) {
  global $lg_gallery;
  $result = true;
  if ( ! isset( $lg_gallery ) ) {
    $result = false;
  } else {    
    $result = $lg_gallery->add_field( $field_name, $display_name, $target, $can_edit );
  } 
  return $result;
}

/**
 * lg_get_the_title()
 * Returns the title for the currently displaying folder or slide page
 * 
 * @since 1.1.0
 * @return string
 */
function lg_get_the_title() {
  global $lg_gallery;
  
  $page = get_page( $lg_gallery->get_option( 'gallery_id' ) );
  $title = esc_html( $page->post_title );
  unset( $page );
  
  if ( !isset( $lg_gallery ) )
  	return $title;
  $lg_gallery->valid();
		
  if ( $lg_gallery->is_image( $lg_gallery->file ) ) {
    $folder = new LazyestFolder( dirname( $lg_gallery->file ) );
    $image = $folder->single_image( basename( $lg_gallery->file ) );
    $title = $image->title();
    unset( $image, $folder );
  }
  if ( $lg_gallery->is_folder( $lg_gallery->file ) ) {
    $folder = new LazyestFolder( $lg_gallery->file );
    $folder->open();
    $title = $folder->title();
    unset( $folder );
  }
  return $title;
}


/**
 * lg_login_required()
 * Checks if login is required to view the current folder or slide page
 * 
 * @since 1.1.0
 * @return bool
 */
function lg_login_required() {
  global $lg_gallery;
  if ( is_user_logged_in() ) return false;
  if ( ! isset( $lg_gallery ) ) return false;
  if ( ! isset( $lg_gallery->file ) ) 
    $lg_gallery->valid();    
  if ( $lg_gallery->is_folder( $lg_gallery->file ) ) {
		$the_folder = new LazyestFolder( $lg_gallery->file ) ;
  } 
  elseif ( $lg_gallery->is_image( $lg_gallery->file ) ) {
    $the_folder = new LazyestFolder( dirname( $lg_gallery->file) );    
  } else {
    return false;
  }  
  $login_required = ! $the_folder->user_can( 'viewer' );
  unset( $the_folder );
  return $login_required;  
}

/**
 * lg_level_required()
 * Checks if a (higher) user level is required to view the current folder or slide page
 * @return
 */
function lg_level_required() {
  global $lg_gallery; 
  if ( ! isset( $lg_gallery ) ) return false;
  $lg_gallery->valid();
  if ( $lg_gallery->is_folder( $lg_gallery->file ) ) {
		$the_folder = new LazyestFolder( $lg_gallery->file ) ;
  } 
  elseif ( $lg_gallery->is_image( $lg_gallery->file ) ) {
    $the_folder = new LazyestFolder( dirname( $lg_gallery->file) );    
  } else {
    return false;
  }   
  $level_required = ! $lg_gallery->access_check( $the_folder );
  unset( $the_folder );
  return $level_required;  
}

/**
 * lg_get_users_of_blog()
 * Gets all users of blog that have at least contributor rights
 * @since 1.1.0
 * @uses get_users()
 * @uses class WP_User()
 * @uses user_can()
 * @return array
 */
function lg_get_users_of_blog() {
	global $lg_gallery;
  $blog_users = get_users();
  $result = array(); 
  // By default, user should be at least contributor to be selected as editor
  $capability = $lg_gallery->default_editor_capability();
  foreach ( $blog_users as $userdata ) {
    $user = new WP_User( $userdata->ID );
    if ( user_can( $user, $capability ) ) { 
      $result[] = $user;
    } else {   
      unset( $user );
    }
    unset( $userdata );
  }
  unset( $blog_users );
  return $result;
}

/**
 * lg_db()
 * for debugging 
 * works only if WP_DEBUG is defined
 * 
 * @param mixed $var
 * @param string $txt
 * @return void
 */
function lg_db($var,$txt=''){
	if( !	defined(	'WP_DEBUG'	)	)
		return;
	$txt = ( $txt == '' ) ? 'var' : $txt; 	
	printf ("<br /><b>%s</b> = %s<br />\n", $txt, htmlentities( print_r( $var, true ) ) );
}
?>