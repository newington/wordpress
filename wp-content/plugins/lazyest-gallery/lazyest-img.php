<?php

/**
 * lg_deprecated_image_request()
 * Used to create images on the fly
 * 
 * As of Lazyest Gallery version 1.1.0 this file should no longer be called directly
 * only for backward compatibility for hard coded links
 * 
 * @deprecated use ajax call lg_image_request 
 * @return void
 */
function lg_deprecated_image_request() {
	require_once( dirname( __FILE__ ) . '/inc/frontend.php' );
  global $lg_gallery;
  
  $lg_gallery = new LazyestFrontend();
  
  if ( ! isset( $lg_gallery ) )
  	die('-1');
 
  $memok = $lg_gallery->valid();
  $thumb = isset( $_GET[ 'thumb' ] );
  
	$path = pathinfo( $lg_gallery->file );
  $folder = new LazyestFrontendFolder( $path[ 'dirname'] );
  $image = ( $thumb ) ? new LazyestThumb( $folder ) : new LazyestSlide( $folder );
  $image->image = $path[ 'basename' ];
  if( $thumb ) {
		$height = $lg_gallery->get_option( 'thumbheight' );
		$width = $lg_gallery->get_option( 'thumbwidth' );
	}
	else {
		$height = $lg_gallery->get_option( 'pictheight' );
		$width = $lg_gallery->get_option( 'pictwidth' );
	}
  $cache = ( ( $thumb && ( 'TRUE' == $lg_gallery->get_option( 'enable_cache' ) ) ) || ( ! $thumb && ( 'TRUE' == $lg_gallery->get_option( 'enable_slides_cache' ) ) ) );
  if ( $memok ) {
	  if ( $cache ) {
	    $memok = $image->cache();
	  } else {		
	    $memok = $image->resize( $width, $height ); 
	  }
  }
  if ( ! $memok ) {      	
    $alert = imagecreatefrompng( $lg_gallery->plugin_dir . '/images/file_alert.png' );
    header( 'Content-type: image/png' );
    imagepng( $alert );
    imagedestroy( $alert );
  } else {    
    if ( is_resource( $image->resized ) ) {
      switch( strtolower( $path[ 'extension' ] ) ) {   
    		case 'jpeg':
    		case 'jpg':
    			header( 'Content-type: image/jpeg' );
    		  imagejpeg( $image->resized );
    			break;
    		case 'gif':
    			header( 'Content-type: image/gif' );
        	imagegif( $image->resized );
    			break;
    		case 'png':
    			header( 'Content-type: image/png' );
        	imagepng( $image->resized );
    			break;
    		default:
    			break;
    	}      	
      imagedestroy( $image->resized );
    }
  }
}


$root = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );

if ( file_exists( $root . '/wp-load.php' ) ) {
    require_once( $root . '/wp-load.php' );
} 

lg_deprecated_image_request();

?>