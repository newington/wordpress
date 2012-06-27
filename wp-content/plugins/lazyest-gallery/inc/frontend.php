<?php
/**
 * LazyestFrontend class
 * This class contains all functions and actions required for Lazyest Gallery to work on the frontend of WordPress
 * 
 * @version 1.1.4
 * @package Lazyest Gallery  
 * @author Marcel Brinkkemper
 * @copyright 2010-2012 Brimosoft
 * @since 0.16.0
 * 
 */
 
class LazyestFrontend extends LazyestGallery {
  
  /**
   * Keeps track of number of slideshows
   * 
   * @var int
   */
  var $slideshows;
  
  /** keeps track of the number of folder thumbnail tables
   * 
   * @var int
   * @since 1.1.0
   */
  var $dirshows;
  
  /** keeps track of the number of image thumbnail tables
   * 
   * @var int
   * @since 1.1.0
   */
  var $thumbshows;
  
  /** 
   * The ID of the comment to show for a folder or image
   * 
   * @var int
   */
  var $comment;
  
  /**
   * The slideshow request
   * 
   * @var string
   */
   var $slideshow;
  
  /**
   * Holds virtual root when given in the gallery shortcode.
   * 
   * @since 1.0.3
   * @var string
   */
  var $virtual_root;
  
  function __construct() {    
    LazyestGallery::__construct();
    
    $this->slideshows = $this->dirshows = $this->thumbshows = 0;    
    $this->slideshow = $this->comment = '';
    
    // actions
    add_action( 'wp_head', array( &$this, 'css_rules'), 1 );
    add_action( 'wp_head', array( &$this, 'styles' ), 2 );
    add_action( 'wp_head', array( &$this, 'scripts' ), 1);
    if ( 'TRUE' == $this->get_option( 'rel_canonical' ) ) {
			remove_action( 'wp_head', 'rel_canonical' );
			add_action('wp_head', array( &$this, 'rel_canonical' ) );
		}		
		$structure = get_option( 'permalink_structure' );    
    if ( ( 0 < strlen( $structure ) ) && ( 0 == strpos( $structure, 'index.php' ) ) && ( 'TRUE' == $this->get_option( 'use_permalinks' ) ) ) {
      add_action( 'generate_rewrite_rules', array( &$this, 'rewrite_rules' ) );
      add_action( 'init', array( &$this, 'flush_rules' ), 100 );
    }
    add_action( 'admin_bar_menu', array( &$this, 'admin_bar_menu' ), 100 );
		add_action( 'after_setup_theme', array( &$this, 'setup_theme' ) ); 
	      
		// filters        
    add_filter('query_vars', array( &$this, 'query_vars' ) );  
		    
    // shortcodes
    add_shortcode( 'lg_folder', array( &$this, 'folder_code' ) );
    add_shortcode( 'lg_gallery', array( &$this, 'gallery_code' ) );
    add_shortcode( 'lg_image', array( &$this, 'image_code' ) );
    add_shortcode( 'lg_slideshow', array( &$this, 'slideshow_code' ) ); 
  }
  
  /**
   * LazyestFrontend::rewrite_rules()
   * 
   * @param mixed $rules
   * @return void
   */
  function rewrite_rules( $rules ) {
    global $wp_rewrite;
    if ( 0 == strlen( $this->get_option( 'gallery_prev'  ) ) ) return;
    $pageid = $this->get_option( 'gallery_id' );
    $sitelen = strlen( get_option( 'home' ) ) + 1;    
    $page_path = untrailingslashit( substr( $this->get_option( 'gallery_prev' ), $sitelen ) );
    $new_rules = array( "$page_path/(.+)" => "index.php?pagename=$page_path&file=" . $wp_rewrite->preg_index( 1 ) );   
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
  }
  
  /**
   * LazyestFrontend::query_vars()
   * 
   * @param mixed $vars
   * @return
   */
  function query_vars( $vars ) { 
    $vars[] = 'file';
    $vars[] = 'lg_comment';
    $vars[] = 'lg_show';
    $vars[] = 'lg_paged';
    $vars[] = 'lg_pagei';
    return $vars;
  }
  
  /**
   * LazyestFrontend::flush_rules()
   * 
   * @return void
   */
  function flush_rules() {	
    global $wp_rewrite;
   	$wp_rewrite->flush_rules( false );    
  }
  
  /**
   * LazyestFrontend::css_rules()
   * Sets widths and heights
   * Basic width formatting for the list items
   * Calculation based on Folder Columns and Thumbnail Columns Settings
   * When setting = 0 (automatic), width is 10px wider than thumbnail width
   * 
   * Is outputted earliest in wp_head so stylesheet will overwrite
   * 
   * @since 1.1.0
   * @uses apply_filters
   * @return void
   */
  function css_rules() {
    $padding = apply_filters( 'lazyest_item_padding', 6 );
    $width = (int) $this->get_option( 'thumbwidth') + $padding;
    $imgwidth = '';
    if ( 0 == (int)$this->get_option( 'folders_columns' ) )
    	$fwidth = strval( $width ).'px';
    else {
    	$fwidth = strval( floor( 100 / (int) $this->get_option( 'folders_columns' ) ) -1 ).'%';
    	$imgwidth = '100%';
    }	
		if  ( 0 == (int)$this->get_option( 'thumbs_columns' ) ) 
			$iwidth =  strval( $width ).'px';
		else {
			$iwidth = strval( floor( 100 / (int) $this->get_option( 'thumbs_columns' ) ) -1 ).'%';
			$imgwidth = '100%';	
		}	
    printf ( "\n<style type='text/css'>li.lgf-item{width:%s;} li.lgi-item{width:%s}</style>\n", $fwidth, $iwidth );
    if ( '' != $imgwidth )    	
    	printf ( "\n<style type='text/css'>li.lgf-item img{max-width:%s;} li.lgi-item img{max-width:%s}</style>\n", $imgwidth, $imgwidth );
  }  
  
  /**
   * LazyestFrontend::styles()
   * Enqueues all stylessheet needed for frontend
   * 
   * @return void
   */
  function styles() {
    $c = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'dev.css' : 'css'; 
    $styles = array();     
    $stylesheet =  $this->get_option( 'style_css' );  // get theme stylesheet
    if ( ( '' != $stylesheet ) && ( 'no_style' != $stylesheet ) ) { // don't add stylesheet when blog theme is used
      $theme_file = trailingslashit( $this->themes_dir() ) . $stylesheet;
      if ( file_exists( $theme_file ) ) {
        $styles[] = trailingslashit( $this->themes_url() ) . $stylesheet;  
      }   
    } 
    // add lazyest native stylesheets
    if ( 'TRUE' == $this->get_option( 'enable_slide_show') )
      $styles[] = $this->plugin_url . "/css/_slideshow.$c";  
    $styles[] = $this->plugin_url . "/css/_ajax.$c";
    $i = 0;
    if ( 0 < count( $styles) ) {
      foreach( $styles as $style_css ) {
        if ( '' != $style_css ) {
          $style_name = 'lazyest-style_' . $i;
          wp_register_style( $style_name, $style_css );
          wp_enqueue_style( $style_name ); 
          $i++;    
        }
      }
    }
  }
  
  /**
   * LazyestFrontend::scripts()
   * 
   * @return void
   */
  function scripts() {   
    $j = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'dev.js' : 'js';     
    wp_enqueue_script( 'lg_gallery', $this->plugin_url . "/js/lazyest-gallery.$j", array( 'jquery' ), '1.1', true );
    wp_localize_script( 'lg_gallery', 'lazyest_ajax', $this->localize_lazyest() );
    if ( 'TRUE' == $this->get_option( 'enable_exif' ) ) {
      wp_enqueue_script( 'jquery' );
    }
    if ( 'TRUE' == $this->get_option( 'enable_slide_show' ) ) {    
      wp_enqueue_script( 'lg_slideshow', $this->plugin_url . "/js/lazyest-slideshow.$j", array( 'jquery' ), '1.1', true );
      wp_localize_script( 'lg_slideshow', 'lazyestshow', $this->localize_show() );
    }
    if ( 'TRUE' == $this->get_option( 'async_cache' ) ) {
      wp_enqueue_script( 'lg_loader', $this->plugin_url . "/js/lazyest-loader.$j", array( 'jquery' ), '1.1', true );
      wp_localize_script( 'lg_loader', 'lazyestimg', $this->localize_loader() );
    }
    if ( 'TRUE' == $this->get_option( 'theme_javascript' ) ) {
      $theme_file = str_replace( '.css', '.js', $this->get_option( 'style_css' ) );
      $theme_path = trailingslashit( $this->themes_dir() ) . $theme_file;
      if ( file_exists( $theme_path ) ) {          
        $theme_script = trailingslashit( $this->themes_url() ) . $theme_file;
        wp_enqueue_script( 'lg_theme_script', $theme_script, array( 'jquery' ) , '1.1', true );  
      }
    }
  }
  
  /**
   * LazyestFrontend::localize_lazyest()
   * Strings for lazyest gallery frontend javascript
   *
   * @return array
   */
  function localize_lazyest() {
    return array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'searchfor' => __('Searching for comment...', 'lazyest-gallery' ),
      'pleasewait' => __('Please wait while Lazyest Gallery searches for ', 'lazyest-gallery' ),
			'pagination' => ( 'TRUE' == $this->get_option( 'ajax_pagination' ) ) ? 'ajax' : 'default'      
    ); 
  }
  
  
  /**
   * LazyestFrontend::localize_show()
   * Variables for slideshow javascript
   * 
   * @return array
   */  
  function localize_show() {
    $option = $this->get_option('slide_show_duration');
    $duration = ( $option != '' ) ? (int)$option : 5; 
    return array(
      'captionqueue' => $duration * 400,
      'captionopcty' => $duration * 400,
      'slideview' => $duration * 200, 
      'duration' => $duration * 1000     
    );
  }
  
  /**
   * LazyestFrontend::setup_theme()
   * Set up filters and action depending on active theme
   * 
   * @since 1.1.9
   * @return void
   */
  function setup_theme() {
		// fix for genesis and catalyst framework
		$priority = ( function_exists( 'genesis' ) ) || ( function_exists( 'catalyst_activate' ) ) ? 6 : 50; 
    add_filter('wp_title', array( &$this, 'wp_title' ), $priority, 3 );    
  }
     
   /**
   * LazyestFrontend::validate_dir()
   * Checks $this->file for valid gallery directory or image
   * If false, $this->file will be shortended to try one level up
   * 
	 * @since 1.0.40 
   * @return bool
   */
  function validate_dir() {
  	$valid = true;			
		if ( '' != $this->file ) {	 			 	  	
			$dotdot = strstr( $this->file, '..' );
			$valid = false === $dotdot;	     
			$valid = file_exists( $this->root . $this->file );
	    // if filevar does not validate, try to jump one level up
	    if ( ! $valid ) {
	    	$strarr = explode( '/', $this->file );
				while ( ! $valid  && ( count( $strarr ) != 0 ) )  {
					unset( $strarr[count( $strarr ) - 1] );
					$this->file = implode( '/', $strarr );
					$valid = $this->validate_dir();					
				} 	
	    }				    
      if ( is_dir( $this->root . $this->file ) ) {     
        $folder = new LazyestFolder( $this->file );
        $valid = $folder->valid(); 
        unset( $folder );
      } else {
      	$valid = ( 0 != preg_match( "/.*\.(jpg|gif|png|jpeg)/i", $this->file ) ); 
      }
    }  
  	return $valid;
  }
  
  /**
   * LazyestFrontend::file_decode()
   * Decodes the file query 
   * 
   * @return string;
   */
  function file_decode() {
  	global $file;
  	$this_file = '';
  	if ( isset( $file ) ) {
 			$this_file = rawurldecode( $file );      
    } else {
      if ( isset( $_GET['file'] ) ) {   
        $this_file = rawurldecode( $_GET['file'] ); 
      } 
    }       		
		$this_file = utf8_decode( stripslashes( $this_file ) );
		return $this_file;
  }
     
  /**
   * LazyestFrontend::valid()
   * Sets the path to folder or image from query var 'file''
   * Sets query var 'cpage' if comment-page is found in 'file;'
   * Handles other Lazyest Gallery query vars for commenting and slideshow
   * 
   * @param string $filevar : path to check if it is a valid gallery folder or image
   * @return bool 
   */
  function valid() { 
  	global $lg_comment, $lg_show, $wp_query, $lg_paged, $lgpagei; // will be set by wordpress query_vars
		 		     
		$this->file = $this->file_decode();
    $comment_pos = strpos( $this->file, 'comment-page-' );
    if ( $comment_pos !== false ) {
      $comment_page = substr( $this->file, $comment_pos + 13 );
      set_query_var( 'cpage', $comment_page );
      $this->file = substr( $this->file, 0, $comment_pos );
    }   
		$feed_pos = strpos( $this->file, 'feed' );
		if ( $feed_pos !== false ) {
			set_query_var( 'feed', 'comments-rss2' );
		}
		          
    if ( isset( $lg_comment ) ) {      
       $this->comment = $lg_comment;
    } else {  
     if ( isset( $_GET['lg_comment'] ) ) {
       $this->comment = $_GET['lg_comment'];
     }
    }   
    if ( isset( $lg_show ) ) {
      $this->slideshow = $lg_show;
    } else {     
      if ( isset( $_GET['lg_show'] ) ) {
        $this->slideshow = $_GET['lg_show'];
      } 
    }
    
    if ( ! isset( $lg_paged ) ) {
    	if ( isset( $_REQUEST['lg_paged'] ) ) {
    		$lg_paged = absint( $_REQUEST['lg_paged'] );
    	}
		}
		
		if ( ! isset( $lg_pagei ) ) {
    	if ( isset( $_REQUEST['lg_pagei'] ) ) {
    		$lg_pagei = absint( $_REQUEST['lg_pagei'] );
    	}
		}
    
    // for compatibility sake: redirect ofsset queries
    if ( isset( $_REQUEST['lg_offset'] ) ) {
    	$offset = absint( $_REQUEST['lg_offset'] );
    	$lg_pagei = $offset / $this->get_option( 'thumbs_page' ) + 1;
    }
		if ( isset( $_REQUEST['lg_diroffset'] ) ) {
    	$offset = absint( $_REQUEST['lg_diroffset'] );
    	$lg_paged = $offset / $this->get_option( 'folders_page' ) + 1;
    }
      
    // validate dir    
    if ( ! $this->validate_dir() )
    	return false;
   	
    $path = pathinfo( $this->file );
    $this->currentdir = ( is_dir( $this->root . $this->file ) ) ? ltrim( $this->file, '/' ) :  trailingslashit( ltrim( $path['dirname'], '/' ) );
    return true;
  }
   
  /**
   * LazyestFrontend::is_gallery()
   * Checks if the current page is the gallery page.
   * Should also run before wordpress is-page() exists
   * 
   * @return bool
   */
  function is_gallery() {
  	$protocol = 'http' . ( ( isset( $_SERVER['HTTPS'] ) && ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) ) ? 's' : '') . '://';
  	$server = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
  	$current_uri = $protocol . $server . $_SERVER['REQUEST_URI'];
    if ( ( $this->get_option( 'use_permalinks' ) == 'TRUE' ) && ( strlen( get_option( 'permalink_structure' ) ) > 0 ) ) {
      $gal = strpos( $current_uri, $this->get_option( 'gallery_prev' ) );
      return ( false === $gal ) ? false : true;
    } else {
      if ( function_exists( 'is_page' ) ) {
        return is_page( $this->get_option( 'gallery_id' ) );
      } else {  
        return false;
      }
    }	
  }
  
  /**
   * LazyestFrontend::folder_code()
   * Returns html code for a folder shortcode
   * 
   * @example [lg_folder folder="afolder/" count="10" cols="3" paging="true"] 
   * @param mixed $atts
   * @return string
   */
  function folder_code( $atts ) {
    global $lg_show;
    extract( shortcode_atts( array( 'folder' => '', 'count' => $this->get_option( 'thumbs_page' ), 'cols' => $this->get_option( 'thumbs_columns' ), 'paging' => 'true' ), $atts ) );
    $folder = trailingslashit( ltrim ( utf8_decode( $folder), '/') );
    $folder = html_entity_decode( $folder );
    $this->valid();
    $folder_code = sprintf( __( 'Lazyest Gallery cannot access %s', 'lazyest-gallery'), lg_html( $folder ) );     
    $paging = strtolower( $paging );     
    if ( isset( $lg_show ) ) {
      $this->slideshow = $lg_show;
    } else {     
      if ( isset( $_GET['lg_show'] ) ) {
        $this->slideshow = $_GET['lg_show'];
      } 
    } 
    $the_folder = new LazyestFrontendFolder( $folder );
    $this->valid(); // set $this->file     
    if ( $the_folder->valid() ) {      
      if ( ( 'true' == $this->slideshow ) && ( $this->file == $folder ) ) {       
        ob_start();
        $the_folder->slideshow();
      } else {
        $the_folder->open();
        ob_start();
      ?>
  		<div class="lg_gallery">
      <?php  
        if ( isset( $this->file ) && $this->is_image( $this->file ) && ( $this->currentdir == $the_folder->curdir ) ) {        
            $the_folder->show_slide( basename( $this->file ) );
          } else {          
            $the_folder->show_thumbs( $count, $cols, $paging );
          }
        ?>
      </div>        
      <?php
      $virtualroot = isset( $this->virtual_root ) ? urlencode( $this->virtual_root ): '';
			echo "\n<script type='text/javascript'>var lazyest_virtual = { root: '$virtualroot' };</script>\n"; 
      }
      
    $folder_code = ob_get_contents();
    ob_end_clean();
    }
    unset( $the_folder );
    return $folder_code;
  }
  
  /**
   * LazyestFrontend::set_root()
   * Sets a different root for a gallery shortcut
   * use [lg_gallery root="folder"]
   * The new root is a subfolder of the gallery 
   * Private folders cannot be set as root folder
   * 
   * @since 1.0.3
   * @param string $root
   * @return bool
   */
  function set_root( $root ) {
  	global  $current_user;
  	
		$new_root = str_replace( "\\", "/", $this->get_absolute_path( path_join( $this->root, $root ) ) );
		if ( ! file_exists( $new_root ) )
			return false;
			
  	get_currentuserinfo();
  	$folder = new LazyestFolder( $root );
  	if ( false !== $folder ) {
  		$folder->open();
  		$this->change_option( 'viewer_level', $folder->viewer_level );
  		if ( ( 'private' == $folder->visibility ) && ( $current_user->ID != $folder->editor ) )
				return false; 	    
	    $this->root = trailingslashit( $new_root );
	    $this->address = trailingslashit( path_join( $this->address, $root ) );
	    $this->virtual_root = trailingslashit( $root );
	    return true;				
    } 
    return false;
  }
  
  /**
   * LazyestFrontend::gallery_code()
   * Returns the html code for the gallery shortcode
   * 
   * @example [lg_gallery root="afolder/"]
   * @param mixed $atts
   * @return string
   */
  function gallery_code( $atts ) {
    extract( shortcode_atts ( array( 'lg_gallery' => '', 'root' => ''), $atts) );
    if ( '' != $root ) {
      $this->set_root( utf8_decode( $root ) );
    }
    ob_start(); 
    $show = ( ! isset( $this->file ) ) ? true : $this->valid();       
    if ( $show === false ) {
      ?>
      <div class="error">
        <p><strong><?php esc_html_e( 'Something went wrong initializing Lazyest Gallery.', 'lazyest-gallery' ); ?></strong></p>
        <p><?php esc_html_e( 'Maybe the folder or the image you are looking for does not exist','lazyest-gallery' ); ?></p>
        <p>
      <?php                 
        if ( current_user_can( 'manage_options' ) ) {
        	/* translators 1: <a href="">, 2: </a> */
          echo sprintf( esc_html__( 'Please check your %1ssettings%2s  or contact the author of this page'),
						sprintf( '<a href="%s">', admin_url( 'admin.php?page=lazyest-gallery' ) ),
						'</a>' 
					);
        } else {          
          esc_html_e( 'Please contact the author of this page.', 'lazyest-gallery' );  
        }
      ?>      
        </p>
      </div>
      <?php 
    } else {    
      $this->show();  			                
    } 
    $new_content = ob_get_contents();
    ob_end_clean();
    return $new_content;
  }
  
  /**
   * LazyestFrontend::image_code()
   * Returns the html code for an image in a post 
   * 
   * @todo add WordPress classes
   * @param mixed $atts
   * @return string
   */
  function image_code( $atts ) {
    
    extract( shortcode_atts ( array( 'folder' => '', 'image' => '', 'align' => '', 'width' => '', 'height' => '', 'caption' => '', 'display' => 'thumb'), $atts));    
    $folder = trailingslashit( utf8_decode( $folder ) );   
    $image = utf8_decode( $image ); 
    $image_code = '<p class="error">' . sprintf( __( 'Lazyest Gallery cannot access %s', 'lazyest-gallery' ), lg_html( $folder ) ) . '</p>';
    if ( is_readable( $this->root . $folder ) && ( '/' != $folder ) ) {      
      $ifolder = new LazyestFolder( $folder );
      if ( false !== $ifolder ) {
	      $the_image = $ifolder->single_image( $image, $display . 's' );
	      if ( ! $the_image ) { 
	        $image_code = '<p class="error">' . sprintf( __( 'Lazyest Gallery cannot find %s', 'lazyest-gallery' ), lg_html( $image ) ) . '</p>';
	      } else {  
	      	$width = intval( $width );
	      	$height = intval( $height );
		      $img_location = $the_image->loc(); 	      	
	    		list( $iwidth, $iheight ) = @getimagesize( $img_location );
	    		if ( 0 == intval( $iheight ) ) 
						$iheight = $this->get_option( 'pictheight' );
	    		if ( 0 == intval( $iwidth ) ) 
						$iwidth = $this->get_option( 'pictwidth' );
	    		
	    		// set width of containing div;
	    		$div_width = ( 0 < $width  ) ? $width : $iwidth;
	    		$div_width = $div_width + 10;
	    		
	    		// only one attribute is set
	    		if ( ( 0 < $height && 0 == $width ) || ( 0 == $height && 0 < $width ) ) {
	    			if ( 0 < $height )
	    				$width = round( $height / $iheight * $iwidth );
	    			else  		    			
	    				$height = round( $width / $iwidth * $iheight );
					}
	        $img_src = $the_image->src();        
	        $img_link = $the_image->on_click( 'widget' );
	        $attr_width = 'width:' . strval( $div_width ) . ';';
	        if ( 'image' == $display ) {
						$img_src = $the_image->src();
	        }
	        $image_link = ''; 
	        unset( $ifolder );	 
					
					// set style for left, right or centered        
	        $image_code = '<div class="lg_image ' . $display . '" style="';
	        if ( 'left' == $align ) {
	            $image_code .= 'float:left;';
	        } elseif ( 'right' == $align ) {
	            $image_code .= 'float:right;';
	        } elseif ( 'center' == $align ) {
	            $image_code .= 'margin-left:auto;margin-right:auto;';
	        }  
	        $image_code .= $attr_width;
	        $image_code .= '"><div style="text-align:center">';
	        $wcode = ( 0 < $width ) ? $width : $iwidth;
	        $hcode = ( 0 < $height ) ? $height : $iheight;
	        $rel = ( '' != $img_link['rel'] ) ? ' rel="' . $img_link['rel'] . '"' : '';
	        if ( 'image' != $display )
	        	$image_link = '<a href="' . $img_link['href'] . '" class="' . $img_link['class'] . $rel . ' title="' . $the_image->title() . '" >';
	        $image_link .= '<img src="' . $img_src . '" alt="' . $the_image->title() . '" width="' . $wcode . '" height="' . $hcode . '" />';
					if ( 'image' != $display )
						$image_link .= '</a>';        
	        $image_code .= $image_link;
	        $image_code .= '</div><div class="caption">';
	        if ( ( '' == $caption ) && ( 'TRUE' == $this->get_option( 'enable_captions' ) ) ) {
	          $caption = $the_image->caption();
	        }
	        if ($caption != '') {
	          $image_code .= lg_html( $caption );
	        }
	        $image_code .= '</div></div>';
	      }
	      unset( $the_image );
			}
    }   
  	return $image_code;
  }
 
  /**
   * LazyestFrontend::slideshow_code()
   * Returns html code for a show of slides from a folder
   * If no folder is given, a random folder will be selected 
   * 
   * @example [lg_slideshow folder="afolder" display="slide"]
   * @param mixed $atts
   * @return string
   */
  function slideshow_code($atts) {
    extract( shortcode_atts( array( 'folder' => '', 'display' => 'slide' ), $atts ) );
    $where = lg_nice_link( $folder );
    $goodfolder = false;
    if ( '' ==  $folder )  {
      $where = __( 'the Gallery', 'lazyest-gallery' );
      $folders = $this->folders( 'subfolders', 'visible' );
      while ( ! $goodfolder && ( 0 < count( $folders ) ) ) {
        $key = array_rand( $folders );
        $sfolder = $folders[$key];
        if ( 0 != $sfolder->count() ) {
          $goodfolder = true;
          $folder = $sfolder->curdir;
        }
        unset( $folders[$key] );             
      }       
    }   
    $slideshow_code = esc_html( sprintf( __( 'Lazyest Gallery cannot find images in %s', 'lazyest-gallery' ), $where ) ); 
    if ( ! $goodfolder ) {      
      $folder = trailingslashit( ltrim( utf8_decode( $folder ), '/' ) );      
      $folder = html_entity_decode( $folder );
    }    
    $sfolder = new LazyestFrontendFolder( $folder );  
    if ( $sfolder->valid() ) {
        ob_start();
        echo "<div class='lg_gallery'>\n";
        $sfolder->slideshow( $display );
        echo "</div>\n";
        $slideshow_code = ob_get_contents();
        ob_end_clean();
    }
    return $slideshow_code;
  }

  /**
   * LazyestFrontend::get_folder()
   * 
   * @return class LazyestFrontendFolder
   */
  function get_folder() {
    return new LazyestFrontEndFolder( $this->currentdir );
  }
  
  /**
   * LazyestFrontend::_is_dir()
   * Checks if the requested file is a directory
   * 
   * @internal
   * @return bool
   */
  function _is_dir() {
    return is_dir( $this->root . $this->file );
  }
  
  /**
   * LazyestFrontend::do_slide()
   * Output a slide page, can be changed by a filter
   * 
   * @param LazyestFrontendFolder $folder
   * @param string $filevar
   * @return void
   */
  function do_slide( $folder, $filevar ) { 
  	ob_start();
  	$folder->show_slide( $filevar );
  	$do_slide = ob_get_contents();
    ob_end_clean();  	 
    $do_slide = apply_filters( 'lazyest_do_slide', $do_slide, $folder, $filevar );
    echo $do_slide;
  }
  
  /**
   * LazyestFrontend::show()
   * Build the html code for the full gallery
   * 
   * @param integer $count number of folders
   * @param integer $cols number of folder columns
   * @param bool $dirs
   * @return
   */
  function show( $count = -1, $cols = -1 ) { // builds main gallery page
   
    if ( 'TRUE' == $this->get_option( 'allow_comments' ) && ( '' == $this->comment ) && ( '' == $this->file ) ) { 
      echo "<script type=\"text/javascript\">lazyestGallery=true;</script>\n";
    } 
		 
    $folder = null;       
    if ( '' != $this->file ) {      
      $folder = $this->get_folder();
      if ( ! $folder->valid() ) {  
        unset( $folder );
        return false;
      } else {        
        $folder->open();
        $folder->load( 'thumbs' );
      }
    }
    
		if ( ! $this->access_check( $folder )  ) 
			return;
  	    
    echo "<div class='lg_gallery'>\n";
   	
		$virtualroot = isset( $this->virtual_root ) ? urlencode( $this->virtual_root ): '';
		echo "\n<script type='text/javascript'>var lazyest_virtual = { root: '$virtualroot' };</script>\n"; 
    
		$this->create_navigation();
                         
    $dcount = ( -1 == $count ) ? $this->get_option( 'folders_page' ) : $count;
    $dcols = ( -1 == $cols ) ?  $this->get_option( 'folders_columns' ) : $cols;
    if ( isset( $folder ) )
    	echo apply_filters( 'lazyest_folder_header', $folder->folder_header() );
    if ( isset( $folder ) && ( 'true' == $this->slideshow ) ) {
      $folder->slideshow('slide');
    } else { 
      if ( ! $this->is_image( $this->file ) ) { 
        $this->show_dirs( $folder, $dcount, $dcols ); // show (sub)folders
      }   
      if ( isset( $folder ) ) {                      // not on the gallery root
        if (  $this->_is_dir() ) { // it is a folder; show thumbnails
          $count = ( -1 == $count ) ? $this->get_option( 'thumbs_page' ) : $count;
          $cols = ( -1 == $cols ) ?  $this->get_option( 'thumbs_columns' ) : $cols;
          $folder->show_thumbs( $count, $cols ); // show thumbs
        } else  { 
        	$this->do_slide( $folder, basename( $this->file ) );
        }
      }
    }
    unset( $folder );
    $this->credits_div();
   
    echo "</div>\n";
  }
  	
	/**
	 * LazyestFrontend::admin_bar_menu()
	 * Show edit links for gallery in WordPress admin bar
	 * 
	 * @since 1.1
	 * @return void
	 */
	function admin_bar_menu() {
		global $wp_admin_bar;
		if ( ! $this->is_gallery() )
			return;
		if ( ( $this->get_option( 'new_install' ) != 'TRUE' ) && $this->valid() && current_user_can( 'edit_lazyest_fields' ) ) {
			$wp_admin_bar->add_menu( array( 'id' => 'lazyest-menu', 'title' => __( 'Lazyest', 'lazyest-gallery' ), 'href' => '#' ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'lazyest-menu', 'id' => 'lazyest-gallery-manage',  'title' => __( 'Manage Gallery', 'lazyest-gallery' ), 'href' => admin_url( 'admin.php?page=lazyest-filemanager') ) );
			if ( current_user_can( 'edit_lazyest_fields' ) && ! is_search() && ( $this->is_folder( $this->file ) || $this->is_image( $this->file ) ) ) {
				$wp_admin_bar->add_menu( array( 'parent' => 'lazyest-gallery-manage', 'title' => __( 'Edit Folder', 'lazyest-gallery' ), 'href' => admin_url( 'admin.php?page=lazyest-filemanager&amp;folder=' . urlencode( $this->currentdir ) ) ) );
			}
			if ( current_user_can( 'edit_lazyest_fields' ) && ! is_search() && $this->is_image( $this->file ) ) {
				$folder = new LazyestFolder( $this->currentdir );
				$filename = basename( $this->file );
				$image = $folder->single_image( $filename );
				$wp_admin_bar->add_menu( array( 'parent' => 'lazyest-gallery-manage', 'id' => 'lazyest-gallery-edit-image', 'title' => __( 'Edit Image', 'lazyest-gallery' ), 'href' => admin_url( 'admin.php?page=lazyest-filemanager&amp;folder=' . urlencode( $this->currentdir ) . '#' . $image->form_name() ) ) );				
			}
			if ( current_user_can( 'edit_posts' ) && ( 'TRUE' == $this->get_option( 'allow_comments' ) ) ) {
				$wp_admin_bar->add_menu( array( 'parent' => 'lazyest-menu', 'id' => 'lazyest-gallery-edit-comments', 'title' => __( 'Comments', 'lazyest-gallery' ), 'href' => admin_url( 'admin.php?page=lazyest-filemanager&edit=comments&amp;file=' . lg_nice_link( $this->file ) ) ) );
			}		
			if ( current_user_can( 'edit_themes') )
				$wp_admin_bar->add_menu( array( 'parent' => 'lazyest-menu', 'id' => 'lazyest-gallery-edit-themes', 'title' => __( 'Themes', 'lazyest-gallery' ), 'href' => admin_url( 'admin.php?page=lazyest-themesmanager' ) ) );						
    	if ( current_user_can( 'manage_options' ) )
				$wp_admin_bar->add_menu( array( 'parent' => 'lazyest-menu', 'id' => 'lazyest-gallery-settings', 'title' => __( 'Gallery Settings', 'lazyest-gallery' ), 'href' => admin_url( 'admin.php?page=lazyest-gallery') ) );			
		}	
	}	
  
  /**
   * LazyestFrontend::credits_div()
   * outputs the 'powered by' credits line
   * 
   * @since 1.1.0
   * @return void
   */
  function credits_div() {
    if ( 'TRUE' != $this->get_option( 'show_credits' ) ) 
      return;
    $credits_div = '<div class="lg_powered"><div class="lgpow">';
    $credits_div .= sprintf( __( 'Powered by <a href="%s">Lazyest Gallery %s</a> Copyright &copy; 2008-%s <a href="%s">%s</a>', 'lazyest-gallery' ),
      'http://wordpress.org/extend/plugins/lazyest-gallery/',
      lg_version(),
      date( 'Y' ),
      'http://brimosoft.nl/',
      'Brimosoft'
    );  
    $credits_div .= "</div></div>\n";
    echo $credits_div;
  }  
  
  /**
   * LazyestFrontend::_sep()
   * Filtered separator used in 'now viewing' breadcrumbs
   * 
   * @since 1.1.0
   * @uses apply_filters()
   * @return string
   */
  function _sep() {
    return apply_filters( 'lazyest_separator', '&raquo;' );
  }
  
  /**
   * LazyestFrontend::create_navigation()
   * Show the navigation breadcrumb trail
   * 
   * @uses apply_filters()
   * @uses get_bloginfo()
   * @uses get_the_title()
   * @uses trailingslashit()
   * @return void
   */
  function create_navigation() {
  	global $post;  	
    $nav = explode( '/', untrailingslashit( $this->currentdir ) );
    $path = pathinfo( $this->file );
    $current = '';
    $now_viewing = apply_filters( 'lazyest_now_viewing', __( ' Now viewing: ', 'lazyest-gallery' ) );
    $sep = $this->_sep();
    $navigator = sprintf( '<div class="top_navigator">%s <a href="%s">%s</a> <span class="raquo">%s</span> <a href="%s">%s</a>',
      $now_viewing,
      get_bloginfo( 'url' ), 
      get_bloginfo( 'name' ),
      $sep,
      $this->uri(),
      get_the_title( $post->ID )
    );    
    if ( $nav[0] != '' ) {
	    foreach ( $nav as $n ) {
	      $current .= trailingslashit( $n );
	      $folder = new LazyestFrontendFolder( $current );
	      $folder->open();
	      $navigator .= sprintf( ' <span class="raquo">%s</span> <a href="%s">%s</a> ',
	        $sep,
	        $folder->uri(),
	        $folder->title()
	      );
	      unset( $folder );
	    }
		}
    if ( ! is_dir( $this->root . $this->file ) ) {
      $folder = new LazyestFolder( $this->currentdir );
      if ( $folder->valid() ) {
        $image = $folder->single_image( $path['basename'] );        
        $navigator .= sprintf ( ' <span class="raquo">%s</span> <a href="%s">%s</a>',
          $sep, 
          $image->uri(), 
          $image->title()
        );    
      }
      unset( $folder );
    }
    $navigator .= "</div>\n";
    echo apply_filters( 'lazyest_navigator', $navigator );
  }

  /**
   * LazyestFrontend::show_dirs()
   * Show the folders view
   * 
   * @param LazyestFolder $folder 
   * @param integer $perpage number of folders per page
   * @param integer $columns number of columns
   * @return void
   */
  function show_dirs( $folder = null, $perpage = 0, $columns = 1 ) {
    global $lg_paged, $current_user;
    
    if ( ! $this->access_check( $folder ) ) 
			return;	
    $columns =  ( 'TRUE' == $this->get_option( 'table_layout') ) ? max( 1, $columns ) : max( 0, $columns );
    $perpage = max( 0, $perpage );
    
    $folders = ( null != $folder ) ? $folder->subfolders( 'visible' ) : $this->folders( 'root', 'visible' );
    if ( 0 == count( $folders ) ) 
      return;		     
    $foldervalue = ( null != $folder ) ? urlencode( $folder->curdir ) : '';
    $start = 1;      
    $end = count( $folders );        
    $query_var = 'lg_paged';
    $lg_paged = isset( $lg_paged ) ? (int)$lg_paged : isset( $_REQUEST[$query_var] ) ? absint( $_REQUEST[$query_var] ) : null;
    printf( '<div class="folders"><!-- Lazyest Gallery %s -->%s', lg_version(), "\n" );
		if ( 0 < $perpage) {    
      $total_pages = ceil( count( $folders ) / $perpage );
      if ( isset ( $lg_paged ) ) {
        $current = max( 1, $lg_paged);
      } else {      
        $current = isset( $_REQUEST[$query_var] ) ? absint( $_REQUEST[$query_var] ) : 0;	
        $current = min( max( 1, $current ), $total_pages );
      }
      $start = ( $current - 1 ) * $perpage + 1;
      $this->dirshows++;
      $current_user = get_currentuserinfo();
      $end = min( count( $folders ), $current * $perpage);
      if ( ( $perpage < count( $folders ) ) && ( $perpage != 0 ) ) { 
      	$ajax_nonce = wp_create_nonce( 'show_dirs' );
        printf( '<form name="folders_page_%s" action="%s" method="post">', $this->dirshows, $this->uri() );
        printf( '<input type="hidden" name="current" value="%s" />', $current );
        printf( '<input type="hidden" name="last_page" value="%s" />', ceil( count( $folders ) / $perpage ) );
        printf( '<input type="hidden" name="folder" value="%s" />', $foldervalue );  
        printf( '<input type="hidden" name="virtual" value="%s" />', urlencode( $this->virtual_root ) );
        printf( '<input type="hidden" name="perpage" value="%s" />', $perpage );
        printf( '<input type="hidden" name="columns" value="%s" />', $columns );
				printf( '<input type="hidden" name="ajax_nonce" value="%s" />', $ajax_nonce );        
        printf( '<input type="hidden" name="request_uri" value="%s" />', remove_query_arg( 'lg_paged', $_SERVER['REQUEST_URI'] ) ); 
				printf( '<input type="hidden" name="user_id" value="%s" />', $current_user->ID );       
      } 		
  	}           
    // this is where we the actually echo the folders 
    echo ( 'TRUE' == $this->get_option( 'table_layout') ) ? $this->dir_table( $folders, $start, $end, $columns ) : $this->dir_view( $folders, $start, $end );    

    if ( ( $perpage < count( $folders ) ) && ( $perpage != 0 ) ) {
      printf ( '<div class="folder_pagination">%s<br style="clear:both;" /></div></form>', $this->pagination( 'folders', $folders ) );            
    } 
    echo "</div>";
  }
  
  /**
   * LazyestFrontend::_above_folders()
   * Filtered title above the folders listing
   * 
   * @since 1.1.0
   * @return string
   */
  function _above_folders() {
    return apply_filters( 'lazyest_above_folders', __(  'Folders', 'lazyest-gallery' ) );
  }
  
  /**
   * LazyestFrontend::_cabinet()
   * Filtered cabinet image
   * 
   * @since 1.1.0
   * @return string <img /> element
   */
  function _cabinet() {
    return apply_filters( 'lazyest_cabinet', sprintf( '<img class="lg_folders_img icon" src="%s" alt="folders" />', $this->plugin_url . '/images/folders.png' ) );
  }
  
  /**
   * LazyestFrontend::dir_table()
   * returns html of folders table
   * 
   * @since 1.1.0
   * @deprecated after 1.1.0 this function will no longer be updated
   * @uses apply_filters()
   * @param array $folders
   * @param integer $start
   * @param integer $end
   * @param integer $columns
   * @return string 
   */
  function dir_table( $folders, $start = 1, $end = 1, $columns = 1 ) {    
    if ( ( 0 == count( $folders ) ) ) 
      return '';
    
    $dir_table = '';
    $col_count = 0;
    $columns = ( $columns == 0 ) ? 1 : $columns;    
    $show_image = 'none' != $this->get_option( 'folder_image' ); 
    $div_width = ( ! $show_image ) ? 0 : (int)$this->get_option( 'thumbwidth' );
            
    $dir_table = '<table class="dir_view"><tbody>';
    $dir_table .= sprintf( '<tr><td colspan="%d" class="folder">%s %s</td></tr><tr>',
      $columns, 
      $this->_cabinet(),
      esc_html( $this->_above_folders() )
    );
    
    $i = $start -1;
    while ( $i < $end ) {                  
      $folderi = $folders[$i];         
      if ( 'hidden' != $folderi->visibility ) { // do not insert a table cell when folder is hidden
      $dir_table .= sprintf( '<td><div class="lg_thumb" style="min-width:%spx">%s%s%s%s</div></td>',
        $div_width,
        $folderi->icon_div(),
        $folderi->caption_div(),
        $folderi->description_div(),
        apply_filters( 'lazyest_frontend_folder', '', $folderi )
      );
      $col_count++;
      if ( ( $col_count / $columns ) == intval( $col_count / $columns ) ) {
        $dir_table .= '</tr>';
        if ( $i+1 < $end ) { 
          $dir_table .='<tr>';
        }
      }            
      $i++;
      } // not hidden
    } //while
    
    if ( ( $col_count / $columns ) != intval( $col_count / $columns ) ) { // pad table with empty cells
      while ( ( $col_count / $columns ) != intval( $col_count / $columns )  ) {
        $dir_table .= '<td></td>';
        $col_count++;
      }
    $dir_table .= '</tr>';
    } 
    $dir_table .= '</tbody></table><br />';
    return $dir_table;
  }
  
  /**
   * LazyestFrontend::dir_view()
   * returns html of folder list
   * 
   * @since 1.1.0
   * @param array $folders
   * @param int $start
   * @param int $end
   * @return string html of folders listing
   */
  function dir_view(  $folders, $start, $end ) {
    if ( ( 0 == count( $folders ) ) ) 
      return '';
      
    $dir_view = '<div class="dir_view">';
    $dir_view .= sprintf( '<div class="folder">%s %s</div>', 
      $this->_cabinet(),
      $this->_above_folders() 
    );
    $dir_view .= '<ul class="lgf-list">';
    $i = $start - 1;
    while ( $i < $end ) {
      $folderi = $folders[$i];
      if ( 'hidden' != $folderi->visibility ) { // do not insert a list tiem when folder is hidden
        $dir_view .= sprintf( '<li class="lgf-item"><div class="lg_thumb">%s%s%s%s</div></li>',
        $folderi->icon_div(),
        $folderi->caption_div(),
        $folderi->description_div(),
        apply_filters( 'lazyest_frontend_folder', '', $folderi ) );                  
        $i++;
      }
    }
    $dir_view .= '</ul>';
    
    $dir_view .= '</div>';
    return $dir_view;
  }

  /**
   * LazyestFrontend::wp_title()
   * Returns the WordPress title for the gallery instead of the page title
   * @link http://brimosoft.nl/2010/12/27/lazyest-gallery-and-seo-plugins/
   * @param string $title The title as compiled by WordPress
   * @param string $sep How to separate the various items within the page title.
   * @param string $seplocation Direction to display title.
   * @return string
   */
  function wp_title( $title, $sep, $seplocation ) { 
    global $lg_pagei, $lg_paged;		 
    $prefix = " $sep ";
  	if ( $this->is_gallery() && $this->valid() && ( '' != $this->currentdir ) ) {
  		$tdirs = untrailingslashit( $this->currentdir );
  		$dirs = explode( '/', $tdirs );  		
  		$tfile = '';
  		$title_array = array();
  		
  		foreach( $dirs as $dir ) {
  			$tfile .= trailingslashit( $dir );
  			if ( is_dir( $this->root . $tfile ) ) {
				  $folder = new LazyestFrontendFolder( $tfile );
          if ( is_object( $folder ) ) {               
            $folder->open();
      			$title_array[] = $folder->title();                      
			    }                 
  			} 
  		} 			   		
  		if ( $this->is_image( $this->file ) ) {
  		  if ( is_object( $folder )) {  		   
    		  $folder->load( 'images' );
          $pathinfo = pathinfo( $this->file );
    			$image = $folder->single_image( $pathinfo['basename'] ); 
  		  }
  			if ( is_object( $image) ) $title_array[] = $image->title();
  		}
      
      if ( !empty($title) )        
  			if ( 'right' == $seplocation ) {
  		  $title_array = array_reverse( $title_array );
        $title = implode( " $sep ", $title_array ) . $prefix . $title;
		  } else {
		    $title = $title . $prefix . implode( " $sep ", $title_array );
		  }  				
  	}
  	
  	// append gallery paging also for galleries in posts
		$imagepage ='';	
		if ( isset( $lg_pagei ) ) {
			$page = intval( $lg_pagei );
			if ( $page > 1 ) { 				
				$imagepage = sprintf( __( '%s page %d', 'lazyest-gallery' ), ucfirst( $this->get_option( 'listed_as' ) ), $page ) ;  			
				$title_array[] = $page;
			}
		}		
		$folderpage = '';
		if ( isset( $lg_paged ) ) {
			$page = intval( $lg_paged );
			if ( $page > 0 ) {				
				$folderpage = sprintf( __( 'Index %d', 'lazyest-gallery' ), $page ) ;
			}
		}
		
		if ( '' != $imagepage ) 
			$title = $imagepage . $prefix . $title;
		if ( '' != $folderpage ) 
			$title = $folderpage . $prefix . $title;
							
  	return $title;
  }
	
	/**
	 * LazyestFrontend::rel_canonical()
	 * Echoes the canonical link in the page header
	 * 
	 * @link http://brimosoft.nl/2011/09/05/canonical-urls-revisited/
	 * @since 1.1
	 * @return void
	 */
	function rel_canonical() {
	  global $wp_the_query, $lg_pagei, $lg_paged;
		// this is copied from original wordpress code   
		if ( !is_singular() )
			return;
		if ( !$id = $wp_the_query->get_queried_object_id() )
			return;
		$link = get_permalink( $id );
  	// check if this is the actual gallery page
  	if ( $this->is_gallery() ) {
  		// validate and clean up the gallery query 
	  	$this->valid();
	  	// the base link is our gallery page link as given in lazyest gallery settings
	  	$link = trailingslashit( $this->get_option( 'gallery_prev' ) );
	  	// check if we are displaying a folder or image
			if ( isset( $this->file ) ) {
				// compile canonical link 
				if ( 'TRUE' != $this->get_option('use_permalinks') ) {			
					$link = add_query_arg( 'file', $this->file, $link );	
				} else {
					$link .= $this->file;
				}			
				$link = trailingslashit( $link );		
			}
		} 
		
		// pages should also be indexed
		if ( !isset( $lg_paged ) )
			$lg_paged = ( isset( $_REQUEST['lg_paged'] ) ) ? intval( $_REQUEST['lg_paged'] ) : 0;
		else 
			$lg_paged = intval( $lg_paged );	
		if ( !isset( $lg_pagei ) )
			$lg_pagei = ( isset( $_REQUEST['lg_pagei'] ) ) ? intval( $_REQUEST['lg_pagei'] ) : 0;
		else 
			$lg_pagei = intval( $lg_pagei );				 
		
		if ( $lg_pagei > 1 )
			$link = add_query_arg( 'lg_pagei', $lg_pagei, $link );
		if ( $lg_paged > 1 )
		  $link = add_query_arg( 'lg_paged', $lg_paged, $link );
		  
		echo "<link rel='canonical' href='$link' />\n";
	} 
  
} // LazyestFrontend;


/**
 * LazyestFrontendFolder
 * Holds all folder functions for Frontend
 * 
 * @package Lazyest Gallery
 * @subpackage Frontend
 * @author Marcel Brinkkemper
 * @copyright 2010-2011 Brimosoft
 * @since 0.16.0
 * @access public
 */
class LazyestFrontendFolder extends LazyestFolder {
  
  /**
   * LazyestFrontendFolder::load()
   * Loads images without the folder icon image
   * 
   * @param string $what
   * @see LazyestFolder::load()
   * @return void
   */
  function load( $what = 'images' ) {
    global $lg_gallery;
    LazyestFolder::load( $what );    
    if (  ( 'icon' == $lg_gallery->get_option( 'folder_image' ) ) && ( 0 < count( $this->list ) ) ) {
      foreach( $this->list as $key_i=>$image  ) {
        if ( $this->is_folder_icon( $image->image ) ) {          
          unset( $this->list[$key_i] );
          $this->list = array_values( $this->list ); 
        }
      }
    } 
  }
  
  /**
   * LazyestFrontendFolder::do_slideshow()
   * 
   * @since 1.1.0
	 * @param string $display What to display in the slide show. Can be either 'slide', 'thumb', or 'image'
   * @param LazyestFrontendFolder $folder
   * @return string html for slideshow
   */
  function do_slideshow( $display='slide' ) {
  	global $lg_gallery;
  	  	
  	$slideshow = '<div class="lg_loading">Loading...</div>';
    $i= 0;     
    while ( $i < count( $this->list ) ) {        
      $slide = $this->list[$i];     
      $img_link = $slide->on_click();
     	$rel = ( '' != $img_link['rel'] ) ? ' rel="' . $img_link['rel'] . '"' : '';   	
      $img_src = $slide->src();        
      $acaption = '';
      $adescription = '';      
      if ( '' != $slide->caption ) {
        $acaption = '<h3>' . htmlspecialchars( strip_tags( $slide->caption), ENT_QUOTES ) . '</h3>'; 
      } 
      if ( '' != $slide->description ) {
        $adescription = '<p>' .  htmlspecialchars( strip_tags( $slide->description ), ENT_QUOTES )  . '</p>';
      }   
      $slideshow .= '<a id="' . $img_link['id'] . '_' . $lg_gallery->nr_shows . '" href="' . $img_link['href'] . '" class="' . $img_link['class'] . '"' . $rel . '>';
      $slideshow .=  '<img src="' . $img_src . '" title="' . $slide->title(). '" alt="' . $slide->alt(). '" rel="'.  $acaption . $adescription . '" /></a>';
      $i++;
    }  		
    $style = ( 'thumb' == $display ) ? ' style = "height:0px"' : '';
    $slideshow .=  '<div class="sscaption"' . $style . '></div>';
    
    return $slideshow;
  }
  
   
  /**
   * LazyestFrontendFolder::slideshow()
   * 
   * @param string $display. What to display in the slide show. Can be either 'slide', 'thumb', or 'image'
   * @return void
   */
  function slideshow( $display='slide' ) {		
  	global $lg_gallery;
  	
  	if ( ! $lg_gallery->access_check( $this ) ) 
  		return;
  
    if ( '' == $lg_gallery->get_option( 'enable_slide_show' ) ) return;
    
    
  	if ( ( 'image' == $display ) && ( $lg_gallery->get_option( 'disable_full_size' ) == "TRUE" ) ) {
      $display = 'slide';
  	}
  	 				
		$this->load( $display . 's' );
		
		if ( 0 == count( $this->list ) )
			return;
		if ( 1 == count( $this->list ) ) {
			$image = $this->list[0];
			$this->show_slide( $image->image );
			return;	
		}			
				
		$show_class = ( $display == 'thumb' )? 'lg_sideshow' : ''; 
		?>
		<div class="lg_slideshow <?php echo $show_class; ?>" id="lg_slideshow_<?php $lg_gallery->nr_shows++; echo $lg_gallery->nr_shows; ?>" >
			<?php echo apply_filters( 'lazyest_slideshow', $this->do_slideshow( $display ), $this  ); ?>	
    </div>
    <?php      
  }
  
  /**
   * LazyestFrontendFolder::thumb_image()
   * 
   * @since 1.1.0
   * @param LazyestThumb $image
   * @return string code for thumbnail image
   */
  function thumb_image( $image ) {  	
    global $post, $lg_gallery; 
    $onclick = $image->on_click();	
		$rel = ( '' != $onclick['rel'] ) ? 'rel="' . $onclick['rel'] . '"' : '';   	
    $class= 'thumb';
    if ( 'TRUE' != $lg_gallery->get_option( 'enable_cache' )  || 
			( ( 'TRUE' == $lg_gallery->get_option( 'async_cache' ) ) 
				&& ! file_exists( $image->loc() ) ) ) {
			$class .= ' lg_ajax';	
		}	
		$postid = is_object ( $post ) ? $post->ID : $lg_gallery->get_option( 'gallery_id' ); 
    return sprintf( '<div class="lg_thumb_image"><a id="%s_%s" href="%s" class="%s" %s title="%s" ><img class="%s" src="%s" alt="%s" /></a></div>',          
      $onclick['id'],
      $postid,
      $onclick['href'],
      $onclick['class'],
			$rel,
      $onclick['title'],
      $class,
      $image->src(),
      $image->alt()  
    );    
  }
  
  /**
   * LazyestFrontendFolder::thumb_caption()
   * 
   * @uses apply_filters
   * @since 1.1.0
   * @param LazyestThumb $image
   * @return string html code of caption
   */
  function thumb_caption( $image ) {
    global $lg_gallery;    				  
    $thumb_caption = '<div class="lg_thumb_caption">';
    $caption = $image->caption(); 
		$max_length = (int) $lg_gallery->get_option( 'captions_length' );
		if ( '0' != $lg_gallery->get_option( 'captions_length' ) )  {
			if ( strlen( $caption ) > $max_length ) {
			  strip_tags( $caption );
				$caption = substr( $caption, 0, $max_length - 1 ) . '&hellip;';
			}	
		}
    $thumb_caption .= sprintf( '<span title="%s" >%s</span>',
      $image->title(),
      lg_html( $caption ) 
    );  		
    $thumb_caption .= '</div>';
	  
    if ( ( 'TRUE' == $lg_gallery->get_option( 'thumb_description' ) ) ) {
    	if ( '' != $image->description )
	      $thumb_caption .= sprintf( '<div class="thumb_description"><p>%s</p></div>',
	        lg_html( $image->description() )
	      );
      $thumb_caption .= apply_filters( 'lazyest_thumb_description', '', $image );
    }
    return $thumb_caption;  
  }
  
  /**
   * LazyestFrontendFolder::folder_header()
   * the header above the thumbnails
   * 
   * @since 1.1.0
   * @uses apply_filters()
   * @return string html code of header
   */
  function folder_header() {
    $thumbs_folder_header =  sprintf( '<div class="folder_caption"><h3>%s</h3></div>%s',
      apply_filters( 'lazyest_folder_caption', lg_html( $this->caption() ), $this ),
      ( '' != $this->description ) ? sprintf( '<div class="folder_description"><p>%s</p></div>', lg_html( $this->description() ) ) : ''
    );
    return apply_filters( 'lazyest_thumbs_folder_header', $thumbs_folder_header, $this );
  }
  
  /**
   * LazyestFrontendFolder::thumbs_view()
   * returns html of folder thumbnails list
   * 
   * @since 1.1.0
   * @param integer $start
   * @param integer $end
   * @return string html code of the thumbnails list
   */
  function thumbs_view( $start, $end ) {
    global $lg_gallery;
			
    $do_caption = ( '-1' != $lg_gallery->get_option( 'captions_length' ) );
    
    $folder_class = sanitize_title( $this->curdir );
    
    $thumbs_view = "<div class='lazyest_thumb_view $folder_class'>\n";
    
    if ( 0 < count( $this->list ) ) {
      $thumbs_view .= "\n<ul class='lgi-list'>\n";	
  		for ( $i = $start - 1; $i < $end; $i++ ) { // main cycle        
        $image = $this->list[$i];
        $thumbs_view .= '<li class="lgi-item"><div class="lg_thumb">';      
        $thumbs_view .= $this->thumb_image( $image );          
    		if ( $do_caption )				  
          $thumbs_view .= $this->thumb_caption( $image );        			
        $thumbs_view .= apply_filters( 'lazyest_frontend_thumbnail', '', $image );
        $thumbs_view .= "</div></li>\n";
      }                      
      $thumbs_view .= '</ul>';
    }
    $thumbs_view .= "</div>\n";    
    return $thumbs_view;
  }
  
  /**
   * LazyestFrontendFolder::thumbs_table()
   * returns html of folder thumbnails table
   * 
   * @since 1.1.0
   * @deprecated after 1.1.0, this function will no longer be updated
   * @param integer $start
   * @param integer $end
   * @param integer $columns
   * @return string html code of the thumbnails table
   */
  function thumbs_table( $start, $end, $columns=1 ) {    	
    global $lg_gallery;
    
    $columns = ( 0 == $columns ) ? 1 : $columns;    
		$col_count = 0;    
		$do_caption = ( '-1' != $lg_gallery->get_option( 'captions_length' ) );
    
    $thumbs_table = "<table class='lazyest_thumb_view'>\n<tbody>\n";
    if ( 0 < count( $this->list ) ) {	
			for ( $i = $start - 1; $i < $end; $i++ ) { // main cycle        
        $image = $this->list[$i];
        
        $thumbs_table .= '<td><div class="lg_thumb">';        
        $thumbs_table .= $this->thumb_image( $image );          
				if ( $do_caption )				  
          $thumbs_table .= $this->thumb_caption( $image );   
        			
        $thumbs_table .= apply_filters( 'lazyest_frontend_thumbnail', '', $image );                           
        $thumbs_table .= '</div></td>';
        
				$col_count++;
        if ( ( $col_count / $columns ) == intval( $col_count / $columns ) ) {
          $thumbs_table .= '</tr>';
          if ( $i+1 < $end ) {
            $thumbs_table .= "\n<tr>";
          }
        }				
		  } // main cycle
    }	
    if ( ( $col_count / $columns ) != intval( $col_count / $columns )  ) {   
      while ( ( $col_count / $columns ) != intval( $col_count / $columns )  ) {
        $thumbs_table .= '<td></td>';
        $col_count++;
      }
      $thumbs_table .= "</tr>\n";
    }
    $thumbs_table .= "</tbody>\n</table>\n";
    return $thumbs_table;
  }
  
  /**
   * LazyestFrontendFolder::empty_link()
   * echo a link to an image
   * 
   * @since 1.1.0
   * @uses esc_attr() 
   * @param LazyestThumb $image
   * @return void
   */
  function empty_link( $image ) { 
  	$onclick = $image->on_click();  
  	$rel = ( '' != $onclick['rel'] ) ? 'rel="' . $onclick['rel'] . '"' : '';   	
    printf ( '<a id="%s" href="%s" class="%s" %s title="%s"></a>',
      $onclick['id'],
      $onclick['href'],
      $onclick['class'],
      $rel,
      esc_attr( $image->title() )
    );       
  } 
  
  /**
   * LazyestFrontendFolder::show_thumbs()
   * Display thumnails of images in the folder
   * 
   * @uses user_logged_in()
   * @uses current_user_can()
   * @param integer $perpage = number of thumbnails to show
   * @param integer $columns = number of columns in the image table
   * @param string $paging = show images over more pages if number of images is larger than $perpage
   * @return void
   */
  function show_thumbs( $perpage = 0, $columns = 1, $paging = 'true' ) {		
    global $lg_gallery, $lg_pagei, $post;
    if ( ! $lg_gallery->access_check( $this ) ) 
			return;
			        
    $foldervalue = urlencode( $this->curdir );
        
    $thumbs_page = $lg_gallery->get_option( 'thumbs_page' );
		if ( $perpage !=  $thumbs_page ) {
			$lg_gallery->change_option( 'thumbs_page', $perpage );
 		}
 		
    if ( ! isset( $this->list )  ) {
      $this->load( 'thumbs' );
    }
    $start = 1;      
    $end = count( $this->list );
		?> 
		<div class="thumb_images">
		<?php  
    if ( 0 < $perpage) {    
      $total_pages = ceil( count( $this->list ) / $perpage ); 
      $query_var = 'lg_pagei';
      if ( isset ( $lg_pagei ) ) {
        $current = max( 1, $lg_pagei);
      } else {      
        $current = isset( $_REQUEST[$query_var] ) ? absint( $_REQUEST[$query_var] ) : 0;	
        $current = min( max( 1, $current ), $total_pages );
      }
      $start = ( $current - 1 ) * $perpage + 1;
      $end = min( count( $this->list ), $current * $perpage);
      if ( ( $paging == true ) && ( $perpage < count( $this->list ) ) && ( $perpage != 0 ) ) {
      	$ajax_nonce = wp_create_nonce( 'show_thumbs' );
        printf( '<form name="thumbs_page" action="%s" method="post">', $this->uri() );                 
        printf( '<input type="hidden" name="current" value="%s" />', $current );
        printf( '<input type="hidden" name="last_page" value="%s" />', ceil( count( $this->list ) / $perpage ) );
        printf( '<input type="hidden" name="folder" value="%s" />', $foldervalue ); 
        printf( '<input type="hidden" name="virtual" value="%s" />', urlencode( $lg_gallery->virtual_root ) ); 
        printf( '<input type="hidden" name="perpage" value="%s" />', $perpage );
        printf( '<input type="hidden" name="columns" value="%s" />', $columns );      
				printf( '<input type="hidden" name="post_id" value="%s" />', $post->ID );
				printf( '<input type="hidden" name="ajax_nonce" value="%s" />', $ajax_nonce );  
        printf( '<input type="hidden" name="request_uri" value="%s" />', remove_query_arg( 'lg_pagei', $_SERVER['REQUEST_URI'] ) );
      }    
    }		  		
		if( 0 < count( $this->list ) ) {		
			$i = 1;   
			if ( in_array( $lg_gallery->get_option( 'on_thumb_click' ), array( 'lightslide', 'thickslide', 'lightbox', 'thickbox' ) ) ) {
	      echo '<div style="display:none">';  
				while ( $i < $start ) { // pre cycle of anchor links for slideshow plugins
	        $image = $this->list[$i-1];    lg_db($i-1,'i');		    
	        $this->empty_link( $image );
					$i++;
			
				}
	      echo "</div>\n";
			}
      echo "<div class='lg_thumb_view'>\n";            
      // this is where we actually echo the thumbnail images
      echo ( 'TRUE' == $lg_gallery->get_option( 'table_layout') ) ? $this->thumbs_table( $start, $end, $columns ) : $this->thumbs_view( $start, $end );      
			echo "</div>\n";
			
			$i = $end;    
			if ( in_array( $lg_gallery->get_option( 'on_thumb_click' ), array( 'lightslide', 'thickslide', 'lightbox', 'thickbox' ) ) ) {   
	      echo '<div style="display:none">';  
	      while ($i < count( $this->list ) ) { // post cycle of anchor links for slideshow plugins
	        $image = $this->list[$i];        
	        $this->empty_link( $image );
					$i++;
				}			
	    	echo "</div>\n";
    	}
		} // count images
		if ( 1 < count( $this->list ) ) {	 
	    ?>  
			<div class="buttons">
	    <?php
	    if ( ( 'TRUE' == $lg_gallery->get_option( 'enable_slide_show' ) )  && $lg_gallery->is_gallery() ) {
					?>
			    <a href="<?php echo add_query_arg( 'lg_show', 'true', $this->uri() ); ?>" class="lg_slideshow_button"><?php echo __( 'Slide Show', 'lazyest-gallery' ); ?></a>
			    <?php      
				}	
			?>	
			</div>       
	    <div class="image_pagination"> <?php      
			if ( ( $paging == true ) && ( $perpage < count( $this->list ) ) && ( $perpage != 0 ) ) { 
 	                    				
	    	echo $lg_gallery->pagination( 'images', $this->list );
	    } 
			?>  	   			 
				<br style="clear:both;" />	   			
	    </div>
	    <?php
	    if ( ( $paging == true ) && ( $perpage < count( $this->list ) ) && ( $perpage != 0 ) )
	    	echo "</form>\n";
    }
    ?>    
    </div>
    <?php		
    $lg_gallery->change_option( 'thumbs_page', $thumbs_page );    
  }
  
  /**
   * LazyestFrontendFolder::show_slide()
   * Show a single image in slide view
   * 
   * @param string $filename file name of the image to show 
   * @return void
   */
  function show_slide( $filename ) {
    global $lg_gallery, $post; 
    
  	if ( ! $lg_gallery->access_check( $this ) ) 
  		return;

    $this->load('slides'); 		          
		for ( $i = 0; $i < count( $this->list ); $i++ ) {
		  $image = $this->list[$i];        
			if( $image->image == $filename ) {          
				if( 0 == $i ) {  				  
					$previous = end( $this->list );
				} else {
					$previous = $this->list[$i-1];
				}  			 
				if( ( $i + 1 ) == count( $this->list ) ) {
					$next = $this->list[0];
				} else {
					$next = $this->list[$i+1];
				}
				break;
			}
		};
    if ( ! isset($previous) || ! isset($next) ) { 
      esc_html_e( 'Something went wrong displaying the slide', 'lazyest-gallery' );
      return;
    }
     	 
    ?>    		
    <div class="lazyest_image">
    <?php
		if ( in_array( $lg_gallery->get_option( 'on_slide_click' ), array( 'lightbox', 'thickbox' ) ) ) { // add links for lightbox
			$j = 0;
			while ( $j < $i ) {
        $dummy = $this->list[$j];
        $onclick = $dummy->on_click();
       	$rel = ( '' != $onclick['rel'] ) ? 'rel="' . $onclick['rel'] . '"' : '';   	
      ?>
        <a id="<?php echo $onclick['id']; ?>" class="lg_dummy <?php echo $onclick['class'] ?>" title="<?php echo $onclick['title']; ?>"  href="<?php echo $onclick['href']; ?>" <?php echo $rel; ?>></a>
      <?php          
				$j++;
			}
		}
		
    $onclick = $image->on_click();
   	$rel = ( '' != $onclick['rel'] ) ? 'rel="' . $onclick['rel'] . '"' : '';   	
    ?>
      <a id="<?php echo $onclick['id'] . $post->ID ?>" class="slide <?php echo $onclick['class']; ?>" href="<?php echo $onclick['href'] ?>" title="<?php echo $onclick['title']; ?>" <?php echo $rel; ?>>
        <img class="slide" id="<?php echo $image->html_id(); ?>" src="<?php echo $image->src(); ?>" alt="<?php echo $image->alt(); ?>" />
      </a>         
    <?php
		
		if ( in_array( $lg_gallery->get_option( 'on_slide_click' ), array( 'lightbox', 'thickbox' ) ) ) { // add links for lightbox
			$j = $i + 1;
			while ( $j < count( $this->list ) ) {
        $dummy = $this->list[$j];
        $onclick = $dummy->on_click();
       	$rel = ( '' != $onclick['rel'] ) ? 'rel="' . $onclick['rel'] . '"' : '';   	
      ?>
        <a id="<?php echo $onclick['id']; ?>" class="lg_dummy <?php echo $onclick['class'] ?>" title="<?php echo $onclick['title']; ?>"  href="<?php echo $onclick['href']; ?>" <?php echo $rel; ?>></a>
      <?php          
				$j++;
			}
		}  	      	
		?>  
		  <div class="caption"><?php echo lg_html( $image->caption() ); ?>&nbsp;</div>
    <?php  
    if ( '' != $image->description ) {       
    ?>  
      <div class="description"><?php echo lg_html( $image->description() ) ; ?>&nbsp;</div>
    <?php          
    }   
    do_action('lazyest_frontend_slide', $image );
    ?> </div> <!-- lazyest image --> <?php
    if ( 1 < count( $this->list ) ){
  		?>  		
      <div class="lazyest_navigator" style="width:95%">
      <?php if ( ( 'TRUE' == $lg_gallery->get_option( 'enable_slide_show' ) )  && $lg_gallery->is_gallery() ) : ?>
      <a href="<?php echo add_query_arg( 'lg_show', 'true', $this->uri() ); ?>" class="lg_slideshow_button"><?php echo __( 'Slide Show', 'lazyest-gallery' ); ?></a>
		  <?php
		  endif;
      $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page',
			esc_attr__( 'Go to the previous slide', 'lazyest-gallery'  ),
			esc_url( $previous->uri() ),
			'&laquo;'
		  );      
  		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
  			'next-page',
  			esc_attr__( 'Go to the next slide', 'lazyest-gallery'  ),
  			esc_url( $next->uri() ),
  			'&raquo;'
  		);
      $output = join( "\n", $page_links );

      echo "<div class='tablenav-pages'>$output</div>"; 
      ?>
        <br style="clear: both;"/>
  		</div> 	      
      <?php
		}
		if( 'TRUE' == $lg_gallery->get_option( 'enable_exif' ) ) {
		  $this->show_exif( $filename );
		}  		
  }
  
    
  /**
   * LazyestFrontendFolder::_photo_getval()
   * Used by LazyestFrontendFolder::show_exif()
   * Get a value from the exif value array
   * 
   * @param string $image_info
   * @param array $val_array
   * @return string
   */
  function _photo_getval( $image_info, $val_array ) {
    $info_val = 'Unknown';
    foreach( $val_array as $name=>$val ) {
      if ( $name == $image_info ) {
        $info_val = &$val;
        break;
      }
    }
    return $info_val;
  }
    
  /**
   * LazyestFrontendFolder::show_exif()
   * Show exif code for a jpeg image
   * 
   * @param string $filename
   * @return void
   */
  function show_exif( $filename ) {
    global $lg_gallery;
    $image = $this->single_image( $filename );    
    $pathinfo = pathinfo( $filename );
        
    $imgtype = array( '', 'GIF', 'JPG', 'PNG', 'SWF', 'PSD', 'BMP', 'TIFF(intel byte order)', 'TIFF(motorola byte order)', 'JPC', 'JP2', 'JPX', 'JB2', 'SWC', 'IFF', 'WBMP', 'XBM');

    $orientation = array('', 'top left side', 'top right side', 'bottom right side', 'bottom left side', 'left side top', 'right side top', 'right side bottom', 'left side bottom');

    $resolution_unit = array('', '', 'inches', 'centimeters');

    $ycbcr_positioning = array('', 'the center of pixel array', 'the datum point');
    
    $exposure_program = array(
      __('Not defined', 'lazyest-gallery' ),
      __( 'Manual', 'lazyest-gallery' ), 
      __( 'Normal program', 'lazyest-gallery' ), 
      __( 'Aperture priority', 'lazyest-gallery' ), 
      __( 'Shutter priority', 'lazyest-gallery' ), 
      __( 'Creative program (biased toward depth of field)', 'lazyest-gallery' ), 
      __( 'Action program (biased toward fast shutter speed)', 'lazyest-gallery' ), 
      __( 'Portrait mode (for closeup photos with the background out of focus)', 'lazyest-gallery' ), 
      __( 'Landscape mode (for landscape photos with the background in focus)', 'lazyest-gallery' )
    );

    
    $metering_mode = array(
      '0' => __( 'Unknown', 'lazyest-gallery' ),
      '1' => __( 'Average', 'lazyest-gallery' ),
      '2' => __( 'Center Weighted Average', 'lazyest-gallery' ),
      '3' => __( 'Spot', 'lazyest-gallery' ),
      '4' => __( 'MultiSpot', 'lazyest-gallery' ),
      '5' => __( 'Pattern', 'lazyest-gallery' ),
      '6' => __( 'Partial', 'lazyest-gallery' ),
      '255' =>__( 'Other Metering Mode', 'lazyest-gallery' )
    );
    
    $light_source = array(
      '0' => __( 'unknown', 'lazyest-gallery' ),
      '1' => __( 'Daylight', 'lazyest-gallery' ),
      '2' => __( 'Fluorescent', 'lazyest-gallery' ),
      '3' => __( 'Tungsten (incandescent light)', 'lazyest-gallery' ),
      '4' => __( 'Flash', 'lazyest-gallery' ),
      '9' => __( 'Fine weather', 'lazyest-gallery' ),
      '10' => __( 'Cloudy weather', 'lazyest-gallery' ),
      '12' => __( 'Daylight fluorescent (D 5700 – 7100K)', 'lazyest-gallery' ),
      '13' => __( 'Day white fluorescent (N 4600 – 5400K)', 'lazyest-gallery' ),
      '14' => __( 'Cool white fluorescent (W 3900 – 4500K)', 'lazyest-gallery' ),
      '15' => __( 'White fluorescent (WW 3200 – 3700K)', 'lazyest-gallery' ),
      '17' => __( 'Standard light A', 'lazyest-gallery' ),
      '18' => __( 'Standard light B', 'lazyest-gallery' ),
      '19' => __( 'Standard light C', 'lazyest-gallery' ),
      '20' => __( 'D55', 'lazyest-gallery' ),
      '21' => __( 'D65', 'lazyest-gallery' ),
      '22' => __( 'D75', 'lazyest-gallery' ),
      '23' => __( 'D50', 'lazyest-gallery' ),
      '24' => __( 'ISO studio tungsten', 'lazyest-gallery' ),
      '255' => __( 'other light source', 'lazyest-gallery' )
    );
    
    $flash = array(
      '0' => __( 'Flash did not fire.', 'lazyest-gallery' ),
      '1' => __( 'Flash fired.', 'lazyest-gallery' ),
      '5' => __( 'Strobe return light not detected.', 'lazyest-gallery' ),
      '7' => __( 'Strobe return light detected.', 'lazyest-gallery' ),
      '9' => __( 'Flash fired, compulsory flash mode', 'lazyest-gallery' ),
      '13' => __( 'Flash fired, compulsory flash mode, return light not detected', 'lazyest-gallery' ),
      '15' => __( 'Flash fired, compulsory flash mode, return light detected', 'lazyest-gallery' ),
      '16' => __( 'Flash did not fire, compulsory flash mode', 'lazyest-gallery' ),
      '24' => __( 'Flash did not fire, auto mode', 'lazyest-gallery' ),
      '25' => __( 'Flash fired, auto mode', 'lazyest-gallery' ),
      '29' => __( 'Flash fired, auto mode, return light not detected', 'lazyest-gallery' ),
      '31' => __( 'Flash fired, auto mode, return light detected', 'lazyest-gallery' ),
      '32' => __( 'No flash function', 'lazyest-gallery' ),
      '65' => __( 'Flash fired, red-eye reduction mode', 'lazyest-gallery' ),
      '69' => __( 'Flash fired, red-eye reduction mode, return light not detected', 'lazyest-gallery' ),
      '71' => __( 'Flash fired, red-eye reduction mode, return light detected', 'lazyest-gallery' ),
      '73' => __( 'Flash fired, compulsory flash mode, red-eye reduction mode', 'lazyest-gallery' ),
      '77' => __( 'Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected', 'lazyest-gallery' ),
      '79' => __( 'Flash fired, compulsory flash mode, red-eye reduction mode, return light detected', 'lazyest-gallery' ),
      '89' => __( 'Flash fired, auto mode, red-eye reduction mode', 'lazyest-gallery' ),
      '93' => __( 'Flash fired, auto mode, return light not detected, red-eye reduction mode', 'lazyest-gallery' ),
      '95' => __( 'Flash fired, auto mode, return light detected, red-eye reduction mode', 'lazyest-gallery' )
    );
    
    $exif = @exif_read_data( $lg_gallery->root . $this->curdir . $image->image, 0, true );  
    $img_info = array ();
    if ( isset( $exif['FILE']['FileName'] ) ) 
      $img_info[__('FileName', 'lazyest-gallery')] = $exif['FILE']['FileName'];  
    if ( isset( $exif['FILE']['FileType'] ) )   
      $img_info[__('FileType', 'lazyest-gallery')] =  $imgtype[$exif['FILE']['FileType']];
    if ( isset( $exif['FILE']['MimeType'] ) ) 
      $img_info[__('MimeType', 'lazyest-gallery')] =  $exif['FILE']['MimeType']; 
    if ( isset( $exif['FILE']['FileSize'] ) ) 
      $img_info[__('FileSize', 'lazyest-gallery')] = ( floor( $exif['FILE']['FileSize'] / 1024 * 10 ) /10 ) . 'KB';
    if ( isset( $exif['FILE']['FileDateTime'] ) )       
      $img_info[__('FileDateTime', 'lazyest-gallery')] = date( 'Y-m-d  H:i:s', $exif['FILE']['FileDateTime'] );
    if ( isset( $exif['IFD0']['Artist'] ) ) 
      $img_info[__('Artist', 'lazyest-gallery')] = $exif['IFD0']['Artist']; 
    if ( isset( $exif['IFD0']['Make'] ) )
      $img_info[__('Make', 'lazyest-gallery')] = $exif['IFD0']['Make']; 
    if ( isset( $exif['IFD0']['Model'] ) )
      $img_info[__('Model', 'lazyest-gallery')] = $exif['IFD0']['Model']; 
    if ( isset( $exif['IFD0']['DateTime'] ) ) 
      $img_info[__('DateTime', 'lazyest-gallery')] = $exif['IFD0']['DateTime']; 
    if ( isset( $exif['EXIF']['ExifVersion'] ) ) 
      $img_info[__('ExifVersion', 'lazyest-gallery')] = $exif['EXIF']['ExifVersion']; 
    if ( isset( $exif['EXIF']['DateTimeOriginal'] ) ) 
      $img_info[__('DateTimeOriginal', 'lazyest-gallery')] = $exif['EXIF']['DateTimeOriginal']; 
    if ( isset( $exif['EXIF']['DateTimeDigitized'] ) ) 
      $img_info[__('DateTimeDigitized', 'lazyest-gallery')] = $exif['EXIF']['DateTimeDigitized']; 
    if ( isset( $exif['COMPUTED']['Height'] ) ) 
      $img_info[__('Height', 'lazyest-gallery')] = $exif['COMPUTED']['Height'] . 'px'; 
    if ( isset( $exif['COMPUTED']['Width'] ) ) 
      $img_info[__('Width', 'lazyest-gallery')] = $exif['COMPUTED']['Width'] . 'px'; 
    if ( isset( $exif['EXIF']['CompressedBitsPerPixel'] ) ) 
      $img_info[__('CompressedBitsPerPixel', 'lazyest-gallery')] = $exif['EXIF']['CompressedBitsPerPixel'] .  __( ' Bits/Pixel', 'lazyest-gallery' );
    $img_info[__('FocusDistance', 'lazyest-gallery')] = isset( $exif['COMPUTED']['FocusDistance'] ) ? $exif['COMPUTED']['FocusDistance'] . 'm' : NULL;
    $img_info[__('FocalLength', 'lazyest-gallery')] = isset( $exif['EXIF']['FocalLength'] ) ? $exif['EXIF']['FocalLength'] . 'mm' : NULL; 
    $img_info[__('FocalLengthIn35mmFilm', 'lazyest-gallery')] = isset( $exif['EXIF']['FocalLengthIn35mmFilm'] ) ? $exif['EXIF']['FocalLengthIn35mmFilm'] . 'mm' : NULL; 
    if ( isset( $exif['EXIF']['ColorSpace'] ) ) 
      $img_info[__('ColorSpace', 'lazyest-gallery')] = $exif['EXIF']['ColorSpace'] == 1 ? 'sRGB' :  __('Uncalibrated', 'lazyest-gallery' );
    if ( isset( $exif['IFD0']['ImageDescription'] ) ) 
      $img_info[__('ImageDescription', 'lazyest-gallery')] = $exif['IFD0']['ImageDescription']; 
    if ( isset( $exif['IFD0']['Orientation'] ) ) 
      $img_info[__('Orientation', 'lazyest-gallery')] = $orientation[$exif['IFD0']['Orientation']]; 
    if ( isset( $exif['IFD0']['XResolution'] ) ) 
      $img_info[__('XResolution', 'lazyest-gallery')] = $exif['IFD0']['XResolution'] . $resolution_unit[$exif['IFD0']['ResolutionUnit']]; 
    if ( isset( $exif['IFD0']['YResolution'] ) ) 
      $img_info[__('YResolution', 'lazyest-gallery')] = $exif['IFD0']['YResolution'] . $resolution_unit[$exif['IFD0']['ResolutionUnit']]; 
    if ( isset( $exif['IFD0']['Software'] ) ) 
      $img_info[__('Software', 'lazyest-gallery')] = utf8_encode( $exif['IFD0']['Software'] ); 
    if ( isset( $exif['IFD0']['YCbCrPositioning'] ) ) 
      $img_info[__('YCbCrPositioning', 'lazyest-gallery')] = $ycbcr_positioning[$exif['IFD0']['YCbCrPositioning']]; 
    if ( isset( $exif['IFD0']['Copyright'] ) ) 
      $img_info[__('Copyright', 'lazyest-gallery')] = $exif['IFD0']['Copyright'];  
    if ( isset( $exif['COMPUTED']['Copyright.Photographer'] ) )
      $img_info[__('Photographer', 'lazyest-gallery')] = $exif['COMPUTED']['Copyright.Photographer']; 
    if ( isset( $exif['COMPUTED']['Copyright.Editor'] ) ) 
      $img_info[__('Editor', 'lazyest-gallery')] = $exif['COMPUTED']['Copyright.Editor']; 
    if ( isset( $exif['EXIF']['ExifVersion'] ) ) 
      $img_info[__('ExifVersion', 'lazyest-gallery')] = $exif['EXIF']['ExifVersion']; 
    if ( isset( $exif['EXIF']['FlashPixVersion'] ) ) 
      $img_info[__('FlashPixVersion', 'lazyest-gallery')] = __('Ver', 'lazyest-gallery') . number_format( $exif['EXIF']['FlashPixVersion']/100, 2 );    
    if ( isset( $exif['EXIF']['ApertureValue'] ) ) 
      $img_info[__('ApertureValue', 'lazyest-gallery')] = $exif['EXIF']['ApertureValue']; 
    if ( isset( $exif['EXIF']['ShutterSpeedValue'] ) ) 
      $img_info[__('ShutterSpeedValue', 'lazyest-gallery')] = $exif['EXIF']['ShutterSpeedValue']; 
    if ( isset( $exif['COMPUTED']['ApertureFNumber'] ) ) 
      $img_info[__('ApertureFNumber', 'lazyest-gallery')] = $exif['COMPUTED']['ApertureFNumber']; 
    if ( isset( $exif['EXIF']['MaxApertureValue'] ) ) 
      $img_info[__('MaxApertureValue', 'lazyest-gallery')] = 'F' . $exif['EXIF']['MaxApertureValue']; 
    if ( isset( $exif['EXIF']['ExposureTime'] ) ) 
      $img_info[__('ExposureTime', 'lazyest-gallery')] = $exif['EXIF']['ExposureTime']; 
    if ( isset( $exif['EXIF']['FNumber'] ) ) 
      $img_info[__('F-Number', 'lazyest-gallery')] = $exif['EXIF']['FNumber']; 
    if ( isset( $exif['EXIF']['MeteringMode'] ) ) 
      $img_info[__('MeteringMode', 'lazyest-gallery')] = $this->_photo_getval( $exif['EXIF']['MeteringMode'], $metering_mode ); 
    if ( isset( $exif['EXIF']['LightSource'] ) ) 
      $img_info[__('LightSource', 'lazyest-gallery')] = $this->_photo_getval( $exif['EXIF']['LightSource'], $light_source ); 
    if ( isset( $exif['EXIF']['Flash'] ) ) 
      $img_info[__('Flash', 'lazyest-gallery')] = $this->_photo_getval( $exif['EXIF']['Flash'], $flash ); 
    if ( isset( $exif['EXIF']['ExposureMode'] ) ) 
      $img_info[__('ExposureMode', 'lazyest-gallery')] = $exif['EXIF']['ExposureMode'] == 1 ? __('Manual exposure', 'lazyest-gallery' ) : __('Auto exposure', 'lazyest-gallery' ); 
    if ( isset( $exif['EXIF']['WhiteBalance'] ) ) 
      $img_info[__('WhiteBalance', 'lazyest-gallery')] = $exif['EXIF']['WhiteBalance'] == 1 ?  __('Manual white balance', 'lazyest-gallery'  ) :  __('Auto white balance', 'lazyest-gallery'  ); 
    if ( isset( $exif['EXIF']['ExposureProgram'] ) ) 
      $img_info[__('ExposureProgram', 'lazyest-gallery')] = $exposure_program[$exif['EXIF']['ExposureProgram']]; 
    if ( isset( $exif['EXIF']['ExposureBiasValue'] ) ) 
      $img_info[__('ExposureBiasValue', 'lazyest-gallery')] = $exif['EXIF']['ExposureBiasValue'] . __('EV', 'lazyest-gallery'); 
    if ( isset( $exif['EXIF']['ISOSpeedRatings'] ) ) 
      $img_info[__('ISOSpeedRatings', 'lazyest-gallery')] = $exif['EXIF']['ISOSpeedRatings']; 
    if ( isset( $exif['EXIF']['ComponentsConfiguration'] ) ) 
      $img_info[__('ComponentsConfiguration', 'lazyest-gallery')] = bin2hex( $exif['EXIF']['ComponentsConfiguration'] ) == '01020300' ? 'YCbCr' : 'RGB';      
    if ( isset( $exif['COMPUTED']['UserCommentEncoding'] ) ) 
      $img_info[__('UserCommentEncoding', 'lazyest-gallery')] = $exif['COMPUTED']['UserCommentEncoding']; 
    if ( isset( $exif['COMPUTED']['UserComment'] ) ) 
      $img_info[__('UserComment', 'lazyest-gallery')] = $exif['COMPUTED']['UserComment'];      
    if ( isset( $exif['EXIF']['ExifImageLength'] ) ) 
      $img_info[__('ExifImageLength', 'lazyest-gallery')] = $exif['EXIF']['ExifImageLength']; 
    if ( isset( $exif['EXIF']['ExifImageWidth'] ) ) 
      $img_info[__('ExifImageWidth', 'lazyest-gallery')] = $exif['EXIF']['ExifImageWidth']; 
    if ( isset( $exif['EXIF']['FileSource'] ) ) 
      $img_info[__('FileSource', 'lazyest-gallery')] = bin2hex( $exif['EXIF']['FileSource'] ) == 0x03 ? 'DSC' : __('unknown', 'lazyest-gallery'  ) ; 
    if ( isset( $exif['EXIF']['SceneType'] ) ) 
      $img_info[__('SceneType', 'lazyest-gallery')] = bin2hex( $exif['EXIF']['SceneType'] ) == 0x01 ? __('A directly photographed image', 'lazyest-gallery'  ) :  __('unknown', 'lazyest-gallery'  ) ; 
    if ( isset( $exif['COMPUTED']['Thumbnail.FileType'] ) ) 
      $img_info[__('Thumbnail.FileType', 'lazyest-gallery')] = $exif['COMPUTED']['Thumbnail.FileType']; 
    if ( isset( $exif['COMPUTED']['Thumbnail.MimeType'] ) ) 
      $img_info[__('Thumbnail.MimeType', 'lazyest-gallery')] = $exif['COMPUTED']['Thumbnail.MimeType'];   
    ?>    
    <div class="imagedata">
      <p class="exifheader"><?php esc_html_e( 'Image data', 'lazyest-gallery' ); ?></p>
      <?php
      if ( $exif ) {        
      ?>
      <table class="imagedatatable">
        <tbody>
          <tr>
            <th scope="row"><?php esc_html_e( 'Date', 'lazyest-gallery' ); ?></th>
            <td><?php echo $img_info[__('FileDateTime', 'lazyest-gallery')]; ?></td>
          </tr>
          <tr>
            <th scope="row"><?php esc_html_e( 'Height' ); ?></th>
            <td><?php echo $img_info[__('Height', 'lazyest-gallery')]; ?></td>
          </tr>
          <tr>
            <th scope="row"><?php esc_html_e( 'Width' ); ?></th>            
            <td><?php echo $img_info[__('Width', 'lazyest-gallery')]; ?></td>
          </tr>
          <?php if ( isset( $img_info[__('Make', 'lazyest-gallery')] ) && isset( $img_info[__('Model', 'lazyest-gallery')]) ) { ?>
          <tr>
            <th scope="row"><?php esc_html_e( 'Camera' ); ?></th>            
            <td><?php echo $img_info[__('Make', 'lazyest-gallery')] . ' - ' . $img_info[__('Model', 'lazyest-gallery')]; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <script type="text/javascript">function showExif(){jQuery('#all_exif').show();}</script>
      <a href="javascript:showExif();"><?php esc_html_e( 'Show all Exif data', 'lazyest-gallery' ); ?></a>
      <table id="all_exif">
        <tbody>
        <?php 
        foreach( $img_info as $name => $val ) {
          if ( $val ) {
          ?>
          <tr>
            <th scope="row"><?php echo $name; ?></th>
            <td><?php echo $val; ?></td>
          </tr>
          <?php
          }
        }
        ?>
        </tbody>
      </table>   
    <?php
    } else {
      list($width, $height, $type, $attr) = getimagesize( $lg_gallery->root . $this->curdir . $image->image );
    ?> 
      <table class="imagedatatable">
        <tbody>
          <tr>
            <th scope="row"><?php esc_html_e( 'Date', 'lazyest-gallery' ); ?></th>
            <td><?php echo date( get_option('date_format' ), filemtime( $lg_gallery->root . $this->curdir . $image->image ) ); ?></td>
            <th scope="row"><?php esc_html_e( 'Height' ); ?></th>
            <td><?php echo $height . 'px'; ?></td>
            <th scope="row"><?php esc_html_e( 'Width' ); ?></th>            
            <td><?php echo $width . 'px'; ?></td>
          </tr>
        </tbody>
      </table>   
    <?php
    }
    ?>
  </div>
  <?php  
  }
  
} // LazyestFrontendFolder
 
?>