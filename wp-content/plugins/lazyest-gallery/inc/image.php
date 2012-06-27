<?php
/**
 * LazyestImage
 * This class holds all functions and variables to handle images
 * 
 * @package Lazyest Gallery
 * @author Marcel Brinkkemper
 * @copyright 2010-2012 Brimosoft
 * @version 1.0
 * @access public
 */
class LazyestImage {
  
  /**
   * The filename.ext
   * @var string
   */
  var $image; 
  
  /**
   * The caption
   * @var string
   */
  var $caption;
  
  /**
   * The description
   * @var string
   */
  var $description;
  
  /**
   * The -unique- id
   * Image identifier for comments
   * @var int
   */
  var $id;
  
  /**
   * The index number to sort manually
   * @var int
   */ 
  var $index;
  
  /**
   * The date/time stored with the image
   * the system dat at the time the image has been found in the gallery
   * @var int
   */
  var $datetime;
  
  /**
   * The folder object holding the image
   * @var LazyestFolder
   */
  var $folder;
  
  /**
   * Resized image resource
   * @var resource
   */
  var $resized;
  
  /**
   * @since 1.1.0
   * array to hold user defined fields
   */
  var $extra_fields = array(); 
    
  
 /**
   * LazyestImage::__construct()
   * Creates a LazyestImage object belonging to folder $parent
   * 
   * @param mixed $parent
   * @return void
   */
  function __construct( $parent ) {
    global $lg_gallery;
    $this->image = $this->caption = $this->description = $this->id = $this->index = '';
    $this->folder = $parent;
    $fields = $lg_gallery->get_fields( 'image' );
    if ( false !== $fields ) {
      foreach( $fields as $field ) {
        $this->extra_fields[$field['name']] = '';
      }
    }       
  }
  
  /**
   * LazyestImage::valid()
   * Checks if the image exists in the LazyestFolder directory
   * @return bool
   */
  function valid() {
    global $lg_gallery;
    return file_exists( $lg_gallery->root . $this->folder->curdir . $this->image );
  }
  
  
  /**
   * LazyestImage::src()
   * Returns the src attribute for the image element
   * @example <img src="<?php echo $image->src(); ?>" alt="" />
   * @return string
   */
  function src() {
    global $lg_gallery;
    if ( ( '' == $this->image ) || ! isset( $lg_gallery ) || ! $this->valid() ) {
      return false;
    }  
    return $lg_gallery->address . lg_nice_link( $this->folder->curdir . $this->image );
  }
  
  /**
   * LazyestImage::alt()
   * Returns the alt attribute for the image element
   * @example <img src="<?php echo $image->src(); ?>" alt="<?php echo $image->alt(); ?>" />
   * @return string
   */
  function alt() {
    return esc_attr__( 'image' ) . ' ' . sanitize_title( $this->image ); 
  }
  
  /**
   * LazyestImage::html_id()
   * Returns an id for use in a html element
   * @example <img id="<?php echo $image->html_id()); ?>" src="<?php echo $image->src(); ?>" alt="" />
   * @return string
   */
  function html_id() {
    return $this->form_name();
  }
  
  /**
   * LazyestImage::uri()
   * The address for the image in Lazyest Gallery
   * This can be a slide view page
   * @param string $widget
   * @return string
   */
  function uri( $widget = 'none' ) {
    global $lg_gallery;
    if ( ( '' == $this->image ) || ! isset( $lg_gallery ) ) {
      return false;
    }
    return  $this->folder->uri( $widget ) . lg_nice_link( $this->image );     
  }
  
  /**
   * LazyestImage::loc()
   * location of the image in the file system
   * @return string 
   */
  function loc() {
    global $lg_gallery;
    if ( ( '' == $this->image ) || ! isset( $lg_gallery ) ) {
      return false;
    }
    $loc = $lg_gallery->root . $this->folder->curdir . $this->image;
    return ( file_exists( $loc ) ) ? $loc : false;
  }
  
  /**
   * LazyestImage::original()
   * location of the original uploaded image
   * @since 1.1.0
   * @return string
   */
  function original(){
    global $lg_gallery;
    if ( ( '' == $this->image ) || ! isset( $lg_gallery ) ) {
      return false;
    }
    return $lg_gallery->root . $this->folder->curdir . $this->image;
  }
  
  /**
   * LazyestImage::caption()
   * Returns a caption.
   * Either the caption from the xml file or the filename
   * When 'show captions' is enabled, underscores will be replaced by spaces
   * @return string
   */
  function caption() {
    global $lg_gallery;
    $caption = ( '' != $this->caption ) ? $this->caption : str_replace( '_', ' ', htmlentities( substr( $this->image, 0, -4 ) ) );
    $caption =  ( 'TRUE' == $lg_gallery->get_option( 'enable_captions' ) ) ? $caption : htmlentities( $this->image ); 
    return apply_filters( 'lazyest_image_caption', $caption, $this );
  }
  
  /**
   * LazyestImage::description()
   * Retruns the discription after applying a filter
   * @return string
   */
  function description() {
    return apply_filters( 'lazyest_image_description', $this->description, $this );
  }
  
  /**
   * LazyestImage::title()
   * 
   * @return
   */
  function title() {
    global $lg_gallery;
    $caption = $this->caption();
    $title =  strip_tags( lg_html( $caption ) );    
    return apply_filters( 'lazyest_image_title', $title, $this );
  }
  
  /**
   * LazyestImage::form_name()
   * 
   * @return
   */
  function form_name() {
    if ( '' == $this->image ) {
      return false;
    }
    return sanitize_title( $this->image );
  }
  
  /**
   * LazyestImage::on_click()
   * 
   * @param string $widget
   * @return
   */
  function on_click( $widget='none' ) {
    global $lg_gallery;
    return array( 'href' => $lg_gallery->address . lg_nice_link( $this->folder->curdir . $this->image ) , 'class' => 'lg', 'rel' => '', 'title' => $this->title(), 'id' => sanitize_title( $this->image ) );  
  }
  
  /**
   * LazyestImage::write_xml()
   * 
   * @param mixed $handle
   * @return void
   */
  function write_xml( $handle ) {
    if ( ! isset( $handle ) ) {    
      return false;      
    }    
		fwrite( $handle, "\t<photo>\n" );
		fwrite( $handle, "\t\t<filename><![CDATA[" . utf8_encode( htmlentities( $this->image ) ) . "]]></filename>\n" );
		fwrite( $handle, "\t\t<caption><![CDATA[" . utf8_encode( htmlentities( $this->caption ) ) . "]]></caption>\n" );    
		fwrite( $handle, "\t\t<description><![CDATA[" . utf8_encode( htmlentities( $this->description ) ) . "]]></description>\n" );
		fwrite( $handle, "\t\t<image>" . $this->id . "</image>\n" );
		fwrite( $handle, "\t\t<index>". $this->index . "</index>\n" );
    fwrite( $handle, "\t\t<imagedate>" . $this->datetime . "</imagedate>\n" );  
    if ( 0 < count( $this->extra_fields ) ) {
      foreach( $this->extra_fields as $key=>$field ) {
        fwrite( $handle, "\t\t<$key><![CDATA[" . utf8_encode( htmlentities( $field ) ) . "]]></$key>\n" );  
      }
    }
		fwrite( $handle, "\t</photo>\n" );
  }
  
  /**
   * LazyestImage::newsize()
   * resizes or crops the image
   * 
   * @since 1.1.0
   * @param int $width
   * @param int $height
   * @return bool resize or crop success
   */
  function newsize( $width, $height ) {
    global $lg_gallery;
    if ( 'TRUE' == $lg_gallery->get_option( 'use_cropping' ) ) {
      $size = ( $width  < $height  ) ? $width :  $height; 
      if ( false === $this->crop( $size ) ) {
        return false;
      }             
    } else {
      if  ( false === $this->resize( $width, $height ) ) {
        return false;
      }
    }
    return true;
  }
  
  /**
   * LazyestImage::resize()
   * 
   * @param int $width  Maximum Width to resize the image
   * @param int $height Maximum height to resize the image
   * @return bool resize success
   */
  function resize( $width, $height ) {
    global $lg_gallery;
    if ( false === $this->loc() ) {
      return false;
    }
    if ( ! $this->memory_ok() ) {
      return false;
    }
    $img_location = $this->original();
    list( $o_width, $o_height, $o_type ) = @getimagesize( $img_location );
    
    $img = wp_load_image( $img_location );
    if ( !is_resource( $img ) ) {
    	trigger_error( $img, E_USER_WARNING );
    	return false;
		}
    
    $xratio = $width / $o_width;
    $yratio = $height / $o_height;
  	if ( ( $xratio >= 1 )  && ( $yratio >= 1 ) ) { 
  	  $nwidth = $o_width;
  	  $nheight = $o_height;
  	} elseif ( ( $xratio * $o_height ) < $height ) {
  		$nheight = floor( $xratio * $o_height );
  		$nwidth = $width;
  	} else {
  		$nwidth = floor( $yratio * $o_width );
  		$nheight = $height;
  	}
  	
    $resized = wp_imagecreatetruecolor( $nwidth, $nheight );  	  	 	
    imagecopyresampled( $resized, $img, 0, 0, 0, 0, $nwidth, $nheight, $o_width, $o_height );
    
    // convert from full colors to index colors, like original PNG.
    if ( IMAGETYPE_PNG == $o_type && function_exists( 'imageistruecolor' ) && ! imageistruecolor( $img ) )
      imagetruecolortopalette( $resized, false, imagecolorstotal( $img ) );
    
    unset( $img );    
    $resized = apply_filters( 'lazyest_imageresized', $resized, $width, $height, $this );
    $this->resized = $resized;               
    return true; 
  }
  
  /**
   * LazyestImage::crop()
   * 
   * @param mixed $size Width and Height of the square cropped image
   * @return bool success or failure
   * @todo merge with LazyestImage::resize()
   */
  function crop( $size ) {
    if ( false === $this->loc() ) {
      return false;
    }
    if ( ! $this->memory_ok() ) {
      return false;
    }
    $img_location = $this->original();
    list( $o_width, $o_height, $o_type ) = @getimagesize( $img_location );
    
    $img = wp_load_image( $img_location );
    if ( !is_resource( $img ) ) {
    	trigger_error( $img, E_USER_WARNING );
    	return false;
		}    
    
    if ( $o_width > $o_height )  { // landscape image
      $out_width = $out_height = $o_height;
      $out_left = round( ( $o_width - $o_height ) / 2 );
      $out_top = 0;
    } else { // portrait image
      $out_top = 0;
      $out_width = $out_height = $o_width;
      $out_left = 0;
    }  
        
    $cropped = wp_imagecreatetruecolor( $size, $size );
		imagecopyresampled( $cropped, $img, 0, 0, $out_left, $out_top, $size, $size, $out_width, $out_height );
		
		// convert from full colors to index colors, like original PNG.
		if ( IMAGETYPE_PNG == $o_type && function_exists( 'imageistruecolor' ) && ! imageistruecolor( $img ) )
			imagetruecolortopalette( $cropped, false, imagecolorstotal( $img ) );
		
    unset( $img );    
    $cropped = apply_filters( 'lazyest_image_cropped', $cropped, $size, $this );
    $this->resized = $cropped;  
    return true;  
  }
  
  /**
   * LazyestImage::memory_ok()
   * Checks if the amount of memory needed to store an image is available
   * 
   * @since 1.0
   * @return bool
   */
  function memory_ok() {
    global $lg_gallery;
    if ( 'TRUE' == $lg_gallery->get_option( 'memory_ok' ) ) {
      @ini_set('memory_limit', '256M' );
      return true; 
    }      
    $image_info = getimagesize( $lg_gallery->root . $this->folder->curdir . $this->image );
    $bits = ( isset( $image_info['bits'] ) ) ? $image_info['bits'] : 8;
    $channels = ( isset( $image_info['channels'] ) ) ? $image_info['channels'] : 3;
    $memory_needed = round( ( $image_info[0] * $image_info[1] * $bits * $channels / 8 + pow(2, 16) ) * 2 );
    return apply_filters( 'lazyest_memory_ok', ( memory_get_usage() + $memory_needed < (integer)ini_get('memory_limit') * pow( 1024, 2 ) ) );
  }
  
  /**
   * LazyestImage::set_extra_field()
   * 
   * set the value for an extra field
   * this should be a string
   * 
   * @param string $index
   * @param string $value
   * @return void
   * @since 1.1.0
   */
  function set_extra_field( $index, $value='' ) {
    $this->extra_fields[$index] = $value;
  }
  
  /**
   * LazyestImage::get_extra_field()
   * 
   * @param string $index
   * @return string
   * @since 1.1.0
   */
  function get_extra_field( $index ) {
    $value = false;
    if ( isset($this->extra_fields[$index] ) ) {
      $value = $this->extra_fields[$index];
    }
    return $value;
  }
  
} // LazyestImage

/**
 * LazyestSlide
 * 
 * @package Lazyest Gallery   
 * @author Marcel Brinkkemper
 * @copyright 2010 Brimosoft
 * @version 1.0
 * @access public
 */
class LazyestSlide extends LazyestImage {  
  
  /**
   * LazyestSlide::src()
   * 
   * @return string
   */
  function src() {
    global $lg_gallery;
    if ( ( ! $this->valid() ) || ! isset( $lg_gallery )  ) {
      return false;
    }     
    $slidefile = $lg_gallery->root . $this->folder->curdir . $lg_gallery->get_option( 'slide_folder' ) . $this->image;
    if ( 'TRUE' == $lg_gallery->get_option( 'enable_slides_cache' ) ) {
      if ( ! file_exists( $slidefile ) ) {
        $this->cache();
      }
      if ( file_exists( $slidefile ) ) { // a slide has been cached
        return  $lg_gallery->address . lg_nice_link( $this->folder->curdir . $lg_gallery->get_option( 'slide_folder' ) . $this->image );
      } else { // a slide could not be cached, probably a memory error
        return $lg_gallery->plugin_url . '/images/file_alert.png';
      }
      
    }		   
  	return admin_url( 'admin-ajax.php' ) . '?action=lg_image_request&amp;file='. lg_nice_link( $this->folder->realdir() . $this->image ) ;						
  }
      
  /**
   * LazyestSlide::html_id()
   * 
   * @return
   */
  function html_id() {
    return 'lg_slide_' . LazyestImage::html_id();
  }
  
  /**
   * LazyestSlide::uri()
   * 
   * @param string $widget
   * @return
   */
  function uri( $widget = 'none' ) {
    global $lg_gallery;
    if ( ( false === $this->loc() ) || ! isset( $lg_gallery )  ) {
      return false;
    }
    return $this->folder->uri( $widget ) . lg_nice_link( $this->image );
  }
  
  /**
   * LazyestSlide::cache()
   * Creates a slide in the slides cache.
   * 
   * @return bool success or failure
   */
  function cache() {
    global $lg_gallery;
    if ( isset( $lg_gallery ) && ( $this->image != '' ) ) {
      if ( 'TRUE' == $lg_gallery->get_option( 'enable_slides_cache' ) ) {
        $slide_dir = $lg_gallery->root . $this->folder->curdir . $lg_gallery->get_option( 'slide_folder' );
		    if ( ! file_exists( $this->loc() ) ) {
		      if ( false === $this->resize( $lg_gallery->get_option( 'pictwidth' ), $lg_gallery->get_option( 'pictheight' ) ) ) {
		        return false;  
		      }
          if ( ! file_exists( $slide_dir ) ) {
            $res = wp_mkdir_p( $slide_dir );
            if ( ! $res ) {
              return false;
            }
          }
          if ( is_writable( $slide_dir ) ) {
            $path = pathinfo( $this->image );         
            if ( is_resource( $this->resized ) ) {
          		switch ( strtolower( $path['extension'] ) ) {
          	  	case 'jpeg':
          	  	case 'jpg':
          	    	imagejpeg( $this->resized, $slide_dir . $this->image, $lg_gallery->get_option( 'resample_quality' ) );
          	    	break;
          	  	case 'gif':
          	    	imagegif( $this->resized, $slide_dir . $this->image );
          	    	break;
          	  	case 'png':
          	    	imagepng( $this->resized, $slide_dir . $this->image );
          	   	 break;
          		}
            }
          }
          if ( file_exists( $slide_dir . $this->image ) ) {
            $stat = stat( dirname( $slide_dir ) );            
            $perms = $stat['mode'] & 0000666;
            @chmod( $slide_dir . $this->image, $perms );
            return true;
          } else {            
            return false;
          }  
        } else {
          return true;
        }
      }
    }
  }
  
	/**
   * LazyestSlide::loc()
   * location of the image in the file system
   * @return string 
   */
  function loc() {
    global $lg_gallery;
    return $lg_gallery->root . $this->folder->curdir . $lg_gallery->get_option( 'slide_folder' ) . $this->image;
  }
  
  
  /**
   * LazyestSlide::on_click()
   * 
   * @param string $widget
   * @return
   */
  function on_click( $widget = 'none' ) {
    global $lg_gallery;  
    $onclick = LazyestImage::on_click();
    $onclick['id'] = 'lg_thumb_onclick_' .  $onclick['id'];   
    $slide = new LazyestSlide( $this->folder );
    $slide->image = $this->image; 
    switch ( $lg_gallery->get_option( 'on_slide_click' ) ) {
      case 'nothing' : 
        $onclick['href'] = '#';
        break;
      case 'fullimg' :
        break;
      case 'lightbox' :
        $onclick['rel'] = 'lightbox[' . $this->folder->form_name() . ']';        
        break;
      case 'thickbox' :
        $onclick['class'] = 'thickbox';
        $onclick['rel'] = $this->folder->form_name();
        break;
      case 'popup' :
        $onclick['href'] = "javascript:void(window.open('" . $lg_gallery->plugin_url . "/lazyest-popup.php?image=" . $this->image . "&amp;folder=" . lg_nice_link( $this->folder->curdir ) . "','','resizable=no,location=no,menubar=no,scrollbars=no,status=no,toolbar=no,fullscreen=no,dependent=yes,width=" . $lg_gallery->get_option( 'pictwidth' ) . ",height=" . $lg_gallery->get_option( 'pictheight' ) . ",left=100,top=100'))";
        break;
    }    
    unset( $slide );
    $onclick = apply_filters( 'lazyest_slide_onclick', $onclick, $this );
    return $onclick;
  }
  
} // LazyestSlide

/**
 * LazyestThumb
 * 
 * @package  Lazyest Gallery  
 * @author Marcel Brinkkemper
 * @copyright 2010 Brimosoft
 * @version 1.0
 * @access public
 */
class LazyestThumb extends LazyestImage {
   
  /**
   * LazyestThumb::__construct()
   * 
   * @param mixed $parent
   * @return
   */
  function __construct( $parent ) {
    LazyestImage::__construct( $parent );    
  }  
  
  /**
   * LazyestThumb::src()
   * 
   * @return
   */
  function src() {
    global $lg_gallery;
    if ( ! $this->valid() || ! isset( $lg_gallery )  ) {
      return false;
    }  
    $thumbfile = $lg_gallery->root . $this->folder->curdir . $lg_gallery->get_option( 'thumb_folder' ) . $this->image;
    if ( 'TRUE' == $lg_gallery->get_option( 'enable_cache' ) ) {   
      if ( ! file_exists( $thumbfile ) && ( 'TRUE' != $lg_gallery->get_option( 'async_cache' ) ) ) {
        $this->cache();
      }
      if ( file_exists( $thumbfile ) ) { 
        return  $lg_gallery->address . lg_nice_link( $this->folder->curdir . $lg_gallery->get_option( 'thumb_folder' ) . $this->image );
      } else {
        if ( 'TRUE' == $lg_gallery->get_option( 'async_cache' ) ) {
          return $lg_gallery->plugin_url . '/images/ajax-img.gif?action=lg_image_request&amp;file=' . lg_nice_link( $this->folder->realdir() . $this->image ) . '&amp;thumb=1';	
        } else {
          return $lg_gallery->plugin_url . '/images/file_alert.png';
        }
      }
      
    }				
  	return admin_url( 'admin-ajax.php' ) . '?action=lg_image_request&amp;file=' . lg_nice_link( $this->folder->realdir() . $this->image ) . '&amp;thumb=1';						
  }
        
  /**
   * LazyestThumb::html_id()
   * 
   * @return
   */
  function html_id() {
    return 'lg_thumb_' . LazyestImage::html_id();
  }
  
  /**
   * LazyestThumb::cache()
   * 
   * @return
   */
  function cache() {
    global $lg_gallery;
    if ( isset( $lg_gallery ) && ( $this->image != '' ) ) {
      if ( 'TRUE' == $lg_gallery->get_option( 'enable_cache' ) ) {
        $thumb_dir = $lg_gallery->root . $this->folder->curdir . $lg_gallery->get_option( 'thumb_folder' );
		    if ( ! file_exists( $this->loc() ) ) {
		      if  ( false === $this->newsize( $lg_gallery->get_option( 'thumbwidth' ), $lg_gallery->get_option( 'thumbheight' ) ) ) {
            return false;
          }          
          if ( ! file_exists( $thumb_dir ) ) {
            $res = wp_mkdir_p( $thumb_dir, 0777 );
            if ( false === $res ) {		                        
              return false;  
            }
          }
          if ( is_writable( $thumb_dir ) ) {
            $path = pathinfo( $this->image );            
            if ( is_resource( $this->resized ) ) {
          		switch ( strtolower( $path['extension'] ) ) {
          	  	case 'jpeg':
          	  	case 'jpg':
          	    	imagejpeg( $this->resized, $thumb_dir . $this->image, $lg_gallery->get_option( 'resample_quality' ) );
          	    	break;
          	  	case 'gif':
          	    	imagegif( $this->resized, $thumb_dir . $this->image );
          	    	break;
          	  	case 'png':
          	    	imagepng( $this->resized, $thumb_dir . $this->image );
          	   	 break;
          		}
            }
          }
          if ( 'TRUE' != $lg_gallery->get_option( 'async_cache' ) ) { // if async_cache, resized image will be output by admin-ajax.php
            if ( is_resource( $this->resized ) ) imagedestroy( $this->resized );
          }
          if ( file_exists( $thumb_dir . $this->image ) ) {
            $stat = stat( dirname( $thumb_dir ) );
            $perms = $stat['mode'] & 0000666;
            @chmod( $thumb_dir . $this->image, $perms );
            return true;
          } else {		          
            return false;
          }  
        } else {
          return true;
        }
      }
    }
  }
  
  
  
  /**
   * LazyestThumb::loc()
   * location of the image in the file system
   * @return string 
   */
  function loc() {
    global $lg_gallery;
    return $lg_gallery->root . $this->folder->curdir . $lg_gallery->get_option( 'thumb_folder' ) . $this->image;
  }
  

  /**
   * LazyestThumb::on_click()
   * 
   * @param string $widget
   * @return
   */
  function on_click( $widget = 'none' ) {
    global $lg_gallery;  
    $onclick = LazyestImage::on_click( $widget );     
    $onclick['id'] = 'lg_thumb_onclick_' .  $onclick['id'];   
    $slide = new LazyestSlide( $this->folder );
    $slide->image = $this->image;  
    switch ( $lg_gallery->get_option( 'on_thumb_click' ) ) {
      case 'nothing' : 
        $onclick['href'] = '#';
        break;
      case 'fullimg' :
        break;
      case 'slide' : 
        $onclick['href'] = $slide->uri( $widget );
        break;
      case 'lightslide' :
        $onclick['href'] = $slide->src();
        $onclick['rel'] = 'lightbox[' . $this->folder->form_name() . ']'; 
        break;
      case 'thickslide' : 
        $onclick['href'] = $slide->src(); 
        $onclick['class'] = 'thickbox';
        $onclick['rel'] = $this->folder->form_name();
      case 'lightbox' :
        $onclick['rel'] = 'lightbox[' . $this->folder->form_name() . ']';        
        break;
      case 'thickbox' :
        $onclick['class'] = 'thickbox';
        $onclick['rel'] = $this->folder->form_name();
        break;
    }    
    unset( $slide );
    $onclick = apply_filters( 'lazyest_thumb_onclick', $onclick, $this );
    return $onclick;
  }
  
} // LazyestThumb
?>